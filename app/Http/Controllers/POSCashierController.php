<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class POSCashierController extends Controller
{
    /**
     * Halaman Utama POS Cashier
     */
    public function index()
    {
        $businessId = Auth::user()->business_id;

        // Ambil produk milik business yang sedang login
        $products = Product::where('business_id', $businessId)->get();

        $cart = Session::get('cart', []);

        // Hitung Subtotal Keranjang menggunakan 'selling_price'
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['selling_price'] * $item['quantity'];
        }

        // Kalkulasi Tambahan
        $tax = $subtotal * 0.11; // Pajak 11%
        $discount = 0; // Diskon default
        $total = $subtotal + $tax - $discount;

        return view('pos-cashier.index', compact('products', 'cart', 'subtotal', 'tax', 'discount', 'total'));
    }

    /**
    /**
     * Tambah Produk ke Keranjang
     */
    public function addToCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity ?? 1;
        $note = $request->note ?? '';

        $product = Product::where('business_id', Auth::user()->business_id)
            ->findOrFail($productId);

        // Cek stok inventori
        if ($product->stock < $quantity) {
            return back()->with('error', 'Stok produk tidak mencukupi!');
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $quantity;

            if ($product->stock < $newQuantity) {
                return back()->with('error', 'Stok tidak mencukupi untuk penambahan ini!');
            }

            $cart[$productId]['quantity'] = $newQuantity;
            if ($note) {
                $cart[$productId]['note'] = $note;
            }
        } else {
            // PERBAIKAN: Simpan murni nama file dari DB ($product->image) tanpa asset()
            $cart[$productId] = [
                'id'             => $product->id,
                'name'           => $product->name,
                'selling_price'  => $product->selling_price,
                'purchase_price' => $product->purchase_price,
                'stock'          => $product->stock,
                'quantity'       => $quantity,
                'image'          => $product->image, 
                'note'           => $note
            ];
        }

        Session::put('cart', $cart);

        return redirect()->route('pos.cashier')->with('success', 'Produk masuk ke keranjang!');
    }

    /**
     * Update Jumlah Item di Keranjang (Tombol + / -)
     */
    public function updateCart(Request $request)
    {
        $productId = $request->product_id;
        $change = (int) $request->quantity; // bernilai +1 atau -1 dari Blade Form

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $product = Product::where('business_id', Auth::user()->business_id)->find($productId);
            $newQuantity = $cart[$productId]['quantity'] + $change;

            if ($newQuantity <= 0) {
                unset($cart[$productId]);
            } else {
                if ($product && $product->stock < $newQuantity) {
                    return back()->with('error', 'Jumlah melebihi stok yang tersedia!');
                }
                $cart[$productId]['quantity'] = $newQuantity;
            }

            Session::put('cart', $cart);
        }

        return redirect()->route('pos.cashier')->with('success', 'Keranjang diperbarui!');
    }

    /**
     * Hapus 1 Item dari Keranjang
     */
    public function removeFromCart($id)
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return redirect()->route('pos.cashier')->with('success', 'Item dihapus dari keranjang!');
    }

    /**
     * Kosongkan Keranjang
     */
    public function clearCart()
    {
        Session::forget('cart');
        return redirect()->route('pos.cashier')->with('success', 'Keranjang dikosongkan!');
    }

    /**
     * Process Checkout & Simpan Transaksi Ke Database
     */
    public function checkout(Request $request)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('pos.cashier')->with('error', 'Keranjang belanja masih kosong!');
        }

        $user = Auth::user();

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $totalCost = 0;

            foreach ($cart as $item) {
                $subtotal += $item['selling_price'] * $item['quantity'];
                $totalCost += ($item['purchase_price'] ?? 0) * $item['quantity'];
            }

            $tax = $subtotal * 0.11;
            $discount = 0;
            $totalAmount = $subtotal + $tax - $discount;
            $totalProfit = $totalAmount - $totalCost;

            $invoiceNumber = 'TRX-' . date('Ymd') . '-' . rand(1000, 9999);

            $transaction = Transaction::create([
                'business_id'    => $user->business_id,
                'user_id'        => $user->id,
                'invoice_number' => $invoiceNumber,
                'subtotal'       => $subtotal,
                'tax'            => $tax,
                'discount'       => $discount,
                'total_amount'   => $totalAmount,
                'total_cost'     => $totalCost,
                'total_profit'   => $totalProfit,
                'payment_method' => $request->payment_method ?? 'cash',
            ]);

            foreach ($cart as $productId => $item) {
                // Pengaman Stok Aktual di Database
                $product = Product::lockForUpdate()->find($productId);
                
                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception("Stok produk '{$item['name']}' tidak mencukupi!");
                }

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $productId,
                    'quantity'       => $item['quantity'],
                    'purchase_price' => $item['purchase_price'] ?? 0,
                    'selling_price'  => $item['selling_price'],
                    'subtotal'       => $item['selling_price'] * $item['quantity'],
                    'note'           => $item['note'] ?? null,
                ]);

                // Potong stok inventori
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();

            Session::forget('cart');

            return redirect()->route('pos.cashier')->with('success', 'Transaksi ' . $invoiceNumber . ' Berhasil Disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pos.cashier')->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }
}
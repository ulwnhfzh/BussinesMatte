<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use RuntimeException;
use Throwable;

class POSCashierController extends Controller
{
    public function __construct(
        private readonly StockMovementService $stockMovementService
    ) {
    }

    /**
     * Halaman utama POS Cashier.
     */
    public function index()
    {
        $businessId = Auth::user()->business_id;

        // Hanya mengambil produk milik business yang sedang login.
        $products = Product::where('business_id', $businessId)->get();

        $cart = Session::get('cart', []);

        // Hitung subtotal keranjang menggunakan selling_price.
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += $item['selling_price'] * $item['quantity'];
        }

        $tax = $subtotal * 0.11;
        $discount = 0;
        $total = $subtotal + $tax - $discount;

        return view('pos-cashier.index', compact(
            'products',
            'cart',
            'subtotal',
            'tax',
            'discount',
            'total'
        ));
    }

    /**
     * Menambahkan produk ke keranjang.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                'integer',
            ],
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'note' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $productId = (int) $request->product_id;
        $quantity = (int) ($request->quantity ?? 1);
        $note = $request->note ?? '';

        $product = Product::where(
            'business_id',
            Auth::user()->business_id
        )->findOrFail($productId);

        if ($product->stock < $quantity) {
            return back()->with(
                'error',
                'Stok produk tidak mencukupi!'
            );
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $quantity;

            if ($product->stock < $newQuantity) {
                return back()->with(
                    'error',
                    'Stok tidak mencukupi untuk penambahan ini!'
                );
            }

            $cart[$productId]['quantity'] = $newQuantity;

            if ($note) {
                $cart[$productId]['note'] = $note;
            }
        } else {
            /*
             * Simpan nama file gambar dari database.
             * URL lengkap akan dibuat pada Blade.
             */
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'selling_price' => $product->selling_price,
                'purchase_price' => $product->purchase_price,
                'stock' => $product->stock,
                'quantity' => $quantity,
                'image' => $product->image,
                'note' => $note,
            ];
        }

        Session::put('cart', $cart);

        return redirect()
            ->route('pos.cashier')
            ->with('success', 'Produk masuk ke keranjang!');
    }

    /**
     * Memperbarui jumlah item di keranjang.
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                'integer',
            ],
            'quantity' => [
                'required',
                'integer',
                'in:-1,1',
            ],
        ]);

        $productId = (int) $request->product_id;
        $change = (int) $request->quantity;
        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $product = Product::where(
                'business_id',
                Auth::user()->business_id
            )->find($productId);

            $newQuantity = $cart[$productId]['quantity'] + $change;

            if ($newQuantity <= 0) {
                unset($cart[$productId]);
            } else {
                if (!$product) {
                    unset($cart[$productId]);

                    Session::put('cart', $cart);

                    return back()->with(
                        'error',
                        'Produk tidak ditemukan pada business ini.'
                    );
                }

                if ($product->stock < $newQuantity) {
                    return back()->with(
                        'error',
                        'Jumlah melebihi stok yang tersedia!'
                    );
                }

                $cart[$productId]['quantity'] = $newQuantity;
            }

            Session::put('cart', $cart);
        }

        return redirect()
            ->route('pos.cashier')
            ->with('success', 'Keranjang diperbarui!');
    }

    /**
     * Menghapus satu item dari keranjang.
     */
    public function removeFromCart($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return redirect()
            ->route('pos.cashier')
            ->with('success', 'Item dihapus dari keranjang!');
    }

    /**
     * Mengosongkan keranjang.
     */
    public function clearCart()
    {
        Session::forget('cart');

        return redirect()
            ->route('pos.cashier')
            ->with('success', 'Keranjang dikosongkan!');
    }

    /**
     * Memproses checkout dan menyimpan transaksi.
     */
    public function checkout(Request $request)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('pos.cashier')
                ->with('error', 'Keranjang belanja masih kosong!');
        }

        $user = Auth::user();
        $businessId = (int) $user->business_id;

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $totalCost = 0;

            foreach ($cart as $item) {
                $subtotal += $item['selling_price']
                    * $item['quantity'];

                $totalCost += ($item['purchase_price'] ?? 0)
                    * $item['quantity'];
            }

            $tax = $subtotal * 0.11;
            $discount = 0;
            $totalAmount = $subtotal + $tax - $discount;
            $totalProfit = $totalAmount - $totalCost;

            $invoiceNumber = 'TRX-'
                . date('Ymd')
                . '-'
                . rand(1000, 9999);

            $transaction = Transaction::create([
                'business_id' => $businessId,
                'user_id' => $user->id,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'payment_method' => $request->payment_method ?? 'cash',
            ]);

            foreach ($cart as $productId => $item) {
                /*
                 * Query wajib menggunakan business_id agar produk
                 * milik tenant lain tidak dapat diproses.
                 */
                $product = Product::where(
                    'business_id',
                    $businessId
                )->find($productId);

                if (!$product) {
                    throw new RuntimeException(
                        "Produk '{$item['name']}' tidak ditemukan "
                        . 'pada business ini.'
                    );
                }

                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        "Jumlah produk '{$item['name']}' tidak valid."
                    );
                }

                if ($product->stock < $quantity) {
                    throw new RuntimeException(
                        "Stok produk '{$item['name']}' tidak mencukupi!"
                    );
                }

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'purchase_price' => $item['purchase_price'] ?? 0,
                    'selling_price' => $item['selling_price'],
                    'subtotal' => $item['selling_price'] * $quantity,
                    'note' => $item['note'] ?? null,
                ]);

                /*
                 * StockMovementService akan:
                 * 1. Mengunci produk menggunakan lockForUpdate().
                 * 2. Memastikan stok tidak menjadi negatif.
                 * 3. Mengurangi stok.
                 * 4. Membuat stock movement bertipe sale.
                 *
                 * Tidak boleh menggunakan decrement() lagi karena
                 * service sudah melakukan pengurangan stok.
                 */
                $this->stockMovementService->changeStock(
                    product: $product,
                    type: StockMovement::TYPE_SALE,
                    quantity: -$quantity,
                    reference: $transaction,
                    note: 'Penjualan melalui POS ' . $invoiceNumber
                );
            }

            DB::commit();

            Session::forget('cart');

            return redirect()
                ->route('pos.cashier')
                ->with(
                    'success',
                    'Transaksi '
                        . $invoiceNumber
                        . ' berhasil disimpan!'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->route('pos.cashier')
                ->with(
                    'error',
                    'Gagal memproses transaksi: '
                        . $exception->getMessage()
                );
        }
    }
}
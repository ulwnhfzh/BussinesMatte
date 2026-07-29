<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class POSCashierController extends Controller
{
    /**
     * Data dummy produk
     * Silakan edit, tambah, atau hapus sesuai kebutuhan
     */
    private function getProducts()
    {
        return [
            [
                'id' => 1,
                'name' => 'Espresso Maker Pro',
                'category' => 'Elektronik',
                'price' => 2450000,
                'stock' => 12,
                'image' => 'https://via.placeholder.com/80x80/2563eb/ffffff?text=EM'
            ],
            [
                'id' => 2,
                'name' => 'Sourdough Artisanal',
                'category' => 'Makanan',
                'price' => 110000,
                'stock' => 45,
                'image' => 'https://via.placeholder.com/80x80/e67e22/ffffff?text=SA'
            ],
            [
                'id' => 3,
                'name' => 'Glass Water Bottle',
                'category' => 'Aksesoris',
                'price' => 120000,
                'stock' => 156,
                'image' => 'https://via.placeholder.com/80x80/2ecc71/ffffff?text=GW'
            ],
            [
                'id' => 4,
                'name' => 'Matcha Powder Premium',
                'category' => 'Minuman',
                'price' => 185000,
                'stock' => 30,
                'image' => 'https://via.placeholder.com/80x80/27ae60/ffffff?text=MP'
            ],
            [
                'id' => 5,
                'name' => 'Wireless Headset Pro',
                'category' => 'Elektronik',
                'price' => 99000,
                'stock' => 8,
                'image' => 'https://via.placeholder.com/80x80/8e44ad/ffffff?text=WH'
            ],
            [
                'id' => 6,
                'name' => 'Luxury Truffle Box',
                'category' => 'Makanan',
                'price' => 320000,
                'stock' => 22,
                'image' => 'https://via.placeholder.com/80x80/c0392b/ffffff?text=LT'
            ],
        ];
    }

    public function index()
    {
        $products = $this->getProducts();
        $cart = Session::get('cart', []);
        
        // Hitung total
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $tax = $subtotal * 0.11; // Pajak 11%
        $discount = 50000; // Diskon member fixed
        $total = $subtotal + $tax - $discount;

        return view('pos-cashier.index', compact('products', 'cart', 'subtotal', 'tax', 'discount', 'total'));
    }

    public function addToCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity ?? 1;
        $note = $request->note ?? '';

        // Ambil data produk dari dummy
        $products = $this->getProducts();
        $product = collect($products)->firstWhere('id', $productId);

        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            // Update quantity jika sudah ada
            $cart[$productId]['quantity'] += $quantity;
            if ($note) {
                $cart[$productId]['note'] = $note;
            }
        } else {
            // Tambah baru
            $cart[$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image' => $product['image'],
                'note' => $note
            ];
        }

        Session::put('cart', $cart);

        return redirect()->route('pos.cashier')->with('success', 'Produk ditambahkan ke keranjang!');
    }

    public function updateCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity;
        $note = $request->note ?? '';

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $quantity;
                $cart[$productId]['note'] = $note;
            }
            Session::put('cart', $cart);
        }

        return redirect()->route('pos.cashier')->with('success', 'Keranjang diperbarui!');
    }

    public function removeFromCart($id)
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return redirect()->route('pos.cashier')->with('success', 'Item dihapus dari keranjang!');
    }

    public function clearCart()
    {
        Session::forget('cart');
        return redirect()->route('pos.cashier')->with('success', 'Keranjang dikosongkan!');
    }

    public function checkout(Request $request)
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('pos.cashier')->with('error', 'Keranjang kosong!');
        }

        // Simulasi penyimpanan transaksi
        $transactionId = 'TRX-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Kosongkan keranjang setelah checkout
        Session::forget('cart');

        return redirect()->route('pos.cashier')->with('success', 'Transaksi ' . $transactionId . ' berhasil!');
    }
}
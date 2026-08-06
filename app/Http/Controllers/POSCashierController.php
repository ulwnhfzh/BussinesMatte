<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\StockMovementService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
     *
     * Dilengkapi proteksi double checkout memakai Cache::lock agar
     * dua permintaan yang datang bersamaan tidak membuat dua invoice.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,qris,e-wallet'],
        ]);

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('pos.cashier')
                ->with('error', 'Keranjang belanja masih kosong!');
        }

        $user = Auth::user();
        $businessId = (int) $user->business_id;

        /*
         * Proteksi double checkout.
         * Mengunci proses untuk kombinasi bisnis + user agar dua request
         * yang hampir bersamaan tidak dapat membuat dua invoice sekaligus.
         * Kunci otomatis lepas setelah 10 detik bila proses macet.
         */
        $lock = Cache::lock(
            'pos-checkout-' . $businessId . '-' . $user->id,
            10
        );

        if (! $lock->get()) {
            return redirect()
                ->route('pos.cashier')
                ->with('error', 'Transaksi sedang diproses. Harap tunggu sebentar.');
        }

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
                'payment_method' => $request->payment_method,
                'status' => Transaction::STATUS_COMPLETED,
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
                 * StockMovementService akan mengunci produk, memastikan
                 * stok tidak negatif, mengurangi stok, dan membuat
                 * stock movement bertipe sale.
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

            $lock->release();

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

            $lock->release();

            return redirect()
                ->route('pos.cashier')
                ->with(
                    'error',
                    'Gagal memproses transaksi: '
                        . $exception->getMessage()
                );
        }
    }

    /**
     * Menampilkan daftar riwayat transaksi kasir.
     */
    public function transactionsHistory(Request $request)
    {
        $businessId = Auth::user()->business_id;

        $query = Transaction::with(['user', 'details'])
            ->where('business_id', $businessId);

        // Filter pencarian invoice / nama kasir
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Rentang Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('pos-cashier.transactions.index', compact('transactions'));
    }

    /**
     * Menampilkan detail transaksi.
     */
    public function transactionDetail($id)
    {
        $businessId = Auth::user()->business_id;

        $transaction = Transaction::with(['user', 'details.product'])
            ->where('business_id', $businessId)
            ->findOrFail($id);

        return view('pos-cashier.transactions.show', compact('transaction'));
    }

    /**
     * Halaman cetak/thermal print struk transaksi.
     */
    public function printReceipt($id)
    {
        $businessId = Auth::user()->business_id;

        $transaction = Transaction::with(['user', 'details.product'])
            ->where('business_id', $businessId)
            ->findOrFail($id);

        return view('pos-cashier.transactions.print', compact('transaction'));
    }

    /**
     * Membatalkan (void) seluruh transaksi.
     *
     * Seluruh stok item dikembalikan ke inventory dengan riwayat retur.
     * Status transaksi diubah menjadi "voided".
     */
    public function voidTransaction($id)
    {
        $businessId = (int) Auth::user()->business_id;

        $transaction = $this->findEditableTransaction($id, $businessId);

        DB::beginTransaction();

        try {
            $this->restockTransactionItems($transaction, 'dibatalkan (void)');

            $transaction->update([
                'status' => Transaction::STATUS_VOIDED,
            ]);

            DB::commit();

            return redirect()
                ->route('pos.transactions.show', $transaction->id)
                ->with('success', 'Transaksi ' . $transaction->invoice_number . ' berhasil dibatalkan (void).');
        } catch (Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->route('pos.transactions.show', $transaction->id)
                ->with('error', 'Gagal membatalkan transaksi: ' . $exception->getMessage());
        }
    }

    /**
     * Refund penuh: seluruh stok item dikembalikan dan status "refunded".
     */
    public function refundTransaction($id)
    {
        $businessId = (int) Auth::user()->business_id;

        $transaction = $this->findEditableTransaction($id, $businessId);

        DB::beginTransaction();

        try {
            $this->restockTransactionItems($transaction, 'di-refund');

            $transaction->update([
                'status' => Transaction::STATUS_REFUNDED,
            ]);

            DB::commit();

            return redirect()
                ->route('pos.transactions.show', $transaction->id)
                ->with('success', 'Transaksi ' . $transaction->invoice_number . ' berhasil di-refund.');
        } catch (Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->route('pos.transactions.show', $transaction->id)
                ->with('error', 'Gagal memproses refund: ' . $exception->getMessage());
        }
    }

    /**
     * Retur parsial atau penuh.
     *
     * Request wajib berisi array `items` berisi product_id => quantity
     * yang akan dikembalikan. Stok dikembalikan sesuai jumlah retur.
     */
    public function returnTransaction(Request $request, $id)
    {
        $businessId = (int) Auth::user()->business_id;

        $transaction = $this->findEditableTransaction($id, $businessId);

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $returnItems = collect($request->input('items', []))
            ->filter(fn ($entry) => (int) ($entry['quantity'] ?? 0) > 0)
            ->mapWithKeys(function ($entry, $productId) {
                return [(int) $productId => (int) $entry['quantity']];
            });

        if ($returnItems->isEmpty()) {
            return back()->with('error', 'Tidak ada item yang dipilih untuk retur.');
        }

        DB::beginTransaction();

        try {
            $totalReturned = 0;

            foreach ($transaction->details as $detail) {
                $returnQuantity = $returnItems->get(
                    (int) $detail->product_id,
                    0
                );

                if ($returnQuantity <= 0) {
                    continue;
                }

                if ($returnQuantity > (int) $detail->quantity) {
                    throw new RuntimeException(
                        'Jumlah retur melebihi jumlah yang dibeli untuk produk ' . ($detail->product->name ?? '')
                    );
                }

                $this->stockMovementService->changeStock(
                    product: $detail->product,
                    type: StockMovement::TYPE_RETURN,
                    quantity: $returnQuantity,
                    reference: $transaction,
                    note: 'Retur ' . $returnQuantity . ' dari transaksi ' . $transaction->invoice_number
                );

                $totalReturned += $returnQuantity;
            }

            if ($totalReturned <= 0) {
                throw new RuntimeException('Tidak ada produk valid yang di-retur.');
            }

            // Retur sebagian item -> tetap status returned.
            $transaction->update([
                'status' => Transaction::STATUS_RETURNED,
            ]);

            DB::commit();

            return redirect()
                ->route('pos.transactions.show', $transaction->id)
                ->with('success', 'Retur ' . $totalReturned . ' unit berhasil diproses.');
        } catch (Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->route('pos.transactions.show', $transaction->id)
                ->with('error', 'Gagal memproses retur: ' . $exception->getMessage());
        }
    }

    /**
     * Mengambil transaksi milik bisnis aktif yang masih dapat diubah.
     */
    private function findEditableTransaction(int $id, int $businessId): Transaction
    {
        $transaction = Transaction::with(['details.product', 'user'])
            ->where('business_id', $businessId)
            ->findOrFail($id);

        if ($transaction->status !== Transaction::STATUS_COMPLETED) {
            throw new RuntimeException(
                'Transaksi sudah diproses '
                . $transaction->status_label
                . ' dan tidak dapat diubah kembali.'
            );
        }

        return $transaction;
    }

    /**
     * Mengembalikan seluruh stok item transaksi ke produk.
     */
    private function restockTransactionItems(
        Transaction $transaction,
        string $reason
    ): void {
        foreach ($transaction->details as $detail) {
            $product = $detail->product;

            if (!$product) {
                continue;
            }

            $this->stockMovementService->changeStock(
                product: $product,
                type: StockMovement::TYPE_RETURN,
                quantity: (int) $detail->quantity,
                reference: $transaction,
                note: 'Stok kembali karena transaksi ' . $reason . ' (' . $transaction->invoice_number . ')'
            );
        }
    }

    /**
     * Alias method untuk route pos.history.
     */
    public function history(Request $request)
    {
        return $this->transactionsHistory($request);
    }
}

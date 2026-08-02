<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Menampilkan halaman inventory.
     */
    public function index(Request $request)
    {
        // Business ID selalu diambil dari user yang sedang login.
        $businessId = Auth::user()->business_id;

        // Query utama hanya mengambil produk milik bisnis yang login.
        $query = Product::where('business_id', $businessId);

        // ===== PENCARIAN =====
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // ===== FILTER KATEGORI =====
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // ===== FILTER STATUS STOK =====
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'optimal':
                    $query
                        ->whereColumn('stock', '>=', 'minimum_stock')
                        ->whereColumn('stock', '<=', 'maximum_stock');
                    break;

                case 'kritis':
                    $query->whereColumn(
                        'stock',
                        '<',
                        'minimum_stock'
                    );
                    break;

                case 'peringatan':
                    $query->whereColumn(
                        'stock',
                        '>',
                        'maximum_stock'
                    );
                    break;
            }
        }

        // ===== FILTER RANGE STOK =====
        if ($request->filled('stock_min')) {
            $query->where('stock', '>=', $request->stock_min);
        }

        if ($request->filled('stock_max')) {
            $query->where('stock', '<=', $request->stock_max);
        }

        // ===== FILTER RANGE HARGA JUAL =====
        if ($request->filled('price_min')) {
            $query->where(
                'selling_price',
                '>=',
                $request->price_min
            );
        }

        if ($request->filled('price_max')) {
            $query->where(
                'selling_price',
                '<=',
                $request->price_max
            );
        }

        // ===== SORTING =====
        switch ($request->get('sort')) {
            case 'name':
                $query->orderBy('name');
                break;

            case 'stock':
                $query->orderBy('stock');
                break;

            case 'price':
                $query->orderBy('selling_price');
                break;

            default:
                $query->latest();
                break;
        }

        // Produk hasil filter dan pencarian.
        $products = $query->paginate(10);

        // Daftar kategori hanya berasal dari bisnis yang login.
        $categories = Product::where('business_id', $businessId)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Kode produk berikutnya untuk modal tambah barang.
        $nextCode = $this->generateCode();

        // ==========================================
        // RINGKASAN INVENTORY
        // ==========================================

        // Jumlah seluruh produk milik bisnis yang login.
        $totalProducts = Product::where(
            'business_id',
            $businessId
        )->count();

        // Jumlah produk dengan stok di bawah minimum.
        $criticalProductsCount = Product::where(
            'business_id',
            $businessId
        )
            ->whereColumn('stock', '<', 'minimum_stock')
            ->count();

        // Jumlah produk dengan stok melebihi maksimum.
        $overstockProductsCount = Product::where(
            'business_id',
            $businessId
        )
            ->whereColumn('stock', '>', 'maximum_stock')
            ->count();

        /*
         * Ambil maksimal tiga produk yang paling membutuhkan restok.
         *
         * Tingkat kebutuhan ditentukan dari selisih:
         * minimum_stock - stock.
         */
        $restockRecommendations = Product::where(
            'business_id',
            $businessId
        )
            ->whereColumn('stock', '<', 'minimum_stock')
            ->orderByRaw('(minimum_stock - stock) DESC')
            ->limit(3)
            ->get();

        /*
         * Estimasi biaya restok dihitung menggunakan:
         *
         * (maximum_stock - stock) × purchase_price
         */
        $estimatedRestockCost = $restockRecommendations->sum(
            function ($product) {
                $recommendedQuantity = max(
                    0,
                    $product->maximum_stock - $product->stock
                );

                return $recommendedQuantity
                    * $product->purchase_price;
            }
        );

        return view('inventory.index', compact(
            'products',
            'categories',
            'nextCode',
            'totalProducts',
            'criticalProductsCount',
            'overstockProductsCount',
            'restockRecommendations',
            'estimatedRestockCost'
        ));
    }

    /**
     * Membuat kode produk otomatis dengan format BRG-XXXX.
     *
     * Nomor produk dibuat terpisah berdasarkan business_id.
     */
    private function generateCode()
    {
        $businessId = Auth::user()->business_id;

        $lastProduct = Product::where(
            'business_id',
            $businessId
        )
            ->where('product_code', 'LIKE', 'BRG-%')
            ->orderBy('product_code', 'desc')
            ->first();

        if (!$lastProduct) {
            return 'BRG-0001';
        }

        // Contoh: BRG-0123 menjadi angka 123.
        $lastNumber = (int) substr(
            $lastProduct->product_code,
            4
        );

        $newNumber = str_pad(
            $lastNumber + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        return 'BRG-' . $newNumber;
    }

    /**
     * Endpoint untuk mengambil kode barang otomatis.
     */
    public function generateCodeAjax()
    {
        return response()->json([
            'code' => $this->generateCode(),
        ]);
    }

    /**
     * Menyimpan produk baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->where(
                    fn ($query) => $query->where(
                        'business_id',
                        Auth::user()->business_id
                    )
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'minimum_stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'maximum_stock' => [
                'required',
                'integer',
                'min:1',
                'gte:minimum_stock',
            ],

            'unit' => [
                'required',
                'string',
                'max:30',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'stock.min' =>
                'Stok tidak boleh kurang dari 0.',

            'minimum_stock.min' =>
                'Stok minimum tidak boleh kurang dari 0.',

            'maximum_stock.min' =>
                'Stok maksimum minimal 1.',

            'maximum_stock.gte' =>
                'Stok maksimum harus lebih besar atau sama dengan stok minimum.',

            'purchase_price.min' =>
                'Harga beli tidak boleh kurang dari 0.',

            'selling_price.min' =>
                'Harga jual tidak boleh kurang dari 0.',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $imageName = uniqid()
                . '_'
                . $request->file('image')->getClientOriginalName();

            $request
                ->file('image')
                ->storeAs('products', $imageName, 'public');
        }

        Product::create([
            // Business ID tidak diambil dari form.
            'business_id' => Auth::user()->business_id,

            'product_code' => $request->product_code,
            'name' => $request->name,
            'category' => $request->category,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'minimum_stock' => $request->minimum_stock,
            'maximum_stock' => $request->maximum_stock,
            'unit' => $request->unit,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return redirect()
            ->route('inventory')
            ->with(
                'success',
                'Barang berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan detail produk.
     */
    public function show($id)
    {
        $product = Product::where(
            'business_id',
            Auth::user()->business_id
        )->findOrFail($id);

        return view(
            'inventory.detail',
            compact('product')
        );
    }

    /**
     * Mengambil data produk untuk modal edit.
     */
    public function edit($id)
    {
        $product = Product::where(
            'business_id',
            Auth::user()->business_id
        )->findOrFail($id);

        return response()->json($product);
    }

    /**
     * Memperbarui produk.
     */
    public function update(Request $request, $id)
    {
        /*
         * Produk hanya boleh ditemukan apabila business_id produk
         * sama dengan business_id user yang sedang login.
         */
        $product = Product::where(
            'business_id',
            Auth::user()->business_id
        )->findOrFail($id);

        $request->validate([
            'product_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')
                    ->ignore($product->id)
                    ->where(
                        fn ($query) => $query->where(
                            'business_id',
                            Auth::user()->business_id
                        )
                    ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'minimum_stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'maximum_stock' => [
                'required',
                'integer',
                'min:1',
                'gte:minimum_stock',
            ],

            'unit' => [
                'required',
                'string',
                'max:30',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'stock.min' =>
                'Stok tidak boleh kurang dari 0.',

            'minimum_stock.min' =>
                'Stok minimum tidak boleh kurang dari 0.',

            'maximum_stock.min' =>
                'Stok maksimum minimal 1.',

            'maximum_stock.gte' =>
                'Stok maksimum harus lebih besar atau sama dengan stok minimum.',

            'purchase_price.min' =>
                'Harga beli tidak boleh kurang dari 0.',

            'selling_price.min' =>
                'Harga jual tidak boleh kurang dari 0.',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete(
                    'products/' . $product->image
                );
            }

            $imageName = uniqid()
                . '_'
                . $request->file('image')->getClientOriginalName();

            $request
                ->file('image')
                ->storeAs('products', $imageName, 'public');

            $product->image = $imageName;
        }

        $product->fill($request->only([
            'product_code',
            'name',
            'category',
            'purchase_price',
            'selling_price',
            'stock',
            'minimum_stock',
            'maximum_stock',
            'unit',
            'description',
        ]));

        $product->save();

        return back()->with(
            'success',
            'Barang berhasil diperbarui.'
        );
    }

    /**
     * Menghapus produk.
     */
    public function destroy($id)
    {
        $product = Product::where(
            'business_id',
            Auth::user()->business_id
        )->findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete(
                'products/' . $product->image
            );
        }

        $product->delete();

        return back()->with(
            'success',
            'Barang berhasil dihapus.'
        );
    }

    /**
     * Mencari produk berdasarkan nama.
     */
    public function search(Request $request)
    {
        return Product::where(
            'business_id',
            Auth::user()->business_id
        )
            ->where(
                'name',
                'like',
                '%' . $request->keyword . '%'
            )
            ->get();
    }

    /**
     * Mengambil hasil prediksi dari prediction service.
     */
    public function getPrediction()
    {
        $service = new PredictionService();
        $prediction = $service->getPrediction();

        return response()->json($prediction);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('business_id', Auth::user()->business_id);

        // ===== SEARCH =====
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
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
                    $query->whereRaw('stock >= minimum_stock AND stock <= maximum_stock');
                    break;
                case 'kritis':
                    $query->where('stock', '<', 'minimum_stock');
                    break;
                case 'peringatan':
                    $query->where('stock', '>', 'maximum_stock');
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
            $query->where('selling_price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('selling_price', '<=', $request->price_max);
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
        }

        $products = $query->paginate(10);

        // Ambil daftar kategori unik untuk dropdown filter
        $categories = Product::where('business_id', Auth::user()->business_id)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Generate kode barang otomatis untuk modal tambah
        $nextCode = $this->generateCode();

        return view('inventory.index', compact('products', 'categories', 'nextCode'));
    }

    /**
     * Generate kode barang otomatis dengan format BRG-XXXX
     * Unique per business_id
     */
    private function generateCode()
    {
        $businessId = Auth::user()->business_id;

        // Ambil produk terakhir berdasarkan business_id dengan kode tertinggi
        $lastProduct = Product::where('business_id', $businessId)
            ->where('product_code', 'LIKE', 'BRG-%')
            ->orderBy('product_code', 'desc')
            ->first();

        if (!$lastProduct) {
            return 'BRG-0001';
        }

        // Ambil angka dari kode terakhir (misal BRG-0123 -> 123)
        $lastNumber = (int) substr($lastProduct->product_code, 4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return 'BRG-' . $newNumber;
    }

    /**
     * Endpoint AJAX untuk generate kode baru
     */
    public function generateCodeAjax()
    {
        return response()->json(['code' => $this->generateCode()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code' => [
                'required', 'string', 'max:50',
                Rule::unique('products')->where(fn($q) => $q->where('business_id', Auth::user()->business_id))
            ],
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'required|integer|min:1',
            'unit' => 'required|string|max:30',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = uniqid() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('products', $imageName, 'public');
        }

        Product::create([
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
            'image' => $imageName
        ]);

        return redirect()->route('inventory')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $product = Product::where('business_id', Auth::user()->business_id)->findOrFail($id);
        return view('inventory.detail', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::where('business_id', Auth::user()->business_id)->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('business_id', Auth::user()->business_id)->findOrFail($id);

        $request->validate([
            'product_code' => [
                'required', 'max:50',
                Rule::unique('products')->ignore($product->id)->where(fn($q) => $q->where('business_id', Auth::user()->business_id))
            ],
            'name' => 'required|max:255',
            'category' => 'nullable|max:100',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock' => 'required|integer',
            'minimum_stock' => 'required|integer',
            'maximum_stock' => 'required|integer',
            'unit' => 'required|max:30',
            'description' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete('products/' . $product->image);
            }
            $imageName = uniqid() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('products', $imageName, 'public');
            $product->image = $imageName;
        }

        $product->fill($request->only([
            'product_code', 'name', 'category', 'purchase_price', 'selling_price',
            'stock', 'minimum_stock', 'maximum_stock', 'unit', 'description'
        ]));
        $product->save();

        return back()->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = Product::where('business_id', Auth::user()->business_id)->findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete('products/' . $product->image);
        }

        $product->delete();

        return back()->with('success', 'Barang berhasil dihapus.');
    }

    public function search(Request $request)
    {
        return Product::where('business_id', Auth::user()->business_id)
            ->where('name', 'like', '%' . $request->keyword . '%')
            ->get();
    }
}
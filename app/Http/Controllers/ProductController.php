<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Menampilkan halaman inventory
     */
    public function index()
    {
        $products = Product::where(
            'business_id',
            Auth::user()->business_id
        )
        ->latest()
        ->get();

        return view('inventory.index', compact('products'));
    }

    /**
     * Simpan barang baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string|max:50|unique:products,product_code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:30',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {

            $imageName = time() . '_' . $request->image->getClientOriginalName();

            $request->image->storeAs(
                'products',
                $imageName,
                'public'
            );
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

            'unit' => $request->unit,

            'description' => $request->description,

            'image' => $imageName

        ]);

        return redirect()
            ->route('inventory')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Detail Barang
     */
    public function show($id)
    {
        $product = Product::where('business_id', Auth::user()->business_id)
            ->findOrFail($id);

        return view('inventory.detail', compact('product'));
    }

    /**
     * Update Barang
     */
    public function update(Request $request, $id)
    {
        $product = Product::where(
            'business_id',
            Auth::user()->business_id
        )->findOrFail($id);

        $request->validate([
            'product_code' => 'required|max:50|unique:products,product_code,' . $product->id,
            'name' => 'required|max:255',
            'category' => 'nullable|max:100',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock' => 'required|integer',
            'minimum_stock' => 'required|integer',
            'unit' => 'required|max:30',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {

            if ($product->image) {

                Storage::disk('public')
                    ->delete('products/' . $product->image);
            }

            $imageName = time() . '_' . $request->image->getClientOriginalName();

            $request->image->storeAs(
                'products',
                $imageName,
                'public'
            );

            $product->image = $imageName;
        }

        $product->product_code = $request->product_code;
        $product->name = $request->name;
        $product->category = $request->category;
        $product->purchase_price = $request->purchase_price;
        $product->selling_price = $request->selling_price;
        $product->stock = $request->stock;
        $product->minimum_stock = $request->minimum_stock;
        $product->unit = $request->unit;
        $product->description = $request->description;

        $product->save();

        return back()
            ->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Hapus Barang
     */
    public function destroy($id)
    {
        $product = Product::where(
            'business_id',
            Auth::user()->business_id
        )->findOrFail($id);

        if ($product->image) {

            Storage::disk('public')
                ->delete('products/' . $product->image);
        }

        $product->delete();

        return back()
            ->with('success', 'Barang berhasil dihapus.');
    }
}
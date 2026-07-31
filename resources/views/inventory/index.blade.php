@extends('layouts.app')

@section('title', 'Manajemen Inventori - UsahaMate')

@section('content')
<div class="flex flex-col lg:flex-row gap-6 w-full">
    
    <!-- ========================================== -->
    <!-- KOLOM KIRI (TABEL INVENTORI)                -->
    <!-- ========================================== -->
    <div class="flex-1 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Inventori</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola tingkat stok Anda dan pantau kesehatan produk secara real-time.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                    + Tambah Barang
                </button>
                <button onclick="openFilterModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter
                    @if(request()->hasAny(['category', 'status', 'stock_min', 'stock_max', 'price_min', 'price_max']))
                        <span class="ml-1 px-1.5 py-0.5 bg-blue-600 text-white text-[10px] rounded-full">●</span>
                    @endif
                </button>
                <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Ekspor
                </button>
            </div>
        </div>

        <!-- ===== FORM PENCARIAN ===== -->
        <form action="{{ route('inventory') }}" method="GET" class="mb-6 flex flex-wrap gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari produk berdasarkan nama, kode, atau kategori..." 
                    value="{{ request('search') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
                <svg class="w-4 h-4 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">Cari</button>
            @if(request()->hasAny(['search', 'category', 'status', 'stock_min', 'stock_max', 'price_min', 'price_max']))
                <a href="{{ route('inventory') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Reset Semua</a>
            @endif
        </form>

        <!-- Filter Kategori (chip) -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('inventory', array_merge(request()->except('category'), ['search' => request('search')])) }}" 
               class="px-4 py-1.5 rounded-full text-xs font-medium {{ !request('category') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">
                Semua Item
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('inventory', array_merge(request()->except('category'), ['category' => $cat, 'search' => request('search')])) }}" 
                   class="px-4 py-1.5 rounded-full text-xs font-medium {{ request('category') == $cat ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- Informasi total data -->
        <div class="text-sm text-gray-500 mb-3 flex justify-between items-center flex-wrap gap-2">
            <span>Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk</span>
            @if(request()->hasAny(['category', 'status', 'stock_min', 'stock_max', 'price_min', 'price_max']))
                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full">Filter aktif</span>
            @endif
        </div>

        <!-- Tabel Produk -->
        <div class="border border-gray-200 rounded-xl overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Minimum Stok</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Maksimum Stok</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-blue-50 transition duration-150">
                        <!-- Nama Produk (link ke detail) -->
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden border border-gray-200 bg-gray-100">
                                    @if($product->image)
                                        <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-lg">📦</div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('inventory.detail', $product->id) }}" class="text-sm font-semibold text-gray-900 hover:underline">
                                        {{ $product->name }}
                                    </a>
                                    <div class="text-xs text-gray-400">SKU: {{ $product->product_code }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Stok Saat Ini (progress bar) -->
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex flex-col">
                                <div class="text-xs text-gray-900 font-medium mb-1">
                                    Kapasitas <span class="text-blue-600">{{ $product->stock }}</span> / {{ max($product->maximum_stock,1) }}
                                </div>
                                <div class="w-24 h-1.5 bg-gray-200 rounded-full">
                                    <div class="h-1.5 rounded-full {{ $product->status == 'kritis' ? 'bg-red-500' : 'bg-blue-600' }}" 
                                         style="width: {{ ($product->stock / max($product->maximum_stock,1))*100 }}%"></div>
                                </div>
                            </div>
                        </td>

                        <!-- Minimum Stok -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-medium">
                            {{ $product->minimum_stock }} Unit
                        </td>

                        <!-- Maksimum Stok -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-medium">
                            {{ $product->maximum_stock }} Unit
                        </td>

                        <!-- Harga Jual -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-800">
                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-5 whitespace-nowrap text-center">
                            @if($product->status == 'optimal')
                                <span class="px-3 inline-flex text-[11px] leading-5 font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">Optimal</span>
                            @elseif($product->status == 'kritis')
                                <span class="px-3 inline-flex text-[11px] leading-5 font-bold rounded-full bg-red-100 text-red-700 border border-red-200">Kritis</span>
                            @else
                                <span class="px-3 inline-flex text-[11px] leading-5 font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-200">Peringatan</span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="px-6 py-5 whitespace-nowrap text-center">
                            <button onclick="openEditModal({{ $product->id }})" class="text-blue-600 hover:text-blue-800 mr-2" title="Edit">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="{{ route('inventory.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-base font-medium">Tidak ada produk ditemukan</span>
                                <span class="text-sm">Coba ubah kata kunci pencarian atau filter kategori</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- ========================================== -->
    <!-- KOLOM KANAN (PREDIKSI AI & REKOMENDASI)     -->
    <!-- ========================================== -->
    <div class="w-full lg:w-[340px] flex flex-col gap-4">
        <!-- Kartu 1: Prediksi Minggu Depan -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-1">
                <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Permintaan Minggu Depan</div>
            </div>
            <div class="text-2xl font-bold text-gray-800 mb-1">+18%</div>
            <div class="text-[11px] text-green-600 font-medium flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                vs minggu lalu
            </div>
        </div>

        <!-- Kartu 2: Rekomendasi Restok -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex-1">
            <h4 class="text-sm font-bold text-gray-800 mb-5">Rekomendasi Restok</h4>
            <div class="space-y-4 pb-5 border-b border-gray-100 mb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">OmniWatch S7</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-blue-600">+120 unit</p>
                        <p class="text-[11px] text-gray-400">est. $8,400</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Nexus V Pro</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-blue-600">+45 unit</p>
                        <p class="text-[11px] text-gray-400">est. $6,250</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-500 font-medium">Estimasi Total Biaya</span>
                <span class="text-lg font-bold text-gray-800">$14,650</span>
            </div>
            <a href="#" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-3 rounded-xl transition duration-200 shadow-sm">
                Buat Pesanan Pembelian
            </a>
        </div>

        <!-- Kartu 3: Tip Optimalisasi -->
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                </div>
                <h4 class="text-base font-bold text-gray-800">Tip Optimalisasi</h4>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">
                Mengurangi stok OmniWatch sebesar 15% selama musim puncak dapat meningkatkan arus kas sebesar $12rb.
            </p>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TAMBAH BARANG                        -->
<!-- ========================================== -->
<div id="productModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-6">Tambah Barang</h2>
        <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <!-- Kode Barang dengan auto-generate -->
                <div class="relative">
                    <input type="text" name="product_code" id="product_code" placeholder="Kode Barang" class="border rounded-lg p-2 w-full" required>
                    <button type="button" onclick="generateCode()" class="absolute right-2 top-2 text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200" title="Generate ulang kode">
                        ↻
                    </button>
                </div>
                <input type="text" name="name" placeholder="Nama Barang" class="border rounded-lg p-2" required>
                <input type="text" name="category" placeholder="Kategori" class="border rounded-lg p-2">
                <input type="text" name="unit" value="Pcs" class="border rounded-lg p-2">
                <input type="number" name="purchase_price" placeholder="Harga Beli" class="border rounded-lg p-2" required>
                <input type="number" name="selling_price" placeholder="Harga Jual" class="border rounded-lg p-2" required>
                <input type="number" name="stock" placeholder="Stok" class="border rounded-lg p-2" required>
                <input type="number" name="minimum_stock" placeholder="Stok Minimum" class="border rounded-lg p-2" value="10" required>
                <input type="number" name="maximum_stock" placeholder="Stok Maksimum" class="border rounded-lg p-2" required>
                <div class="col-span-2">
                    <input type="file" name="image" class="border rounded-lg p-2 w-full">
                    <p class="text-xs text-gray-500 mt-1">Ukuran maksimal 2MB (jpg, jpeg, png, webp)</p>
                </div>
                <div class="col-span-2">
                    <textarea name="description" class="border rounded-lg p-2 w-full" placeholder="Deskripsi (opsional)"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeModal()" class="border px-4 py-2 rounded-lg">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL EDIT BARANG                          -->
<!-- ========================================== -->
<div id="editModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-6">Edit Barang</h2>
        <form id="editForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="product_code" id="edit_product_code" placeholder="Kode Barang" class="border rounded-lg p-2" required>
                <input type="text" name="name" id="edit_name" placeholder="Nama Barang" class="border rounded-lg p-2" required>
                <input type="text" name="category" id="edit_category" placeholder="Kategori" class="border rounded-lg p-2">
                <input type="text" name="unit" id="edit_unit" class="border rounded-lg p-2">
                <input type="number" name="purchase_price" id="edit_purchase_price" placeholder="Harga Beli" class="border rounded-lg p-2" required>
                <input type="number" name="selling_price" id="edit_selling_price" placeholder="Harga Jual" class="border rounded-lg p-2" required>
                <input type="number" name="stock" id="edit_stock" placeholder="Stok" class="border rounded-lg p-2" required>
                <input type="number" name="minimum_stock" id="edit_minimum_stock" placeholder="Stok Minimum" class="border rounded-lg p-2" required>
                <input type="number" name="maximum_stock" id="edit_maximum_stock" placeholder="Stok Maksimum" class="border rounded-lg p-2" required>
                <div class="col-span-2">
                    <input type="file" name="image" class="border rounded-lg p-2 w-full">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah gambar (maks 2MB)</p>
                </div>
                <div class="col-span-2">
                    <textarea name="description" id="edit_description" class="border rounded-lg p-2 w-full" placeholder="Deskripsi (opsional)"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeEditModal()" class="border px-4 py-2 rounded-lg">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Update Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL FILTER                               -->
<!-- ========================================== -->
<div id="filterModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Filter Produk</h2>
            <button onclick="closeFilterModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('inventory') }}" method="GET" id="filterForm">
            <!-- Pertahankan parameter search -->
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <!-- Kategori -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Stok -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Stok</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="optimal" {{ request('status') == 'optimal' ? 'selected' : '' }}>Optimal</option>
                    <option value="kritis" {{ request('status') == 'kritis' ? 'selected' : '' }}>Kritis</option>
                    <option value="peringatan" {{ request('status') == 'peringatan' ? 'selected' : '' }}>Peringatan (Melebihi Maks)</option>
                </select>
            </div>

            <!-- Range Stok -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Range Stok</label>
                <div class="flex gap-2">
                    <input type="number" name="stock_min" placeholder="Min" value="{{ request('stock_min') }}" class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <input type="number" name="stock_max" placeholder="Max" value="{{ request('stock_max') }}" class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <!-- Range Harga Jual -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Range Harga Jual</label>
                <div class="flex gap-2">
                    <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}" class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}" class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <!-- Tombol -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">Terapkan Filter</button>
                @if(request()->hasAny(['category', 'status', 'stock_min', 'stock_max', 'price_min', 'price_max']))
                    <a href="{{ route('inventory', ['search' => request('search')]) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
    // Variabel untuk menyimpan kode awal dari controller
    let nextCode = "{{ $nextCode ?? 'BRG-0001' }}";

    // Modal Tambah
    function openModal() {
        // Isi field kode dengan kode terbaru
        document.getElementById('product_code').value = nextCode;
        // Tampilkan modal
        document.getElementById('productModal').classList.remove('hidden');
        document.getElementById('productModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('productModal').classList.remove('flex');
        document.getElementById('productModal').classList.add('hidden');
    }

    // Generate kode baru dengan memanggil endpoint
    function generateCode() {
        fetch('{{ route("inventory.generate") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('product_code').value = data.code;
                nextCode = data.code; // simpan untuk digunakan lagi
            })
            .catch(error => {
                alert('Gagal generate kode, silakan coba lagi.');
                console.error(error);
            });
    }

    // Modal Edit
    function openEditModal(id) {
        const url = "{{ route('inventory.edit', ['id' => ':id']) }}".replace(':id', id);
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Server merespon dengan status ${response.status}: ${text.substring(0, 100)}...`);
                });
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('edit_product_code').value = data.product_code;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_category').value = data.category || '';
            document.getElementById('edit_unit').value = data.unit;
            document.getElementById('edit_purchase_price').value = data.purchase_price;
            document.getElementById('edit_selling_price').value = data.selling_price;
            document.getElementById('edit_stock').value = data.stock;
            document.getElementById('edit_minimum_stock').value = data.minimum_stock;
            document.getElementById('edit_maximum_stock').value = data.maximum_stock;
            document.getElementById('edit_description').value = data.description || '';

            document.getElementById('editForm').action = `/inventory/${id}`;

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        })
        .catch(error => {
            alert('Gagal mengambil data produk!\n' + error.message);
            console.error('Error detail:', error);
        });
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Modal Filter
    function openFilterModal() {
        document.getElementById('filterModal').classList.remove('hidden');
        document.getElementById('filterModal').classList.add('flex');
    }

    function closeFilterModal() {
        document.getElementById('filterModal').classList.remove('flex');
        document.getElementById('filterModal').classList.add('hidden');
    }
</script>

@endsection
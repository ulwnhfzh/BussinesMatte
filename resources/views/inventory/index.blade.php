@extends('layouts.app')

{{-- Versi inventory dengan sidebar AI dan gambar produk terintegrasi --}}

@section('title', 'Manajemen Inventori - UsahaMate')

@section('content')
{{-- Notifikasi validasi gagal --}}
@if ($errors->any())
    <div
        class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4"
        role="alert"
    >
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 text-red-600">
                ⚠️
            </div>

            <div>
                <h3 class="text-sm font-bold text-red-800">
                    Data barang gagal disimpan
                </h3>

                <ul class="mt-2 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm text-red-700">
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

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
            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('inventory.stock-movements') }}"
                    class="px-4 py-2 border border-blue-200 bg-blue-50 text-blue-700 rounded-lg text-sm hover:bg-blue-100 flex items-center gap-2 transition"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    Riwayat Stok
                </a>

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
                                <div class="w-24 h-1.5 bg-gray-200 rounded-full overflow-hidden">
    <div
        class="h-1.5 rounded-full
            {{ $product->status === 'kritis'
                ? 'bg-red-500'
                : ($product->status === 'peringatan'
                    ? 'bg-amber-500'
                    : 'bg-emerald-500') }}"
        style="width: {{
            min(
                100,
                max(
                    0,
                    ($product->stock / max($product->maximum_stock, 1)) * 100
                )
            )
        }}%">
    </div>
</div>
                            </div>
                        </td>

                        <!-- Minimum Stok -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-medium">
                            {{ $product->minimum_stock }} {{ $product->unit }}
                        </td>

                        <!-- Maksimum Stok -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-medium">
                            {{ $product->maximum_stock }} {{ $product->unit }}
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
                                <span class="px-3 inline-flex text-[11px] leading-5 font-bold rounded-full bg-amber-100 text-amber-700 border border-amber-200">Peringatan</span>
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
    <!-- KOLOM KANAN: INVENTORY & AI ASSISTANT     -->
    <!-- ========================================== -->
    <div class="w-full lg:w-[340px] flex flex-col gap-4">
        <!-- Ringkasan kondisi seluruh inventory -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Ringkasan Inventory</h3>
                    <p class="text-xs text-gray-400 mt-1">Kondisi seluruh produk bisnis Anda</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-xl">📦</div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="rounded-xl bg-gray-50 border border-gray-100 p-3 text-center">
                    <p class="text-xl font-bold text-gray-800">{{ $totalProducts }}</p>
                    <p class="text-[10px] text-gray-500 mt-1">Total</p>
                </div>

                <div class="rounded-xl bg-red-50 border border-red-100 p-3 text-center">
                    <p class="text-xl font-bold text-red-600">{{ $criticalProductsCount }}</p>
                    <p class="text-[10px] text-red-500 mt-1">Kritis</p>
                </div>

                <div class="rounded-xl bg-amber-50 border border-amber-100 p-3 text-center">
                    <p class="text-xl font-bold text-amber-600">{{ $overstockProductsCount }}</p>
                    <p class="text-[10px] text-amber-600 mt-1">Berlebih</p>
                </div>
            </div>
        </div>

        <!-- Forecast AI -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 shadow-sm text-white overflow-hidden relative">
            <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-white/10"></div>

            <div class="relative">
                <div class="flex items-start justify-between gap-3 mb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg">✨</span>
                            <h3 class="text-sm font-bold">Asisten AI Inventory</h3>
                        </div>
                        <p id="ai-service-text" class="text-[11px] text-blue-100 mt-1">
                            Menghubungkan service prediksi...
                        </p>
                    </div>

                    <button
                        type="button"
                        onclick="loadInventoryPrediction()"
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition"
                        title="Muat ulang prediksi"
                    >
                        ↻
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span id="ai-service-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-[10px] font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-300 animate-pulse"></span>
                        Memuat
                    </span>
                    <span id="ai-mode-badge" class="px-2.5 py-1 rounded-full bg-white/15 text-[10px] font-semibold">
                        Menunggu data
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-white/10 border border-white/10 p-3">
                        <p class="text-[10px] text-blue-100">Permintaan 7 Hari</p>
                        <p class="mt-1">
                            <span id="ai-predicted-total" class="text-2xl font-bold">—</span>
                            <span class="text-xs text-blue-100">unit</span>
                        </p>
                    </div>

                    <div class="rounded-xl bg-white/10 border border-white/10 p-3">
                        <p class="text-[10px] text-blue-100">Dibanding Minggu Lalu</p>
                        <p id="ai-percentage" class="text-2xl font-bold mt-1">—</p>
                    </div>
                </div>

                <p id="ai-summary" class="text-[11px] leading-relaxed text-blue-100 mt-4">
                    Prediksi sedang dihitung dari histori penjualan bisnis Anda.
                </p>
            </div>
        </div>

        <!-- Rekomendasi restok dari hasil prediksi -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex-1">
            <div class="flex items-start justify-between gap-3 mb-5">
                <div>
                    <h4 class="text-sm font-bold text-gray-800">Rekomendasi Restok AI</h4>
                    <p class="text-[11px] text-gray-400 mt-1">Forecast + safety stock, dibatasi stok maksimum</p>
                </div>
                <span id="ai-restock-count" class="px-2 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold">—</span>
            </div>

            <div id="ai-restock-list" class="space-y-4 pb-5 border-b border-gray-100 mb-4">
                <div class="py-6 text-center">
                    <div class="w-8 h-8 mx-auto rounded-full border-2 border-blue-100 border-t-blue-600 animate-spin"></div>
                    <p class="text-xs text-gray-400 mt-3">Memuat rekomendasi...</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <span class="text-sm text-gray-500 font-medium">Estimasi Total Biaya</span>
                <span id="ai-restock-cost" class="text-lg font-bold text-gray-800 text-right">Rp 0</span>
            </div>
        </div>

        <!-- Tip optimalisasi dinamis -->
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-sm">💡</div>
                <div>
                    <h4 class="text-sm font-bold text-gray-800">Tip Optimalisasi</h4>
                    <p id="ai-tip-source" class="text-[10px] text-gray-400">Menunggu hasil analisis</p>
                </div>
            </div>

            <p id="ai-optimization-tip" class="text-sm text-gray-600 leading-relaxed">
                Asisten sedang membaca kondisi stok dan histori penjualan bisnis Anda.
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

        <!-- HARGA BELI (Disesuaikan) -->
        <div class="relative flex items-center">
            <span class="absolute left-3 text-gray-500 text-sm font-semibold">Rp</span>
            <input type="text" id="purchase_price_display" placeholder="Harga Beli" class="border rounded-lg p-2 pl-10 w-full" onkeyup="formatRupiahInput(this, 'purchase_price')" required>
            <input type="hidden" name="purchase_price" id="purchase_price" required>
        </div>

        <!-- HARGA JUAL (Disesuaikan) -->
        <div class="relative flex items-center">
            <span class="absolute left-3 text-gray-500 text-sm font-semibold">Rp</span>
            <input type="text" id="selling_price_display" placeholder="Harga Jual" class="border rounded-lg p-2 pl-10 w-full" onkeyup="formatRupiahInput(this, 'selling_price')" required>
            <input type="hidden" name="selling_price" id="selling_price" required>
        </div>

        <input type="number" name="stock" min="0" placeholder="Stok" class="border rounded-lg p-2" required>
        <input type="number" name="minimum_stock" min="0" placeholder="Stok Minimum" class="border rounded-lg p-2" value="10" required>
        <input type="number" name="maximum_stock" min="1" placeholder="Stok Maksimum" class="border rounded-lg p-2" required>
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

                <!-- HARGA BELI EDIT (Diperbaiki) -->
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-500 text-sm font-semibold">Rp</span>
                    <input type="text" id="edit_purchase_price_display" placeholder="Harga Beli" class="border rounded-lg p-2 pl-10 w-full" onkeyup="formatRupiahInput(this, 'edit_purchase_price')" required>
                    <input type="hidden" name="purchase_price" id="edit_purchase_price" required>
                </div>

                <!-- HARGA JUAL EDIT (Diperbaiki) -->
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-500 text-sm font-semibold">Rp</span>
                    <input type="text" id="edit_selling_price_display" placeholder="Harga Jual" class="border rounded-lg p-2 pl-10 w-full" onkeyup="formatRupiahInput(this, 'edit_selling_price')" required>
                    <input type="hidden" name="selling_price" id="edit_selling_price" required>
                </div>

                <input type="number" name="stock" id="edit_stock" min="0" placeholder="Stok" class="border rounded-lg p-2" required>
                <input type="number" name="minimum_stock" id="edit_minimum_stock" min="0" placeholder="Stok Minimum" class="border rounded-lg p-2" required>
                <input type="number" name="maximum_stock" id="edit_maximum_stock" min="1" placeholder="Stok Maksimum" class="border rounded-lg p-2" required>
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

        // Reset input harga display dan hidden input murni
        document.getElementById('purchase_price_display').value = '';
        document.getElementById('purchase_price').value = '';
        document.getElementById('selling_price_display').value = '';
        document.getElementById('selling_price').value = '';

        // Tampilkan modal
        const modal = document.getElementById('productModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('productModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Generate kode baru dengan memanggil endpoint
    function generateCode() {
        fetch('{{ route("inventory.generate") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('product_code').value = data.code;
                nextCode = data.code; // Simpan untuk digunakan lagi
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

            // Set harga beli (Hidden Input & Formatted Display Input)
            const purchasePrice = parseInt(data.purchase_price) || 0;
            document.getElementById('edit_purchase_price').value = purchasePrice;
            document.getElementById('edit_purchase_price_display').value = purchasePrice ? new Intl.NumberFormat('id-ID').format(purchasePrice) : '';

            // Set harga jual (Hidden Input & Formatted Display Input)
            const sellingPrice = parseInt(data.selling_price) || 0;
            document.getElementById('edit_selling_price').value = sellingPrice;
            document.getElementById('edit_selling_price_display').value = sellingPrice ? new Intl.NumberFormat('id-ID').format(sellingPrice) : '';

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

    // Helper Fungsi Format Rupiah Real-time
    function formatRupiahInput(input, hiddenInputId) {
        // Ambil angka murni saja
        let rawValue = input.value.replace(/[^0-9]/g, '');

        // Simpan angka murni ke hidden input (untuk dikirim ke controller/database)
        const targetElement = document.getElementById(hiddenInputId);
        if (targetElement) {
            targetElement.value = rawValue;
        }

        // Tampilkan dengan format ribuan di layar
        if (rawValue) {
            input.value = new Intl.NumberFormat('id-ID').format(rawValue);
        } else {
            input.value = '';
        }
    }

    // =========================================================
    // AI INVENTORY ASSISTANT
    // =========================================================
    const inventoryPredictionUrl = @json(route('inventory.prediction'));
    const inventoryDetailUrlTemplate = @json(
        route('inventory.detail', ['id' => '__PRODUCT_ID__'])
    );

    let inventoryPredictionIsLoading = false;

    document.addEventListener('DOMContentLoaded', function () {
        loadInventoryPrediction();
    });

    async function loadInventoryPrediction() {
        if (inventoryPredictionIsLoading) {
            return;
        }

        inventoryPredictionIsLoading = true;
        setInventoryPredictionLoadingState();

        try {
            const response = await fetch(inventoryPredictionUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const prediction = await response.json();
            renderInventoryPrediction(prediction);
        } catch (error) {
            console.error('Gagal memuat prediksi inventory:', error);
            renderInventoryPredictionError();
        } finally {
            inventoryPredictionIsLoading = false;
        }
    }

    function setInventoryPredictionLoadingState() {
        document.getElementById('ai-service-text').textContent =
            'Menghitung prediksi terbaru...';

        document.getElementById('ai-service-badge').innerHTML = `
            <span class="w-1.5 h-1.5 rounded-full bg-amber-300 animate-pulse"></span>
            Memuat
        `;
    }

    function renderInventoryPrediction(prediction) {
        renderInventoryServiceStatus(prediction);

        document.getElementById('ai-mode-badge').textContent =
            prediction.mode_label || 'Belum Ada Prediksi';

        document.getElementById('ai-predicted-total').textContent =
            formatInteger(prediction.predicted_total);

        document.getElementById('ai-percentage').textContent =
            formatPercentage(prediction.percentage);

        document.getElementById('ai-summary').textContent =
            prediction.summary || 'Belum ada ringkasan prediksi.';

        const products = Array.isArray(prediction.products)
            ? prediction.products
            : [];

        renderInventoryRestock(products);
        renderInventoryOptimizationTip(prediction, products);
    }

    function renderInventoryServiceStatus(prediction) {
        const serviceBadge = document.getElementById('ai-service-badge');
        const serviceText = document.getElementById('ai-service-text');

        if (prediction.service_status === 'online') {
            serviceBadge.innerHTML = `
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                AI Online
            `;
            serviceText.textContent =
                prediction.service_message || 'Service AI terhubung.';
            return;
        }

        if (prediction.service_status === 'fallback') {
            serviceBadge.innerHTML = `
                <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                Mode Fallback
            `;
            serviceText.textContent =
                prediction.service_message || 'Menggunakan Moving Average lokal.';
            return;
        }

        serviceBadge.innerHTML = `
            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
            Belum Ada Data
        `;
        serviceText.textContent =
            prediction.service_message || 'Data belum mencukupi untuk dianalisis.';
    }

    function renderInventoryRestock(products) {
        const restockList = document.getElementById('ai-restock-list');

        const recommendations = products
            .filter(product => Number(product.recommended_restock) > 0)
            .sort(
                (firstProduct, secondProduct) =>
                    Number(secondProduct.recommended_restock)
                    - Number(firstProduct.recommended_restock)
            );

        const totalCost = recommendations.reduce(
            (total, product) => total + Number(product.estimated_cost || 0),
            0
        );

        document.getElementById('ai-restock-count').textContent =
            `${recommendations.length} produk`;

        document.getElementById('ai-restock-cost').textContent =
            formatRupiah(totalCost);

        if (recommendations.length === 0) {
            restockList.innerHTML = `
                <div class="py-6 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-3">✓</div>
                    <p class="text-sm font-semibold text-emerald-700">Belum perlu restok</p>
                    <p class="text-xs text-gray-400 mt-1">Stok mencukupi forecast tujuh hari.</p>
                </div>
            `;
            return;
        }

        restockList.innerHTML = recommendations
            .slice(0, 4)
            .map(product => {
                const detailUrl = inventoryDetailUrlTemplate.replace(
                    '__PRODUCT_ID__',
                    encodeURIComponent(product.product_id)
                );

                return `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            ${renderInventoryProductImage(product)}

                            <div class="min-w-0">
                                <a
                                    href="${detailUrl}"
                                    class="block text-sm font-semibold text-gray-700 hover:text-blue-600 truncate"
                                >
                                    ${escapeHtml(product.product_name || 'Produk')}
                                </a>

                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[9px] font-semibold">
                                        ${escapeHtml(product.method_label || 'Belum Ada Prediksi')}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        Forecast ${formatInteger(product.predicted_quantity)} ${escapeHtml(product.unit || 'unit')}
                                    </span>
                                </div>

                                <p class="text-[10px] text-gray-400 mt-1 leading-relaxed">
                                    ${escapeHtml(product.method_reason || product.reason || '')}
                                </p>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-blue-600">
                                +${formatInteger(product.recommended_restock)} ${escapeHtml(product.unit || 'unit')}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                ${formatRupiah(product.estimated_cost)}
                            </p>
                        </div>
                    </div>
                `;
            })
            .join('');
    }

    function renderInventoryOptimizationTip(prediction, products) {
        const tipElement = document.getElementById('ai-optimization-tip');
        const sourceElement = document.getElementById('ai-tip-source');

        const recommendations = products.filter(
            product => Number(product.recommended_restock) > 0
        );

        const noDataProducts = products.filter(
            product => product.method === 'no_data'
        );

        const overstockProducts = products.filter(
            product => Number(product.current_stock)
                > Number(product.maximum_stock)
        );

        sourceElement.textContent = `Mode: ${
            prediction.mode_label || 'Belum Ada Prediksi'
        }`;

        if (prediction.service_status === 'fallback') {
            tipElement.textContent =
                'Service AI sedang tidak tersedia. Prediksi tetap berjalan dengan Moving Average; nyalakan kembali service Python untuk menguji Random Forest.';
            return;
        }

        if (recommendations.length > 0) {
            const productNames = recommendations
                .slice(0, 2)
                .map(product => product.product_name)
                .join(' dan ');

            tipElement.textContent =
                `Prioritaskan restok ${productNames}. Jumlah rekomendasi sudah mempertimbangkan forecast, stok minimum, dan kapasitas stok maksimum.`;
            return;
        }

        if (overstockProducts.length > 0) {
            tipElement.textContent =
                `Terdapat ${overstockProducts.length} produk melebihi stok maksimum. Pertimbangkan promosi atau bundling agar perputaran stok lebih cepat.`;
            return;
        }

        if (noDataProducts.length > 0) {
            tipElement.textContent =
                `Terdapat ${noDataProducts.length} produk tanpa histori penjualan. Lakukan transaksi melalui POS agar rekomendasinya mulai terbentuk.`;
            return;
        }

        tipElement.textContent =
            'Stok saat ini mencukupi forecast tujuh hari. Pantau kembali setelah ada transaksi baru agar rekomendasi tetap sesuai kondisi terbaru.';
    }

    function renderInventoryProductImage(product) {
        const productName = escapeHtml(
            product.product_name || 'Produk'
        );

        if (!product.image_url) {
            return `
                <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-blue-100 flex items-center justify-center text-base">
                    📦
                </div>
            `;
        }

        return `
            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden relative">
                <img
                    src="${escapeHtml(product.image_url)}"
                    alt="${productName}"
                    class="w-full h-full object-cover"
                    onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');"
                >
                <div class="hidden absolute inset-0 items-center justify-center text-base bg-blue-100">
                    📦
                </div>
            </div>
        `;
    }

    function renderInventoryPredictionError() {
        document.getElementById('ai-service-badge').innerHTML = `
            <span class="w-1.5 h-1.5 rounded-full bg-red-300"></span>
            Gagal Memuat
        `;

        document.getElementById('ai-service-text').textContent =
            'Endpoint prediksi tidak dapat dibaca.';

        document.getElementById('ai-mode-badge').textContent =
            'Tidak tersedia';

        document.getElementById('ai-predicted-total').textContent = '—';
        document.getElementById('ai-percentage').textContent = '—';
        document.getElementById('ai-summary').textContent =
            'Periksa koneksi Laravel dan service Python, lalu tekan tombol muat ulang.';

        document.getElementById('ai-restock-count').textContent = '0 produk';
        document.getElementById('ai-restock-cost').textContent = 'Rp 0';
        document.getElementById('ai-restock-list').innerHTML = `
            <div class="py-6 text-center text-sm text-red-500">
                Rekomendasi belum dapat dimuat.
            </div>
        `;

        document.getElementById('ai-tip-source').textContent =
            'Analisis tidak tersedia';
        document.getElementById('ai-optimization-tip').textContent =
            'Muat ulang setelah endpoint prediksi kembali tersedia.';
    }

    function formatInteger(value) {
        const number = Number(value || 0);
        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(number);
    }

    function formatRupiah(value) {
        const number = Number(value || 0);
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(number);
    }

    function formatPercentage(value) {
        if (value === null || value === undefined || value === '') {
            return 'Belum ada';
        }

        const number = Number(value);
        const sign = number > 0 ? '+' : '';
        return `${sign}${new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 1
        }).format(number)}%`;
    }

    function escapeHtml(value) {
        const temporaryElement = document.createElement('div');
        temporaryElement.textContent = String(value ?? '');
        return temporaryElement.innerHTML;
    }
</script>

@endsection
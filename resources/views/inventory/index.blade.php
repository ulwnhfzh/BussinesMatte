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
                <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter
                </button>
                <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Ekspor
                </button>
            </div>
        </div>

        <!-- Filter Kategori (Tombol Putih dengan border) -->
        <div class="flex flex-wrap gap-2 mb-6">
            <button class="px-4 py-1.5 rounded-full bg-blue-600 text-white text-xs font-medium">Semua Item</button>
            <button class="px-4 py-1.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition">Elektronik</button>
            <button class="px-4 py-1.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition">Rumah Tangga</button>
            <button class="px-4 py-1.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition">Aksesori</button>
            <button class="px-4 py-1.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition">Wearable</button>
        </div>

        <!-- Tabel Produk -->
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk <span class="ml-1 text-[10px]">•</span></th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Ambang Batas Minimum</th>
                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($products as $product)
                    <!-- BUNGKUS DENGAN TAG <a> AGAR BISA DIKLIK -->
                    <a href="{{ route('inventory.detail', $product['id']) }}" class="block hover:bg-blue-50 transition duration-150">
                        <tr class="cursor-pointer">
                            <!-- Nama Produk -->
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden border border-gray-200 bg-gray-100">

    @if($product->image)
        <img
            src="{{ asset('storage/products/' . $product->image) }}"
            alt="{{ $product->name }}"
            class="w-full h-full object-cover">
    @else
        <div class="w-full h-full flex items-center justify-center text-lg">
            📦
        </div>
    @endif

</div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-400">SKU: {{ $product->product_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- Kapasitas / Stok Saat Ini -->
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <div class="text-xs text-gray-900 font-medium mb-1">
                                        Kapasitas <span class="text-blue-600">{{ $product->stock }}</span> / {{ max($product->stock,1) }}
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-24 h-1.5 bg-gray-200 rounded-full">
                                        <div class="h-1.5 rounded-full {{ $product->status == 'kritis' ? 'bg-red-500' : 'bg-blue-600' }}" 
                                             style="width: {{ ($product->stock / max($product->stock,1)) * 100 }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <!-- Ambang Batas -->
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-medium">
                                {{ $product->minimum_stock }} Unit
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
                        </tr>
                    </a>
                    @endforeach
                </tbody>
            </table>
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
                <!-- Item 1 -->
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
                <!-- Item 2 -->
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

            <!-- Estimasi Total & Tombol Aksi -->
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-500 font-medium">Estimasi Total Biaya</span>
                <span class="text-lg font-bold text-gray-800">$14,650</span>
            </div>
            
            <!-- TOMBOL "BUAT PESANAN PEMBELIAN" (BISA DIKLIK) -->
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

<div id="productModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
<div class="bg-white rounded-2xl w-full max-w-2xl p-6">
<h2 class="text-xl font-bold mb-6">Tambah Barang</h2>
<form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="grid grid-cols-2 gap-4">
<input type="text" name="product_code" placeholder="Kode Barang" class="border rounded-lg p-2">
<input type="text" name="name" placeholder="Nama Barang" class="border rounded-lg p-2" required>
<input type="text" name="category" placeholder="Kategori" class="border rounded-lg p-2">
<input type="text" name="unit" value="Pcs" class="border rounded-lg p-2">
<input type="number" name="purchase_price" placeholder="Harga Beli" class="border rounded-lg p-2">
<input type="number" name="selling_price" placeholder="Harga Jual" class="border rounded-lg p-2">
<input type="number" name="stock" placeholder="Stok" class="border rounded-lg p-2">
<input type="number" name="minimum_stock" value="10" class="border rounded-lg p-2">
<div class="col-span-2"><input type="file" name="image" class="border rounded-lg p-2 w-full"></div>
<div class="col-span-2"><textarea name="description" class="border rounded-lg p-2 w-full" placeholder="Deskripsi"></textarea></div>
</div>
<div class="flex justify-end gap-2 mt-6">
<button type="button" onclick="closeModal()" class="border px-4 py-2 rounded-lg">Batal</button>
<button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Simpan Barang</button>
</div></form></div></div>
<script>
function openModal(){const m=document.getElementById('productModal');m.classList.remove('hidden');m.classList.add('flex');}
function closeModal(){const m=document.getElementById('productModal');m.classList.remove('flex');m.classList.add('hidden');}
</script>

@endsection
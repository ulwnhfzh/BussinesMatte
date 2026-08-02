@extends('layouts.app')

@section('title', 'Detail Produk - UsahaMate')

@section('content')
@php
    // Mencegah pembagian dengan nol apabila maximum_stock bernilai 0
    $maximumStock = max((int) $product->maximum_stock, 1);

    // Progress bar dibatasi antara 0% sampai 100%
    $stockPercentage = min(
        100,
        max(0, ((int) $product->stock / $maximumStock) * 100)
    );

    // Perhitungan keuntungan per unit
    $profitPerUnit = $product->selling_price - $product->purchase_price;

    // Margin dihitung berdasarkan harga jual
    $marginPercentage = $product->selling_price > 0
        ? ($profitPerUnit / $product->selling_price) * 100
        : 0;
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ route('inventory') }}"
                class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 mb-2"
            >
                <span>←</span>
                <span>Kembali ke Inventory</span>
            </a>

            <h1 class="text-2xl font-bold text-gray-800">
                Detail Produk
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Informasi produk dan kondisi stok saat ini.
            </p>
        </div>

        <!-- Status Produk -->
        <div>
            @if($product->status === 'optimal')
                <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full
                             bg-emerald-100 text-emerald-700 border border-emerald-200">
                    Stok Optimal
                </span>
            @elseif($product->status === 'kritis')
                <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full
                             bg-red-100 text-red-700 border border-red-200">
                    Stok Kritis
                </span>
            @else
                <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full
                             bg-blue-100 text-blue-700 border border-blue-200">
                    Stok Melebihi Maksimum
                </span>
            @endif
        </div>
    </div>

    <!-- Informasi Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Foto Produk -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 border border-gray-200">

                @if($product->image)
                    <img
                        src="{{ asset('storage/products/' . $product->image) }}"
                        alt="{{ $product->name }}"
                        class="w-full h-full object-cover"
                    >
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                        <span class="text-6xl mb-3">📦</span>
                        <span class="text-sm">Belum ada gambar</span>
                    </div>
                @endif

            </div>
        </div>

        <!-- Identitas Produk -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

            <div class="border-b border-gray-100 pb-5 mb-5">
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-2">
                    {{ $product->category ?: 'Tanpa Kategori' }}
                </p>

                <h2 class="text-3xl font-bold text-gray-800">
                    {{ $product->name }}
                </h2>

                <p class="text-sm text-gray-500 mt-2">
                    Kode barang: {{ $product->product_code }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">
                        Satuan
                    </p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">
                        {{ $product->unit }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">
                        Terakhir diperbarui
                    </p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">
                        {{ $product->updated_at
                            ? $product->updated_at->format('d M Y, H:i')
                            : '-' }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">
                        Deskripsi
                    </p>
                    <p class="text-sm text-gray-700 mt-1 leading-relaxed">
                        {{ $product->description ?: 'Belum ada deskripsi produk.' }}
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- Informasi Stok -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    Kondisi Stok
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Perbandingan stok saat ini dengan batas minimum dan maksimum.
                </p>
            </div>

            <span class="text-2xl font-bold text-blue-600">
                {{ $product->stock }} {{ $product->unit }}
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden mb-5">
            <div
                class="h-full rounded-full
                    {{ $product->status === 'kritis'
                        ? 'bg-red-500'
                        : ($product->status === 'peringatan'
                            ? 'bg-blue-500'
                            : 'bg-emerald-500') }}"
                style="width: {{ $stockPercentage }}%"
            ></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase tracking-wide">
                    Stok Saat Ini
                </p>
                <p class="text-xl font-bold text-gray-800 mt-1">
                    {{ $product->stock }}
                </p>
            </div>

            <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                <p class="text-xs text-red-500 uppercase tracking-wide">
                    Stok Minimum
                </p>
                <p class="text-xl font-bold text-red-700 mt-1">
                    {{ $product->minimum_stock }}
                </p>
            </div>

            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <p class="text-xs text-blue-500 uppercase tracking-wide">
                    Stok Maksimum
                </p>
                <p class="text-xl font-bold text-blue-700 mt-1">
                    {{ $product->maximum_stock }}
                </p>
            </div>

        </div>
    </div>

    <!-- Informasi Harga -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500">
                Harga Beli
            </p>
            <p class="text-xl font-bold text-gray-800 mt-2">
                Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500">
                Harga Jual
            </p>
            <p class="text-xl font-bold text-blue-600 mt-2">
                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500">
                Keuntungan per Unit
            </p>
            <p class="text-xl font-bold
                {{ $profitPerUnit >= 0 ? 'text-emerald-600' : 'text-red-600' }}
                mt-2">
                Rp {{ number_format($profitPerUnit, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Margin {{ number_format($marginPercentage, 1, ',', '.') }}%
            </p>
        </div>

    </div>
</div>
@endsection
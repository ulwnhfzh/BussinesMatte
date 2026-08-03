@extends('layouts.app')

@section('title', 'Riwayat Stok - UsahaMate')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Notifikasi validasi filter --}}
    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200 bg-red-50 p-4"
            role="alert"
        >
            <div class="flex items-start gap-3">
                <span class="text-red-600">⚠️</span>

                <div>
                    <h3 class="text-sm font-bold text-red-800">
                        Filter tidak dapat diterapkan
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

    {{-- Header halaman --}}
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
                Riwayat Pergerakan Stok
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Pantau setiap perubahan stok produk bisnis Anda.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 border border-blue-100">
            <span class="text-blue-600">🧾</span>
            <div>
                <p class="text-[10px] uppercase tracking-wide text-blue-500">
                    Total aktivitas
                </p>
                <p class="text-sm font-bold text-blue-700">
                    {{ number_format($totalMovements, 0, ',', '.') }} catatan
                </p>
            </div>
        </div>
    </div>

    {{-- Ringkasan aktivitas --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500">Total Aktivitas</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">
                        {{ number_format($totalMovements, 0, ',', '.') }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-xl">
                    📋
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500">Total Stok Masuk</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-2">
                        +{{ number_format($totalIncoming, 0, ',', '.') }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center text-xl">
                    ↗
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-gray-500">Total Stok Keluar</p>
                    <p class="text-2xl font-bold text-red-600 mt-2">
                        -{{ number_format($totalOutgoing, 0, ',', '.') }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center text-xl">
                    ↘
                </div>
            </div>
        </div>
    </div>

    {{-- Filter riwayat --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-800">
                    Filter Riwayat
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Cari aktivitas berdasarkan produk, jenis, dan tanggal.
                </p>
            </div>

            @if(request()->hasAny([
                'search',
                'product_id',
                'type',
                'direction',
                'date_from',
                'date_to'
            ]))
                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                    Filter aktif
                </span>
            @endif
        </div>

        <form
            action="{{ route('inventory.stock-movements') }}"
            method="GET"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3"
        >
            <div class="md:col-span-2 xl:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    Pencarian
                </label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nama, kode produk, atau catatan..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    Produk
                </label>
                <select
                    name="product_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
                    <option value="">Semua produk</option>

                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            {{ (string) request('product_id') === (string) $product->id ? 'selected' : '' }}
                        >
                            {{ $product->product_code }} — {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    Jenis aktivitas
                </label>
                <select
                    name="type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
                    <option value="">Semua jenis</option>

                    @foreach ($typeOptions as $value => $label)
                        <option
                            value="{{ $value }}"
                            {{ request('type') === $value ? 'selected' : '' }}
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    Arah stok
                </label>
                <select
                    name="direction"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
                    <option value="">Semua arah</option>
                    <option value="incoming" {{ request('direction') === 'incoming' ? 'selected' : '' }}>
                        Stok masuk
                    </option>
                    <option value="outgoing" {{ request('direction') === 'outgoing' ? 'selected' : '' }}>
                        Stok keluar
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    Dari tanggal
                </label>
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    Sampai tanggal
                </label>
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
            </div>

            <div class="md:col-span-2 xl:col-span-5 flex flex-wrap items-end gap-2">
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                >
                    Terapkan Filter
                </button>

                @if(request()->hasAny([
                    'search',
                    'product_id',
                    'type',
                    'direction',
                    'date_from',
                    'date_to'
                ]))
                    <a
                        href="{{ route('inventory.stock-movements') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel riwayat --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-gray-800">
                    Daftar Aktivitas Stok
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Menampilkan {{ $movements->firstItem() ?? 0 }}–{{ $movements->lastItem() ?? 0 }}
                    dari {{ $movements->total() }} aktivitas.
                </p>
            </div>

            <span class="text-xs text-gray-400">
                Aktivitas terbaru ditampilkan lebih dahulu
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Waktu
                        </th>
                        <th class="px-5 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Produk
                        </th>
                        <th class="px-5 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aktivitas
                        </th>
                        <th class="px-5 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                            Perubahan
                        </th>
                        <th class="px-5 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                            Kondisi Stok
                        </th>
                        <th class="px-5 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pelaku & Referensi
                        </th>
                        <th class="px-5 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Catatan
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($movements as $movement)
                        @php
                            $badgeClass = match ($movement->type) {
                                \App\Models\StockMovement::TYPE_INITIAL =>
                                    'bg-blue-100 text-blue-700 border-blue-200',

                                \App\Models\StockMovement::TYPE_SALE =>
                                    'bg-red-100 text-red-700 border-red-200',

                                \App\Models\StockMovement::TYPE_PURCHASE =>
                                    'bg-emerald-100 text-emerald-700 border-emerald-200',

                                \App\Models\StockMovement::TYPE_ADJUSTMENT =>
                                    'bg-amber-100 text-amber-700 border-amber-200',

                                \App\Models\StockMovement::TYPE_RETURN =>
                                    'bg-purple-100 text-purple-700 border-purple-200',

                                default =>
                                    'bg-gray-100 text-gray-700 border-gray-200',
                            };

                            $referenceLabel = null;

                            if ($movement->reference) {
                                $referenceLabel = $movement->reference->invoice_number
                                    ?? ('#' . $movement->reference_id);
                            } elseif ($movement->reference_id) {
                                $referenceLabel = '#' . $movement->reference_id;
                            }
                        @endphp

                        <tr class="hover:bg-blue-50/50 transition">
                            <td class="px-5 py-5 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-700">
                                    {{ $movement->created_at->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $movement->created_at->format('H:i') }} WIB
                                </p>
                            </td>

                            <td class="px-5 py-5 min-w-[190px]">
                                @if ($movement->product)
                                    <a
                                        href="{{ route('inventory.detail', $movement->product_id) }}"
                                        class="text-sm font-semibold text-gray-800 hover:text-blue-600"
                                    >
                                        {{ $movement->product_name }}
                                    </a>
                                @else
                                    <p class="text-sm font-semibold text-gray-700">
                                        {{ $movement->product_name }}
                                    </p>
                                @endif

                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $movement->product_code ?: 'Tanpa kode' }}
                                </p>
                            </td>

                            <td class="px-5 py-5 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-[11px] font-bold rounded-full border {{ $badgeClass }}">
                                    {{ $movement->type_label }}
                                </span>
                            </td>

                            <td class="px-5 py-5 whitespace-nowrap text-center">
                                <span class="text-base font-bold {{ $movement->quantity > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="px-5 py-5 whitespace-nowrap text-center">
                                <div class="inline-flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 font-semibold">
                                        {{ number_format($movement->stock_before, 0, ',', '.') }}
                                    </span>
                                    <span class="text-gray-400">→</span>
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold">
                                        {{ number_format($movement->stock_after, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-5 py-5 min-w-[170px]">
                                <p class="text-sm font-medium text-gray-700">
                                    {{ $movement->user?->name ?? 'Sistem' }}
                                </p>

                                @if ($referenceLabel)
                                    <p class="text-xs text-blue-600 font-medium mt-1">
                                        {{ $referenceLabel }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 mt-1">
                                        Tanpa referensi
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-5 min-w-[220px]">
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    {{ $movement->note ?: 'Tidak ada catatan.' }}
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-2xl mb-3">
                                    📭
                                </div>
                                <p class="text-base font-semibold text-gray-700">
                                    Belum ada aktivitas stok
                                </p>
                                <p class="text-sm text-gray-400 mt-1">
                                    Aktivitas akan muncul setelah produk dibuat, diedit, atau dijual.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
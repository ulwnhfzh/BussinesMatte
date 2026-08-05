@extends('layouts.app')

@section('title', 'Dashboard - UsahaMate')

@section('content')
<div class="w-full min-w-0 space-y-5 sm:space-y-6">
    <!-- Header Overview -->
    <div class="flex min-w-0 flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-bold text-gray-800">Dashboard Overview</h2>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ now()->locale('id')->translatedFormat('l, d F Y') }} · Ringkasan performa bisnis Anda.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Data Bisnis Aktif
            </span>
            <span class="text-xs text-gray-400">
                Diperbarui {{ now()->format('H:i') }} WIB
            </span>
        </div>
    </div>

    <!-- 4 Kartu Statistik -->
    <div class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-5">
        <!-- Kartu 1 -->
        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <span class="flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-700">REAL-TIME</span>
            </div>
            <p class="mb-1 text-xs font-medium text-gray-500">PENDAPATAN HARI INI</p>
            <h3 class="truncate text-xl font-bold text-gray-800" title="Rp {{ number_format($todayRevenue, 0, ',', '.') }}">
                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
            </h3>
        </div>
        <!-- Kartu 2 -->
        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
                <span class="flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700">HARI INI</span>
            </div>
            <p class="mb-1 text-xs font-medium text-gray-500">TRANSAKSI HARI INI</p>
            <h3 class="text-xl font-bold text-gray-800">
                {{ number_format($todayTransactionCount, 0, ',', '.') }} Transaksi
            </h3>
        </div>
        <!-- Kartu 3 -->
        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-red-50 p-2 rounded-lg text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></div>
                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center">KRITIS</span>
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1">PERINGATAN STOK</p>
            <h3 class="text-xl font-bold text-gray-800">
                {{ number_format($criticalStockCount, 0, ',', '.') }} SKU
            </h3>
        </div>
        <!-- Kartu 4 -->
        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-yellow-50 p-2 rounded-lg text-yellow-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
                <span class="flex items-center rounded-full border border-gray-200 bg-white px-2 py-0.5 text-[10px] font-bold text-gray-500">AKTUAL</span>
            </div>
            <p class="mb-1 text-xs font-medium text-gray-500">LABA HARI INI</p>
            <h3 class="truncate text-xl font-bold text-gray-800" title="Rp {{ number_format($todayProfit, 0, ',', '.') }}">
                Rp {{ number_format($todayProfit, 0, ',', '.') }}
            </h3>
        </div>
    </div>

    <!-- Bagian Grid Chart + AI -->
    <div class="grid min-w-0 grid-cols-1 gap-5 xl:grid-cols-3">
        <!-- Kolom Chart -->
        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6 xl:col-span-2">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800">Pendapatan Mingguan</h3>
                    <p class="text-xs text-gray-400">Pendapatan tujuh hari terakhir berdasarkan transaksi POS</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                    7 Hari Terakhir
                </span>
            </div>
            <div class="h-56 w-full relative">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Kolom AI -->
        <div class="relative min-w-0 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-6">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-2">
                    <div class="bg-blue-50 p-1.5 rounded-lg text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg></div>
                    <span class="truncate text-sm font-bold">AI INVENTORY</span>
                </div>

                @if($aiServiceStatus === 'online')
                    <span class="flex flex-shrink-0 items-center gap-1 text-[10px] font-bold text-emerald-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        AI Online
                    </span>
                @else
                    <span class="flex flex-shrink-0 items-center gap-1 text-[10px] font-bold text-red-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                        AI Offline
                    </span>
                @endif
            </div>

            <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white px-2 py-1 text-[10px] font-semibold text-blue-700 shadow-sm">
                        {{ $aiModeLabel }}
                    </span>
                    <span class="text-[10px] text-gray-500" title="{{ $aiServiceMessage }}">
                        Pemilihan metode otomatis
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($aiRecommendations as $recommendation)
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-gray-800" title="{{ $recommendation['product_name'] }}">
                                    {{ $recommendation['product_name'] }}
                                </p>
                                <p class="mt-1 text-[10px] leading-relaxed text-gray-500">
                                    {{ $recommendation['reason'] ?? 'Rekomendasi restok berdasarkan prediksi permintaan.' }}
                                </p>
                            </div>

                            <span class="flex-shrink-0 rounded-full bg-white px-2 py-1 text-[10px] font-bold text-blue-700 shadow-sm">
                                +{{ number_format($recommendation['recommended_restock'], 0, ',', '.') }}
                                {{ $recommendation['unit'] ?? 'unit' }}
                            </span>
                        </div>

                        <div class="mt-2 flex items-center justify-between gap-2">
                            <span class="truncate text-[9px] text-gray-400" title="{{ $recommendation['method_reason'] ?? '' }}">
                                {{ $recommendation['method_label'] ?? $aiModeLabel }}
                            </span>
                            <a
                                href="{{ route('inventory.detail', $recommendation['product_id']) }}"
                                class="flex-shrink-0 text-[10px] font-semibold text-blue-600 hover:underline"
                            >
                                Lihat Produk →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-center">
                        <div class="mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-full {{ $aiServiceStatus === 'online' ? 'bg-emerald-100' : 'bg-red-100' }}">
                            {{ $aiServiceStatus === 'online' ? '✓' : '!' }}
                        </div>
                        <p class="text-xs font-semibold text-gray-700">
                            {{ $aiServiceStatus === 'online' ? 'Belum ada produk yang perlu direstok' : 'Prediksi sedang tidak tersedia' }}
                        </p>
                        <p class="mt-1 text-[10px] leading-relaxed text-gray-500">
                            {{ $aiServiceStatus === 'online' ? $aiSummary : $aiServiceMessage }}
                        </p>
                    </div>
                @endforelse
            </div>

            <a
                href="{{ route('ai.copilot') }}"
                class="mt-5 block w-full rounded-xl bg-blue-50 py-2.5 text-center text-sm font-medium text-blue-600 transition hover:bg-blue-100"
            >
                Buka AI Copilot
            </a>
        </div>
    </div>

    <!-- Tabel Produk & Aktivitas -->
    <div class="grid min-w-0 grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6 xl:col-span-2">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800">Performa Produk Unggulan</h3>
                    <p class="text-xs text-gray-400">Metrik penjualan produk selama 30 hari terakhir</p>
                </div>
                <a href="{{ route('inventory') }}" class="text-xs font-medium text-blue-600 hover:underline">Lihat Inventory</a>
            </div>
            <div class="w-full min-w-0 overflow-x-auto">
                <table class="w-full min-w-[680px] table-fixed text-left text-sm">
                    <colgroup>
                        <col style="width: 30%">
                        <col style="width: 24%">
                        <col style="width: 16%">
                        <col style="width: 14%">
                        <col style="width: 16%">
                    </colgroup>
                    <thead class="text-[10px] text-gray-400 uppercase border-b">
                        <tr>
                            <th class="pb-3 pr-4 font-medium whitespace-nowrap">PRODUK</th>
                            <th class="pb-3 px-4 font-medium whitespace-nowrap">STATUS STOK</th>
                            <th class="pb-3 px-4 text-center font-medium whitespace-nowrap">JUMLAH TERJUAL</th>
                            <th class="pb-3 px-4 text-center font-medium whitespace-nowrap">MARGIN LABA</th>
                            <th class="pb-3 pl-4 text-right font-medium whitespace-nowrap">PENDAPATAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topProducts as $product)
                            @php
                                if ($product->stock < $product->minimum_stock) {
                                    $stockLabel = 'Kritis';
                                    $stockBadgeClass = 'bg-red-100 text-red-700';
                                    $stockTextClass = 'text-red-600';
                                } elseif ($product->stock > $product->maximum_stock) {
                                    $stockLabel = 'Berlebih';
                                    $stockBadgeClass = 'bg-amber-100 text-amber-700';
                                    $stockTextClass = 'text-amber-600';
                                } else {
                                    $stockLabel = 'Optimal';
                                    $stockBadgeClass = 'bg-emerald-100 text-emerald-700';
                                    $stockTextClass = 'text-emerald-600';
                                }
                            @endphp

                            <tr class="transition hover:bg-blue-50/50">
                                <td class="py-4 pr-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                            @if($product->image)
                                                <img
                                                    src="{{ asset('storage/products/' . $product->image) }}"
                                                    alt="{{ $product->name }}"
                                                    class="h-full w-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-base">📦</div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <a
                                                href="{{ route('inventory.detail', $product->product_id) }}"
                                                class="block truncate text-sm font-bold text-gray-800 hover:text-blue-600"
                                            >
                                                {{ $product->name }}
                                            </a>
                                            <p class="truncate text-[10px] text-gray-400">
                                                SKU: {{ $product->product_code }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $stockBadgeClass }}">
                                        {{ $product->stock }} / {{ max((int) $product->maximum_stock, 1) }} {{ $product->unit }}
                                    </span>
                                    <span class="ml-1 text-[10px] font-bold {{ $stockTextClass }}">
                                        {{ $stockLabel }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-center font-semibold text-gray-700">
                                    {{ number_format($product->sold_quantity, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 text-center font-semibold text-gray-700">
                                    {{ number_format($product->margin_percentage, 1, ',', '.') }}%
                                </td>

                                <td class="py-4 pl-4 text-right font-bold text-gray-800">
                                    Rp {{ number_format($product->total_revenue, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <span class="mb-2 text-3xl">📊</span>
                                        <p class="text-sm font-medium text-gray-600">Belum ada data penjualan</p>
                                        <p class="mt-1 text-xs">Produk akan muncul setelah transaksi POS tersimpan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-6">
            <div class="mb-6 flex items-center justify-between gap-3">
                <h3 class="font-bold text-gray-800">AKTIVITAS TERKINI</h3>
                <a href="{{ route('inventory.stock-movements') }}" class="flex-shrink-0 text-xs font-medium text-blue-600 hover:underline">Riwayat Stok</a>
            </div>
            <div class="space-y-5">
                @forelse($recentActivities as $activity)
                    <div class="flex min-w-0 gap-3">
                        @if($activity['category'] === 'transaction')
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        @else
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9M20 20v-5h-.581m0 0a8.003 8.003 0 01-15.357-2"></path>
                                </svg>
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="truncate text-xs font-bold text-gray-800" title="{{ $activity['title'] }}">
                                    {{ $activity['title'] }}
                                </p>
                                <span class="flex-shrink-0 text-[9px] text-gray-400">
                                    {{ $activity['created_at']->locale('id')->diffForHumans() }}
                                </span>
                            </div>

                            <p class="mt-0.5 truncate text-[10px] text-gray-400" title="{{ $activity['description'] }}">
                                {{ $activity['description'] }}
                            </p>

                            @if($activity['amount'] !== null)
                                <p class="mt-1 text-xs font-semibold text-gray-700">
                                    Rp {{ number_format($activity['amount'], 0, ',', '.') }}
                                </p>
                            @elseif($activity['quantity'] !== null)
                                <p class="mt-1 text-xs font-semibold {{ $activity['quantity'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $activity['quantity'] > 0 ? '+' : '' }}{{ number_format($activity['quantity'], 0, ',', '.') }} unit
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center">
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-lg">🕘</div>
                        <p class="text-sm font-medium text-gray-600">Belum ada aktivitas</p>
                        <p class="mt-1 text-xs text-gray-400">Transaksi dan perubahan stok akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

    <!-- SCRIPT CHART.JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartElement = document.getElementById('salesChart');

            if (!chartElement || typeof Chart === 'undefined') {
                return;
            }

            const chartLabels = @json($chartLabels);
            const chartRevenue = @json($chartRevenue);
            const rupiahFormatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            });

            const compactRupiahFormatter = new Intl.NumberFormat('id-ID', {
                notation: 'compact',
                maximumFractionDigits: 1,
            });

            const ctx = chartElement.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: chartRevenue,
                        backgroundColor: chartRevenue.map((value, index) =>
                            index === chartRevenue.length - 1 ? '#2563eb' : '#dbeafe'
                        ),
                        hoverBackgroundColor: '#2563eb',
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 34,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return 'Pendapatan: ' + rupiahFormatter.format(context.raw);
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9',
                            },
                            border: {
                                display: false,
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    size: 10,
                                },
                                callback: function (value) {
                                    return 'Rp ' + compactRupiahFormatter.format(value);
                                },
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                            border: {
                                display: false,
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    weight: 'bold',
                                },
                                color: '#9ca3af',
                            },
                        },
                    }
                }
            });
        });
    </script>
@endsection
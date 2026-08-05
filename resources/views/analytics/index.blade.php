@extends('layouts.app')

@section('title', 'Analytics - UsahaMate')

@section('content')
<style>
    /* ============================================= */
    /* GAYA UMUM */
    /* ============================================= */
    .analytics-card {
        transition: all 0.3s ease;
        background: white;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        padding: 20px;
        height: 100%;
    }
    .analytics-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }
    .stat-number {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.5px;
    }
    .stat-label {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-selesai {
        background: #dcfce7;
        color: #15803d;
    }
    .status-pending {
        background: #fef3c7;
        color: #b45309;
    }
    .status-refund {
        background: #fee2e2;
        color: #dc2626;
    }
    .table-row:hover {
        background: #f8fafc;
    }
    .filter-select {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 8px 14px;
        font-size: 13px;
        outline: none;
        background: white;
        transition: all 0.2s;
    }
    .filter-select:focus {
        border-color: #2563eb;
        ring: 2px solid #bfdbfe;
    }
    .forecast-bar {
        height: 8px;
        border-radius: 8px;
        background: #e2e8f0;
        overflow: hidden;
        position: relative;
    }
    .forecast-bar-fill {
        height: 100%;
        border-radius: 8px;
        transition: width 1s ease;
    }
    .confidence-high { background: #2563eb; }
    .confidence-medium { background: #8b5cf6; }
    .confidence-low { background: #f59e0b; }

    /* ============================================= */
    /* CARD STATISTIK KE SAMPING (HORIZONTAL) */
    /* ============================================= */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    @media (max-width: 1024px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
    }

    /* Stat Card - Horizontal dengan icon di kiri */
    .stat-card-horizontal {
        background: white;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        padding: 16px 20px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .stat-card-horizontal:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }
    .stat-card-horizontal .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .stat-card-horizontal .stat-icon-blue { background: #eff6ff; color: #2563eb; }
    .stat-card-horizontal .stat-icon-purple { background: #f5f3ff; color: #8b5cf6; }
    .stat-card-horizontal .stat-icon-green { background: #ecfdf5; color: #22c55e; }
    .stat-card-horizontal .stat-icon-amber { background: #fffbeb; color: #f59e0b; }

    .stat-card-horizontal .stat-info {
        flex: 1;
        min-width: 0;
    }
    .stat-card-horizontal .stat-info .stat-label {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-card-horizontal .stat-info .stat-number {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    .stat-card-horizontal .stat-info .stat-sub {
        font-size: 11px;
        color: #94a3b8;
    }
    .stat-card-horizontal .stat-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 20px;
        display: inline-block;
        white-space: nowrap;
    }
    .stat-badge-green {
        background: #dcfce7;
        color: #15803d;
    }
    .stat-badge-red {
        background: #fee2e2;
        color: #dc2626;
    }
    .stat-badge-amber {
        background: #fef3c7;
        color: #b45309;
    }
    .stat-card-horizontal .stat-right {
        text-align: right;
        flex-shrink: 0;
    }

    /* ============================================= */
    /* LAYOUT 2 KOLOM UTAMA */
    /* ============================================= */
    .two-col-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .two-col-layout {
            grid-template-columns: 1fr;
        }
    }

    /* ============================================= */
    /* LAYOUT 3 KOLOM */
    /* ============================================= */
    .three-col-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .three-col-layout {
            grid-template-columns: 1fr;
        }
    }

    /* ============================================= */
    /* PRODUK MARGIN LIST */
    /* ============================================= */
    .product-margin-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .product-margin-item:last-child {
        border-bottom: none;
    }
    .product-margin-item .rank {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
        margin-right: 12px;
    }
    .product-margin-item .rank-1 { background: #2563eb; }
    .product-margin-item .rank-2 { background: #8b5cf6; }
    .product-margin-item .rank-3 { background: #f59e0b; }
    .product-margin-item .rank-4 { background: #94a3b8; }
    .product-margin-item .rank-5 { background: #94a3b8; }

    /* ============================================= */
    /* FORECAST CARDS */
    /* ============================================= */
    .forecast-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }

    @media (max-width: 640px) {
        .forecast-grid {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        }
    }

    .forecast-item {
        text-align: center;
        padding: 12px;
        border-radius: 12px;
        background: #f8fafc;
    }
    .forecast-item .day {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
    }
    .forecast-item .value {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }
</style>

<div class="w-full min-w-0 space-y-6">
    <!-- ============================================= -->
    <!-- HEADER -->
    <!-- ============================================= -->
    <div class="flex min-w-0 flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-bold text-gray-800">Analytics</h2>
            <p class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                Analisis transaksi dan profit bisnis berdasarkan data POS
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                Periode {{ $periodDays }} Hari
            </span>
            <span class="text-xs text-gray-400">
                {{ $periodStart->format('d M Y') }} – {{ $periodEnd->format('d M Y') }}
            </span>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- FILTER -->
    <!-- ============================================= -->
    <form action="{{ route('analytics') }}" method="GET" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="w-full sm:max-w-xs">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rentang Tanggal</label>
                <select name="date_range" class="filter-select w-full mt-1">
                    <option value="7_hari" {{ $dateRange === '7_hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30_hari" {{ $dateRange === '30_hari' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="90_hari" {{ $dateRange === '90_hari' ? 'selected' : '' }}>90 Hari Terakhir</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>Terapkan Filter
                </button>

                <a href="{{ route('analytics') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                    Bersihkan
                </a>
            </div>
        </div>
    </form>

    <!-- ============================================= -->
    <!-- STATISTIK CARD - HORIZONTAL (KE SAMPING) -->
    <!-- ============================================= -->
    <div class="stats-row">
        <!-- Card 1: Total Pendapatan -->
        <div class="stat-card-horizontal">
            <div class="stat-icon stat-icon-blue">
                <span class="text-sm font-extrabold tracking-tight">Rp</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-number truncate" title="Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
                <div class="flex items-center gap-2 mt-1">
                    @if($stats['revenue_growth'] !== null)
                        <span class="stat-badge {{ $stats['revenue_growth'] >= 0 ? 'stat-badge-green' : 'stat-badge-red' }}">
                            {{ $stats['revenue_growth'] > 0 ? '+' : '' }}{{ number_format($stats['revenue_growth'], 1, ',', '.') }}%
                        </span>
                        <span class="stat-sub">dari periode sebelumnya</span>
                    @else
                        <span class="stat-badge stat-badge-amber">DATA BARU</span>
                        <span class="stat-sub">belum ada pembanding</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 2: Total Transaksi -->
        <div class="stat-card-horizontal">
            <div class="stat-icon stat-icon-purple">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-number">{{ number_format($stats['total_orders']) }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="stat-badge stat-badge-green">AKTUAL</span>
                    <span class="stat-sub">{{ $periodDays }} hari</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Rata-rata Transaksi -->
        <div class="stat-card-horizontal">
            <div class="stat-icon stat-icon-green">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Rata-rata Transaksi</div>
                <div class="stat-number truncate" title="Rp {{ number_format($stats['avg_order_value'], 0, ',', '.') }}">Rp {{ number_format($stats['avg_order_value'], 0, ',', '.') }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="stat-badge stat-badge-green">RATA-RATA</span>
                    <span class="stat-sub">per transaksi</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Laba -->
        <div class="stat-card-horizontal">
            <div class="stat-icon stat-icon-amber">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Laba</div>
                <div class="stat-number truncate" title="Rp {{ number_format($stats['total_profit'], 0, ',', '.') }}">Rp {{ number_format($stats['total_profit'], 0, ',', '.') }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="stat-badge stat-badge-amber">AKTUAL</span>
                    <span class="stat-sub">setelah harga pokok</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- GRAFIK TREN PENDAPATAN DAN LABA -->
    <!-- ============================================= -->
    <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Tren Pendapatan dan Laba</h3>
                <p class="text-xs text-gray-400">
                    Pergerakan harian berdasarkan transaksi POS pada periode aktif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                <span class="inline-flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                    Pendapatan
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    Laba
                </span>
            </div>
        </div>

        <div class="relative h-64 w-full sm:h-72">
            <canvas id="analyticsTrendChart"></canvas>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- LAYOUT 2 KOLOM: Distribusi + Produk -->
    <!-- ============================================= -->
    <div class="two-col-layout">
        <!-- Kiri: Distribusi Pendapatan (Chart) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800">Distribusi Pendapatan</h3>
                    <p class="text-xs text-gray-400">Berdasarkan kategori produk pada periode aktif</p>
                </div>
            </div>

            @if(count($revenueDistribution) > 0)
                <div class="flex min-w-0 flex-col items-center gap-6 sm:flex-row sm:gap-8">
                    <!-- Pie Chart -->
                    <div class="h-48 w-48 flex-shrink-0">
                        <canvas id="revenueChart"></canvas>
                    </div>

                    <!-- Legend -->
                    <div class="w-full min-w-0 flex-1 space-y-3">
                        @foreach($revenueDistribution as $item)
                            <div class="flex min-w-0 items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-3 w-3 flex-shrink-0 rounded-full" style="background: {{ $item['color'] }}"></span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm text-gray-700" title="{{ $item['label'] }}">
                                            {{ $item['label'] }}
                                        </p>
                                        <p class="text-[10px] text-gray-400">
                                            Rp {{ number_format($item['revenue'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <span class="flex-shrink-0 font-bold text-gray-800">
                                    {{ number_format($item['value'], 1, ',', '.') }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($topRevenueCategory)
                    <div class="mt-5 flex min-w-0 flex-col gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-500">
                                Kontributor Pendapatan Terbesar
                            </p>
                            <p class="mt-1 truncate text-sm font-bold text-gray-800" title="{{ $topRevenueCategory['label'] }}">
                                {{ $topRevenueCategory['label'] }}
                            </p>
                        </div>

                        <div class="flex-shrink-0 sm:text-right">
                            <p class="text-lg font-extrabold text-blue-700">
                                {{ number_format($topRevenueCategory['value'], 1, ',', '.') }}%
                            </p>
                            <p class="text-xs text-blue-600">
                                Rp {{ number_format($topRevenueCategory['revenue'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endif
            @else
                <div class="py-10 text-center">
                    <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-xl">📊</div>
                    <p class="text-sm font-medium text-gray-600">Belum ada distribusi pendapatan</p>
                    <p class="mt-1 text-xs text-gray-400">Kategori akan muncul setelah transaksi tersimpan.</p>
                </div>
            @endif
        </div>

        <!-- Kanan: Profitabilitas Produk -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800">Profitabilitas Produk</h3>
                    <p class="text-xs text-gray-400">Produk dengan total laba terbesar pada periode aktif</p>
                </div>
                <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded-full font-medium">Top 5</span>
            </div>
            <div class="space-y-1">
                @forelse($topProducts as $index => $product)
                    <div class="product-margin-item gap-3">
                        <div class="flex min-w-0 items-center">
                            <span class="rank rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                            <div class="min-w-0">
                                <a
                                    href="{{ route('inventory.detail', $product['id']) }}"
                                    class="block truncate text-sm font-bold text-gray-800 hover:text-blue-600"
                                    title="{{ $product['name'] }}"
                                >
                                    {{ $product['name'] }}
                                </a>
                                <p class="truncate text-xs text-gray-400">
                                    {{ $product['product_code'] }} · Margin {{ number_format($product['margin'], 1, ',', '.') }}%
                                </p>
                            </div>
                        </div>

                        <div class="flex-shrink-0 text-right">
                            <p class="font-bold text-gray-800">
                                Rp {{ number_format($product['profit'], 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-gray-400">
                                Pendapatan Rp {{ number_format($product['revenue'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <div class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-xl">📦</div>
                        <p class="text-sm font-medium text-gray-600">Belum ada produk terjual</p>
                        <p class="mt-1 text-xs text-gray-400">Profitabilitas muncul setelah transaksi POS.</p>
                    </div>
                @endforelse
            </div>

            <a href="{{ route('inventory') }}" class="mt-4 block w-full text-center text-xs font-medium text-blue-600 hover:underline">
                Lihat Semua Produk →
            </a>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- LAYOUT 3 KOLOM: Forecast + New Sales -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Forecast - 3 kolom -->
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-800">📈 Prediksi Permintaan 7 Hari</h3>
                    <p class="text-xs text-gray-400">Prediksi permintaan berdasarkan riwayat transaksi POS</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $aiServiceStatus === 'online' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $aiServiceStatus === 'online' ? 'AI Online' : 'AI Offline' }}
                    </span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                        {{ $aiModeLabel }}
                    </span>
                </div>
            </div>

            <div class="forecast-grid">
                @forelse($aiForecastProducts as $item)
                    <div class="forecast-item min-w-0 border border-gray-100">
                        <a
                            href="{{ route('inventory.detail', $item['product_id']) }}"
                            class="day block truncate hover:text-blue-600"
                            title="{{ $item['product_name'] }}"
                        >
                            {{ $item['product_name'] }}
                        </a>

                        <p class="value mt-1">
                            {{ number_format($item['predicted_quantity'] ?? 0, 0, ',', '.') }}
                            <span class="text-xs font-medium text-gray-400">
                                {{ $item['unit'] ?? 'unit' }}
                            </span>
                        </p>

                        <div class="forecast-bar mt-2">
                            <div
                                class="forecast-bar-fill {{ ($item['method'] ?? '') === 'random_forest' ? 'confidence-high' : 'confidence-medium' }}"
                                style="width: {{ min(100, max(0, ((int) ($item['predicted_quantity'] ?? 0) / $maxForecastQuantity) * 100)) }}%"
                            >
                            </div>
                        </div>

                        <p class="mt-2 truncate text-[10px] text-gray-400" title="{{ $item['method_reason'] ?? '' }}">
                            {{ $item['method_label'] ?? $aiModeLabel }} ·
                            {{ $item['confidence_label'] ?? 'Belum tersedia' }}
                        </p>

                        @if((int) ($item['recommended_restock'] ?? 0) > 0)
                            <p class="mt-1 text-[10px] font-semibold text-blue-600">
                                Restok +{{ number_format($item['recommended_restock'], 0, ',', '.') }} {{ $item['unit'] ?? 'unit' }}
                            </p>
                        @else
                            <p class="mt-1 text-[10px] font-semibold text-emerald-600">
                                Stok masih mencukupi
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-8 text-center">
                        <p class="text-sm font-medium text-gray-600">
                            {{ $aiServiceStatus === 'online' ? 'Belum ada produk yang dapat diprediksi' : 'Service AI sedang offline' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ $aiServiceStatus === 'online' ? $aiSummary : $aiServiceMessage }}
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 border-t border-gray-100 pt-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Dianalisis</p>
                    <p class="mt-1 text-sm font-bold text-slate-700">
                        {{ number_format($aiAnalyzedProductCount) }} produk
                    </p>
                </div>

                <div class="rounded-xl bg-emerald-50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-500">Siap Diprediksi</p>
                    <p class="mt-1 text-sm font-bold text-emerald-700">
                        {{ number_format($aiReadyProductCount) }} produk
                    </p>
                </div>

                <div class="rounded-xl bg-amber-50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-500">Menunggu Data</p>
                    <p class="mt-1 text-sm font-bold text-amber-700">
                        {{ number_format($aiWaitingProductCount) }} produk
                    </p>
                </div>
            </div>

            <div class="mt-4 text-center text-xs text-gray-400">
                <i class="fas fa-robot mr-1"></i>
                {{ $aiSummary }}
            </div>
        </div>

        <!-- New Sales - 2 kolom -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800">🔥 Penjualan Baru</h3>
                    <p class="text-xs text-gray-400">Transaksi terakhir masuk</p>
                </div>
                <span class="text-xs bg-red-50 text-red-600 px-2 py-1 rounded-full font-medium">Live</span>
            </div>
            <div class="max-h-[240px] space-y-2 overflow-y-auto">
                @forelse($recentSales as $sale)
                    <div class="flex min-w-0 items-center justify-between gap-3 rounded-xl p-2 transition hover:bg-slate-50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-800" title="{{ $sale->invoice_number }}">
                                {{ $sale->invoice_number }}
                            </p>
                            <p class="truncate text-xs text-gray-400">
                                {{ $sale->cashier_name ?: 'User' }} ·
                                {{ strtoupper(str_replace('_', ' ', $sale->payment_method)) }} ·
                                {{ $sale->created_at->format('d M, H:i') }}
                            </p>
                        </div>

                        <span class="flex-shrink-0 font-bold text-emerald-600">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm font-medium text-gray-600">Belum ada penjualan</p>
                        <p class="mt-1 text-xs text-gray-400">Transaksi pada periode aktif akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            <a href="#transaction-history" class="mt-3 block w-full text-center text-xs font-medium text-blue-600 hover:underline">
                Lihat Semua Transaksi →
            </a>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- RIWAYAT TRANSAKSI -->
    <!-- ============================================= -->
    <div id="transaction-history" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-gray-800">📋 Riwayat Transaksi</h3>
                <p class="text-xs text-gray-400">Transaksi berhasil pada periode aktif</p>
            </div>

            <form action="{{ route('analytics') }}" method="GET" class="flex w-full flex-wrap gap-2 sm:w-auto">
                <input type="hidden" name="date_range" value="{{ $dateRange }}">
                <input
                    type="text"
                    name="search"
                    value="{{ $transactionSearch }}"
                    placeholder="Cari invoice, kasir, metode..."
                    class="min-w-0 flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100 sm:w-64"
                >
                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Cari
                </button>
                @if($transactionSearch !== '')
                    <a
                        href="{{ route('analytics', ['date_range' => $dateRange]) }}#transaction-history"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="w-full min-w-0 overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="pb-3 pr-4 text-xs font-bold text-gray-400 uppercase tracking-wider">ID Transaksi</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Kasir</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jumlah</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Metode</th>
                        <th class="pb-3 pl-4 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transactions as $transaction)
                        <tr class="table-row">
                            <td class="py-3 pr-4">
                                <span class="text-xs font-bold text-gray-800">
                                    {{ $transaction->invoice_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-700">
                                {{ $transaction->cashier_name ?: 'User' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="status-badge status-selesai">Selesai</span>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-800">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}
                            </td>
                            <td class="py-3 pl-4 text-right text-xs text-gray-500">
                                {{ $transaction->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center">
                                <p class="text-sm font-medium text-gray-600">Transaksi tidak ditemukan</p>
                                <p class="mt-1 text-xs text-gray-400">Coba ubah periode atau kata kunci pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-400">
                Menampilkan {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }}
                dari {{ $transactions->total() }} transaksi
            </p>
            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') {
            return;
        }

        const rupiahFormatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        });

        const compactNumberFormatter = new Intl.NumberFormat('id-ID', {
            notation: 'compact',
            maximumFractionDigits: 1,
        });

        const trendChartElement = document.getElementById('analyticsTrendChart');

        if (trendChartElement) {
            const trendLabels = @json($trendData['labels']);
            const trendRevenue = @json($trendData['revenue']);
            const trendProfit = @json($trendData['profit']);

            new Chart(trendChartElement.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: 'Pendapatan',
                            data: trendRevenue,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.10)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                            pointRadius: trendLabels.length > 30 ? 0 : 3,
                            pointHoverRadius: 5,
                        },
                        {
                            label: 'Laba',
                            data: trendProfit,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.35,
                            pointRadius: trendLabels.length > 30 ? 0 : 3,
                            pointHoverRadius: 5,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + rupiahFormatter.format(context.raw);
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            border: {
                                display: false,
                            },
                            ticks: {
                                color: '#94a3b8',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12,
                                font: {
                                    size: 10,
                                },
                            },
                        },
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
                                callback: function(value) {
                                    return 'Rp ' + compactNumberFormatter.format(value);
                                },
                            },
                        },
                    },
                },
            });
        }

        const revenueChartElement = document.getElementById('revenueChart');

        if (!revenueChartElement) {
            return;
        }

        new Chart(revenueChartElement.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json(array_column($revenueDistribution, 'label')),
                datasets: [{
                    data: @json(array_column($revenueDistribution, 'value')),
                    backgroundColor: @json(array_column($revenueDistribution, 'color')),
                    borderWidth: 3,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    });

</script>

@endsection
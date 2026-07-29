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
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    @media (max-width: 640px) {
        .forecast-grid {
            grid-template-columns: repeat(2, 1fr);
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

<div class="space-y-6">
    <!-- ============================================= -->
    <!-- HEADER -->
    <!-- ============================================= -->
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">📊 Analytics</h2>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                Paket Enterprise • Analisis real-time
            </p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.location.reload()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <button onclick="exportData()" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                <i class="fas fa-download mr-2"></i>Ekspor
            </button>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- FILTER -->
    <!-- ============================================= -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rentang Tanggal</label>
                <select name="date_range" class="filter-select w-full mt-1">
                    <option value="7_hari">7 Hari Terakhir</option>
                    <option value="30_hari" selected>30 Hari Terakhir</option>
                    <option value="90_hari">90 Hari Terakhir</option>
                    <option value="custom">Kustom</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</label>
                <select name="category" class="filter-select w-full mt-1">
                    <option value="all" selected>Semua Kategori</option>
                    <option value="elektronik">Elektronik</option>
                    <option value="gaya_hidup">Gaya Hidup</option>
                    <option value="makanan">Makanan</option>
                    <option value="aksesoris">Aksesoris</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Filter Produk</label>
                <select name="products" class="filter-select w-full mt-1">
                    <option value="all">4 Produk Terpilih</option>
                    @foreach($productList as $product)
                    <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button onclick="applyFilters()" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
                    <i class="fas fa-filter mr-2"></i>Terapkan Filter
                </button>
                <button onclick="clearFilters()" class="flex-1 border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                    Bersihkan
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- STATISTIK CARD - HORIZONTAL (KE SAMPING) -->
    <!-- ============================================= -->
    <div class="stats-row">
        <!-- Card 1: Total Pendapatan -->
        <div class="stat-card-horizontal">
            <div class="stat-icon stat-icon-blue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-number">${{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="stat-badge stat-badge-green">+{{ $stats['revenue_growth'] }}%</span>
                    <span class="stat-sub">Kinerja harian</span>
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
                    <span class="stat-badge stat-badge-green">+8.2%</span>
                    <span class="stat-sub">Transaksi sukses</span>
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
                <div class="stat-number">${{ number_format($stats['avg_order_value'], 2) }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="stat-badge stat-badge-green">+3.4%</span>
                    <span class="stat-sub">Nilai pesanan</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Konversi -->
        <div class="stat-card-horizontal">
            <div class="stat-icon stat-icon-amber">
                <i class="fas fa-percent"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Konversi</div>
                <div class="stat-number">{{ $stats['conversion_rate'] }}%</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="stat-badge stat-badge-amber">-0.5%</span>
                    <span class="stat-sub">Rasio konversi</span>
                </div>
            </div>
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
                    <p class="text-xs text-gray-400">Berdasarkan sektor utama</p>
                </div>
            </div>
            <div class="flex items-center gap-8">
                <!-- Pie Chart -->
                <div class="w-48 h-48 flex-shrink-0">
                    <canvas id="revenueChart"></canvas>
                </div>
                <!-- Legend -->
                <div class="space-y-2 flex-1">
                    @foreach($revenueDistribution as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background: {{ $item['color'] }}"></span>
                            <span class="text-sm text-gray-700">{{ $item['label'] }}</span>
                        </div>
                        <span class="font-bold text-gray-800">{{ $item['value'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Kanan: Profitabilitas Produk -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800">Profitabilitas Produk</h3>
                    <p class="text-xs text-gray-400">Item inventaris dengan performa margin teratas</p>
                </div>
                <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded-full font-medium">Top 5</span>
            </div>
            <div class="space-y-1">
                @foreach($topProducts as $index => $product)
                <div class="product-margin-item">
                    <div class="flex items-center">
                        <span class="rank rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $product['name'] }}</p>
                            <p class="text-xs text-gray-400">Margin {{ $product['margin'] }}%</p>
                        </div>
                    </div>
                    <span class="font-bold text-gray-800">${{ number_format($product['profit'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            <button class="w-full mt-4 text-xs text-blue-600 font-medium hover:underline text-center">
                Lihat Semua Produk →
            </button>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- LAYOUT 3 KOLOM: Forecast + New Sales -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Forecast - 3 kolom -->
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800">📈 Prakiraan Penjualan 7 Hari</h3>
                    <p class="text-xs text-gray-400">Pemodelan pasar prediktif berbasis AI</p>
                </div>
                <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-medium">
                    <i class="fas fa-circle text-[6px] mr-1 align-middle"></i> Keyakinan Tinggi
                </span>
            </div>
            <div class="forecast-grid">
                @foreach($forecast as $item)
                <div class="forecast-item">
                    <p class="day">{{ $item['day'] }}</p>
                    <p class="value">${{ number_format($item['value'], 0, ',', '.') }}</p>
                    <div class="forecast-bar mt-2">
                        <div class="forecast-bar-fill {{ $item['confidence'] === 'Tinggi' ? 'confidence-high' : ($item['confidence'] === 'Sedang' ? 'confidence-medium' : 'confidence-low') }}" 
                             style="width: {{ $item['value'] / max(array_column($forecast, 'value')) * 100 }}%">
                        </div>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">
                        <i class="fas fa-{{ $item['confidence'] === 'Tinggi' ? 'check-circle text-green-500' : ($item['confidence'] === 'Sedang' ? 'minus-circle text-amber-500' : 'exclamation-circle text-red-500') }} mr-1"></i>
                        {{ $item['confidence'] }}
                    </span>
                </div>
                @endforeach
            </div>
            <div class="mt-4 text-center text-xs text-gray-400">
                <i class="fas fa-robot mr-1"></i> Berdasarkan analisis AI dari data 6 bulan terakhir
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
            <div class="space-y-3 max-h-[220px] overflow-y-auto">
                @php
                $recentSales = array_slice($transactions, 0, 5);
                @endphp
                @foreach($recentSales as $sale)
                <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition">
                    <div>
                        <p class="font-bold text-gray-800 text-sm">{{ $sale['customer'] }}</p>
                        <p class="text-xs text-gray-400">{{ $sale['method'] }} • {{ $sale['date'] }}</p>
                    </div>
                    <span class="font-bold text-green-600">+${{ number_format($sale['amount'], 2) }}</span>
                </div>
                @endforeach
            </div>
            <button class="w-full mt-3 text-xs text-blue-600 font-medium hover:underline text-center">
                Lihat Semua Transaksi →
            </button>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- RIWAYAT TRANSAKSI -->
    <!-- ============================================= -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div>
                <h3 class="font-bold text-gray-800">📋 Riwayat Transaksi</h3>
                <p class="text-xs text-gray-400">Catatan waktu nyata operasi bisnis</p>
            </div>
            <div class="flex gap-3">
                <input type="text" id="searchTransaction" placeholder="Cari transaksi..." 
                       class="rounded-xl border border-slate-200 px-4 py-1.5 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                <button onclick="exportTable()" class="border border-slate-200 px-3 py-1.5 rounded-xl text-sm hover:bg-slate-50 transition">
                    <i class="fas fa-file-export mr-1"></i> Ekspor
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="pb-3 pr-4 text-xs font-bold text-gray-400 uppercase tracking-wider">ID Transaksi</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Pelanggan</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jumlah</th>
                        <th class="pb-3 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Metode</th>
                        <th class="pb-3 pl-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($transactions as $transaction)
                    <tr class="table-row">
                        <td class="py-3 pr-4">
                            <span class="font-bold text-gray-800 text-xs">{{ $transaction['id'] }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $transaction['customer'] }}</p>
                                <p class="text-[10px] text-gray-400">{{ $transaction['date'] }}</p>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="status-badge status-{{ strtolower($transaction['status']) }}">
                                {{ $transaction['status'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-bold text-gray-800">
                            ${{ number_format($transaction['amount'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            {{ $transaction['method'] }}
                        </td>
                        <td class="py-3 pl-4 text-right">
                            <button onclick="viewTransaction('{{ $transaction['id'] }}')" 
                                    class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 flex-wrap gap-2">
            <p class="text-xs text-gray-400">Menampilkan 1 sampai 10 dari {{ count($transactions) }} entri</p>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 transition">Previous</button>
                <button class="px-3 py-1 bg-blue-600 text-white rounded-lg text-sm">1</button>
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 transition">2</button>
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 transition">3</button>
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm hover:bg-slate-50 transition">Next</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Distribution Chart (Pie Chart)
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
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

    // Filter functions
    function applyFilters() {
        const dateRange = document.querySelector('[name="date_range"]').value;
        const category = document.querySelector('[name="category"]').value;
        const products = document.querySelector('[name="products"]').value;
        
        window.location.href = '{{ route("analytics") }}?date_range=' + dateRange + '&category=' + category + '&products=' + products;
    }

    function clearFilters() {
        window.location.href = '{{ route("analytics") }}';
    }

    // Search transaction
    document.getElementById('searchTransaction')?.addEventListener('keyup', function() {
        const search = this.value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    });

    function exportData() {
        alert('Fitur ekspor data akan segera tersedia!');
    }

    function exportTable() {
        alert('Fitur ekspor tabel akan segera tersedia!');
    }

    function viewTransaction(id) {
        alert('Detail transaksi: ' + id);
    }
</script>

@endsection
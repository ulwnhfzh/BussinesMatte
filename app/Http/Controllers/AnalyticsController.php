<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // ================================================================
        // 📊 DATA STATISTIK UTAMA
        // ================================================================
        // Cara Edit: Ubah nilai di bawah sesuai data bisnis Anda
        // ================================================================
        $stats = [
            'total_revenue' => 128450,        // 💰 Total pendapatan (dalam USD)
            'revenue_growth' => 12.5,         // 📈 Persentase pertumbuhan (%)
            'total_orders' => 420,            // 📦 Jumlah total transaksi
            'avg_order_value' => 305.83,      // 💵 Rata-rata nilai transaksi
            'conversion_rate' => 3.2,         // 🎯 Rasio konversi (%)
        ];

        // ================================================================
        // 📊 DISTRIBUSI PENDAPATAN (Pie Chart)
        // ================================================================
        // Cara Edit: 
        // - Tambah/hapus array di dalam $revenueDistribution
        // - Ubah 'label' sesuai kategori bisnis Anda
        // - Ubah 'value' sesuai persentase (total harus 100%)
        // - Ubah 'color' sesuai kode warna HEX yang diinginkan
        // ================================================================
        $revenueDistribution = [
            ['label' => 'Elektronik',   'value' => 60, 'color' => '#2563eb'],
            ['label' => 'Gaya Hidup',   'value' => 25, 'color' => '#8b5cf6'],
            ['label' => 'Makanan',      'value' => 10, 'color' => '#22c55e'],  // TAMBAH KATEGORI BARU
            ['label' => 'Lainnya',      'value' => 5,  'color' => '#f59e0b'],  // UBAH PERSENTASE
        ];

        // ================================================================
        // 🏆 PRODUK PROFITABILITAS (Top 5)
        // ================================================================
        // Cara Edit:
        // - Tambah/hapus array di dalam $topProducts
        // - Ubah 'name' sesuai nama produk Anda
        // - Ubah 'profit' sesuai keuntungan (dalam USD)
        // - Ubah 'margin' sesuai persentase margin (%)
        // ================================================================
        $topProducts = [
            ['name' => 'Quantum Tablet Pro',    'profit' => 12400, 'margin' => 32.5],
            ['name' => 'Nebula Wireless Hub',   'profit' => 9200,  'margin' => 28.7],
            ['name' => 'Apex Gaming Mouse',     'profit' => 7800,  'margin' => 25.1],
            ['name' => 'Crystal Sound Bar',     'profit' => 6500,  'margin' => 22.3], // TAMBAH PRODUK
            ['name' => 'OmniCharge Power Bank', 'profit' => 5200,  'margin' => 19.8],
            ['name' => 'UltraBook Pro 15"',     'profit' => 4800,  'margin' => 17.5], // TAMBAH PRODUK
        ];

        // ================================================================
        // 📈 PRAKIRAAN PENJUALAN 7 HARI (AI Forecast)
        // ================================================================
        // Cara Edit:
        // - Tambah/hapus array di dalam $forecast
        // - Ubah 'day' sesuai hari (Hari Ini, H+1, H+2, dst)
        // - Ubah 'value' sesuai prediksi nilai penjualan (dalam USD)
        // - Ubah 'confidence' sesuai tingkat keyakinan (Tinggi/Sedang/Rendah)
        // ================================================================
        $forecast = [
            ['day' => 'Hari Ini',   'value' => 12450, 'confidence' => 'Tinggi'],
            ['day' => 'H+1',        'value' => 11800, 'confidence' => 'Tinggi'], // TAMBAH HARI
            ['day' => 'H+2',        'value' => 11200, 'confidence' => 'Sedang'],
            ['day' => 'H+3',        'value' => 10500, 'confidence' => 'Sedang'], // TAMBAH HARI
            ['day' => 'H+4',        'value' => 9800,  'confidence' => 'Sedang'],
            ['day' => 'H+5',        'value' => 9200,  'confidence' => 'Rendah'], // TAMBAH HARI
            ['day' => 'H+7',        'value' => 13500, 'confidence' => 'Tinggi'],
        ];

        // ================================================================
        // 📋 RIWAYAT TRANSAKSI
        // ================================================================
        // Cara Edit:
        // - Tambah/hapus array di dalam $transactions
        // - Ubah 'id' sesuai format ID transaksi Anda
        // - Ubah 'customer' sesuai nama pelanggan
        // - Ubah 'status' (Selesai/Pending/Refund/Batal)
        // - Ubah 'amount' sesuai nominal (dalam USD)
        // - Ubah 'method' (Kartu Kredit/PayPal/Transfer Bank/Kredit Toko)
        // - Ubah 'date' sesuai tanggal transaksi
        // ================================================================
        $transactions = [
            [
                'id' => '#TX-90421',
                'customer' => 'Jordan Smith',
                'status' => 'Selesai',
                'amount' => 1240.00,
                'method' => 'Kartu Kredit',
                'date' => '2024-05-24 14:32'
            ],
            [
                'id' => '#TX-90422',
                'customer' => 'Maria Banks',
                'status' => 'Selesai',
                'amount' => 840.50,
                'method' => 'PayPal',
                'date' => '2024-05-24 13:15'
            ],
            [
                'id' => '#TX-90423',
                'customer' => 'Klien Anonim',
                'status' => 'Refund',
                'amount' => 125.00,
                'method' => 'Kredit Toko',
                'date' => '2024-05-24 12:45'
            ],
            [
                'id' => '#TX-90424',
                'customer' => 'Alex Johnson',
                'status' => 'Selesai',
                'amount' => 2100.00,
                'method' => 'Kartu Kredit',
                'date' => '2024-05-24 11:20'
            ],
            [
                'id' => '#TX-90425',
                'customer' => 'Sarah Lee',
                'status' => 'Pending',
                'amount' => 450.75,
                'method' => 'Transfer Bank',
                'date' => '2024-05-24 10:05'
            ],
            [
                'id' => '#TX-90426',
                'customer' => 'David Kim',
                'status' => 'Selesai',
                'amount' => 3200.00,
                'method' => 'Kartu Kredit',
                'date' => '2024-05-24 09:30'
            ],
            [
                'id' => '#TX-90427',
                'customer' => 'Lisa Wong',
                'status' => 'Selesai',
                'amount' => 675.25,
                'method' => 'PayPal',
                'date' => '2024-05-24 08:55'
            ],
            [
                'id' => '#TX-90428',
                'customer' => 'Robert Chen',
                'status' => 'Refund',
                'amount' => 890.00,
                'method' => 'Kredit Toko',
                'date' => '2024-05-24 08:10'
            ],
            // TAMBAH TRANSAKSI BARU DI SINI:
            [
                'id' => '#TX-90431',
                'customer' => 'Andi Pratama',
                'status' => 'Selesai',
                'amount' => 1550.00,
                'method' => 'Transfer Bank',
                'date' => '2024-05-25 09:00'
            ],
            [
                'id' => '#TX-90432',
                'customer' => 'Siti Rahayu',
                'status' => 'Pending',
                'amount' => 2300.00,
                'method' => 'Kartu Kredit',
                'date' => '2024-05-25 10:30'
            ],
        ];

        // ================================================================
        // 📦 PRODUK UNTUK FILTER
        // ================================================================
        // Cara Edit:
        // - Tambah/hapus array di dalam $productList
        // - Ubah 'id' sesuai ID produk (unik)
        // - Ubah 'name' sesuai nama produk
        // ================================================================
        $productList = [
            ['id' => 1, 'name' => 'Quantum Tablet Pro'],
            ['id' => 2, 'name' => 'Nebula Wireless Hub'],
            ['id' => 3, 'name' => 'Apex Gaming Mouse'],
            ['id' => 4, 'name' => 'Crystal Sound Bar'],
            ['id' => 5, 'name' => 'UltraBook Pro 15"'], // TAMBAH PRODUK
            ['id' => 6, 'name' => 'SmartWatch X7'],     // TAMBAH PRODUK
        ];

        // ================================================================
        // 📊 DATA CHART (untuk API endpoint)
        // ================================================================
        // Cara Edit:
        // - Ubah 'labels' sesuai bulan/tahun yang diinginkan
        // - Ubah 'values' sesuai data penjualan per bulan
        // ================================================================
        $chartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'values' => [42000, 48000, 52000, 61000, 58000, 72000, 68000, 79000, 85000, 92000, 88000, 102000],
        ];

        // Ambil parameter filter
        $dateRange = $request->get('date_range', '30 Hari Terakhir');
        $category = $request->get('category', 'Semua Kategori');
        $selectedProducts = $request->get('products', []);

        return view('analytics.index', compact(
            'stats',
            'revenueDistribution',
            'topProducts',
            'forecast',
            'transactions',
            'productList',
            'chartData',
            'dateRange',
            'category',
            'selectedProducts'
        ));
    }

    public function getChartData()
    {
        // Data untuk chart - bisa diambil dari database atau hardcoded
        $data = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'values' => [42000, 48000, 52000, 61000, 58000, 72000, 68000, 79000, 85000, 92000, 88000, 102000],
        ];
        return response()->json($data);
    }
}
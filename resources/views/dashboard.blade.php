@extends('layouts.app')

@section('title', 'Dashboard - UsahaMate')

@section('content')
    <!-- Header Overview -->
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dashboard Overview</h2>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Senin, 24 Mei 2024 · Ringkasan performa real-time Anda.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">Ekspor Laporan</button>
            <button class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"><span>⚡</span> Filter Laporan</button>
        </div>
    </div>

    <!-- 4 Kartu Statistik -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- Kartu 1 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center">+12.5%</span>
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1">TOTAL PENDAPATAN</p>
            <h3 class="text-xl font-bold text-gray-800">Rp 124.500.000</h3>
        </div>
        <!-- Kartu 2 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center">+8.2%</span>
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1">PELANGGAN BARU</p>
            <h3 class="text-xl font-bold text-gray-800">842 Jiwa</h3>
        </div>
        <!-- Kartu 3 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-red-50 p-2 rounded-lg text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></div>
                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center">KRITIS</span>
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1">PERINGATAN STOK</p>
            <h3 class="text-xl font-bold text-gray-800">12 SKU</h3>
        </div>
        <!-- Kartu 4 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-start mb-2">
                <div class="bg-yellow-50 p-2 rounded-lg text-yellow-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
                <span class="bg-white border text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center border-gray-200">AI PREDICT</span>
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1">PREDIKSI LABA</p>
            <h3 class="text-xl font-bold text-gray-800">Rp 42.1M</h3>
        </div>
    </div>

    <!-- Bagian Grid Chart + AI -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kolom Chart -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-gray-800">Analisis Penjualan Lanjut</h3>
                    <p class="text-xs text-gray-400">Perbandingan performa mingguan dan prediksi AI</p>
                </div>
                <div class="flex bg-gray-100 p-1 rounded-full text-xs font-semibold">
                    <button class="bg-blue-600 text-white px-3 py-1 rounded-full">Real-time</button>
                    <button class="px-3 py-1 text-gray-500 hover:text-gray-800">Historical</button>
                </div>
            </div>
            <div class="h-56 w-full relative">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Kolom AI -->
        <div class="card card-hover relative p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-50 p-1.5 rounded-lg text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg></div>
                    <span class="font-bold text-sm">AI INTELLIGENCE</span>
                </div>
                <span class="text-[10px] text-green-600 font-bold flex items-center gap-1"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Live</span>
            </div>
            <div class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-xl">
                    <p class="text-xs text-gray-700 leading-relaxed">"Stok Kopi Arabika Gayo tersisa 5 unit. Prediksi habis dalam 2 hari berdasarkan tren."</p>
                    <button class="mt-2 text-[10px] bg-white px-3 py-1 rounded-full shadow-sm border border-blue-100 text-blue-600 font-medium">Buat PO Supplier Sekarang →</button>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl">
                    <p class="text-xs text-gray-700 leading-relaxed">"Lonjakan transaksi sebesar <b>18%</b> diprediksi besok pukul 16:00. Disarankan menambah 1 kasir."</p>
                    <button class="mt-2 text-[10px] bg-white px-3 py-1 rounded-full shadow-sm border border-blue-100 text-blue-600 font-medium">Lihat Jadwal Shift →</button>
                </div>
            </div>
            <button class="w-full mt-6 bg-blue-50 text-blue-600 py-2.5 rounded-xl text-sm font-medium hover:bg-blue-100 transition">Tanya AI Lebih Lanjut</button>
        </div>
    </div>

    <!-- Tabel Produk & Aktivitas -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="font-bold text-gray-800">Performa Produk Unggulan</h3>
                    <p class="text-xs text-gray-400">Metrik profitabilitas dan perputaran stok</p>
                </div>
                <a href="#" class="text-xs text-blue-600 font-medium hover:underline">Download Detail</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[10px] text-gray-400 uppercase border-b">
                        <tr>
                            <th class="pb-3 pr-4 font-medium">PRODUK</th>
                            <th class="pb-3 px-4 font-medium">STATUS STOK</th>
                            <th class="pb-3 px-4 font-medium">VOLUME</th>
                            <th class="pb-3 px-4 font-medium">MARGIN</th>
                            <th class="pb-3 pl-4 font-medium text-right">REVENUE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-4 pr-4 flex items-center gap-3">
                                <img src="https://via.placeholder.com/40x40/2563eb/ffffff?text=K" class="w-10 h-10 rounded-lg object-cover">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">Kopi Arabika Gayo</p>
                                    <p class="text-[10px] text-gray-400">SKU: CF-0407-256</p>
                                </div>
                            </td>
                            <td class="py-4 px-4"><span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">65 / 100 unit</span> <span class="text-[10px] text-green-600 ml-1 font-bold">Aman</span></td>
                            <td class="py-4 px-4 font-semibold">1,240</td>
                            <td class="py-4 px-4 font-semibold">32.4%</td>
                            <td class="py-4 pl-4 text-right font-bold text-gray-800">Rp 42.5M</td>
                        </tr>
                        <tr>
                            <td class="py-4 pr-4 flex items-center gap-3">
                                <img src="https://via.placeholder.com/40x40/e74c3c/ffffff?text=T" class="w-10 h-10 rounded-lg object-cover">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">Teh Hijau Melati</p>
                                    <p class="text-[10px] text-gray-400">SKU: TE-JASMINE-012</p>
                                </div>
                            </td>
                            <td class="py-4 px-4"><span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">12 / 150 unit</span> <span class="text-[10px] text-red-600 ml-1 font-bold">Low Stock</span></td>
                            <td class="py-4 px-4 font-semibold">890</td>
                            <td class="py-4 px-4 font-semibold">28.1%</td>
                            <td class="py-4 pl-4 text-right font-bold text-gray-800">Rp 18.2M</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-hover p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800">AKTIVITAS TERKINI</h3>
                <a href="#" class="text-xs text-blue-600 font-medium hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-6">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">TRX-982101 Berhasil</p>
                        <p class="text-[10px] text-gray-400">Kelas: Sarah · BCA ORIS</p>
                        <p class="text-xs font-semibold mt-1">Rp 155.600</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">Sinkronasi Invent...</p>
                        <p class="text-[10px] text-gray-400">Gudang · Central Jakarta</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">TRX-982098 Gagal</p>
                        <p class="text-[10px] text-gray-400">Masalah: Timeout Gateway</p>
                        <p class="text-[10px] text-red-500 mt-0.5 font-medium">Retrigger</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT CHART.JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB', 'MIN'],
                    datasets: [{
                        label: 'Penjualan',
                        data: [12, 15, 24, 18, 25, 20, 16],
                        backgroundColor: ['#dbeafe', '#dbeafe', '#2563eb', '#dbeafe', '#dbeafe', '#dbeafe', '#dbeafe'],
                        borderRadius: 8,
                        barThickness: 25,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { display: false, beginAtZero: true },
                        x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' }, color: '#9ca3af' } }
                    }
                }
            });
        });
    </script>
@endsection
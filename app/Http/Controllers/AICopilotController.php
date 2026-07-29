<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AICopilotController extends Controller
{
    public function index()
    {
        // Data dummy untuk AI Copilot
        $suggestions = [
            [
                'title' => 'Stok Kopi Arabika Gayo',
                'message' => 'Stok tersisa 5 unit. Prediksi habis dalam 2 hari berdasarkan tren penjualan.',
                'action' => 'Buat PO Supplier Sekarang',
                'action_url' => '#'
            ],
            [
                'title' => 'Lonjakan Transaksi',
                'message' => 'Lonjakan transaksi sebesar 18% diprediksi besok pukul 16:00. Disarankan menambah 1 kasir.',
                'action' => 'Lihat Jadwal Shift',
                'action_url' => '#'
            ],
            [
                'title' => 'Rekomendasi Produk',
                'message' => 'Produk Teh Hijau Melati memiliki potensi kenaikan 25% jika dipromosikan dengan diskon 10%.',
                'action' => 'Buat Promosi',
                'action_url' => '#'
            ]
        ];

        return view('ai-copilot.index', compact('suggestions'));
    }
}
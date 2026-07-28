<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        // Contoh data dummy (Ini akan muncul di tabel Anda)
        $products = [
            [
                'id' => 1,
                'name' => 'Nexus V Pro Headphones',
                'sku' => 'NV-2024-X1',
                'stock' => 120,
                'capacity' => 425,
                'max_capacity' => 500,
                'status' => 'optimal'
            ],
            [
                'id' => 2,
                'name' => 'OmniWatch Series 7',
                'sku' => 'OW-707-GLD',
                'stock' => 80,
                'capacity' => 110,
                'max_capacity' => 200,
                'status' => 'kritis'
            ],
            [
                'id' => 3,
                'name' => 'ProDisplay 32" 4K',
                'sku' => 'PD-32K-MON',
                'stock' => 20,
                'capacity' => 24,
                'max_capacity' => 50,
                'status' => 'peringatan'
            ],
        ];

        // Kirim data ke view
        return view('inventory.index', compact('products'));
    }

    public function show($id)
    {
        // Ini adalah halaman yang akan dituju saat item diklik
        return "Ini adalah halaman detail untuk produk dengan ID: " . $id;
    }
}
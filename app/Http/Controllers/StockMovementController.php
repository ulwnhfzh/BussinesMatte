<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
    /**
     * Menampilkan riwayat pergerakan stok.
     */
    public function index(Request $request)
    {
        $businessId = (int) Auth::user()->business_id;

        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where(
                        'business_id',
                        $businessId
                    )
                ),
            ],

            'type' => [
                'nullable',
                Rule::in([
                    StockMovement::TYPE_INITIAL,
                    StockMovement::TYPE_SALE,
                    StockMovement::TYPE_PURCHASE,
                    StockMovement::TYPE_ADJUSTMENT,
                    StockMovement::TYPE_RETURN,
                ]),
            ],

            'direction' => [
                'nullable',
                Rule::in([
                    'incoming',
                    'outgoing',
                ]),
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        /*
         * Query selalu dibatasi berdasarkan business_id
         * user yang sedang login.
         */
        $query = StockMovement::forBusiness($businessId)
            ->with([
    'product',
    'user',
    'reference',
]);
        // ===== PENCARIAN =====
        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'product_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'product_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'note',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        // ===== FILTER PRODUK =====
        if (!empty($validated['product_id'])) {
            $query->where(
                'product_id',
                $validated['product_id']
            );
        }

        // ===== FILTER JENIS AKTIVITAS =====
        if (!empty($validated['type'])) {
            $query->where(
                'type',
                $validated['type']
            );
        }

        // ===== FILTER ARAH STOK =====
        if (!empty($validated['direction'])) {
            if ($validated['direction'] === 'incoming') {
                $query->where('quantity', '>', 0);
            }

            if ($validated['direction'] === 'outgoing') {
                $query->where('quantity', '<', 0);
            }
        }

        // ===== FILTER TANGGAL AWAL =====
        if (!empty($validated['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $validated['date_from']
            );
        }

        // ===== FILTER TANGGAL AKHIR =====
        if (!empty($validated['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $validated['date_to']
            );
        }

        // Aktivitas terbaru, 15 data per halaman.
        $movements = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        /*
         * Daftar produk untuk filter.
         * Hanya mengambil produk milik business yang login.
         */
        $products = Product::where(
            'business_id',
            $businessId
        )
            ->orderBy('name')
            ->get([
                'id',
                'product_code',
                'name',
            ]);

        /*
         * Ringkasan aktivitas seluruh tenant.
         * Tidak dipengaruhi filter halaman.
         */
        $summaryQuery = StockMovement::forBusiness($businessId);

        $totalMovements = (clone $summaryQuery)->count();

        $totalIncoming = (int) (clone $summaryQuery)
            ->where('quantity', '>', 0)
            ->sum('quantity');

        $totalOutgoing = abs(
            (int) (clone $summaryQuery)
                ->where('quantity', '<', 0)
                ->sum('quantity')
        );

        $typeOptions = [
            StockMovement::TYPE_INITIAL => 'Stok Awal',
            StockMovement::TYPE_SALE => 'Penjualan',
            StockMovement::TYPE_PURCHASE => 'Stok Masuk',
            StockMovement::TYPE_ADJUSTMENT => 'Penyesuaian',
            StockMovement::TYPE_RETURN => 'Retur',
        ];

        return view(
            'inventory.stock-movements.index',
            compact(
                'movements',
                'products',
                'typeOptions',
                'totalMovements',
                'totalIncoming',
                'totalOutgoing'
            )
        );
    }
}
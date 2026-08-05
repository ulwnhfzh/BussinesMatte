<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\PredictionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        $businessId = (int) Auth::user()->business_id;
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan transaksi hari ini
        |--------------------------------------------------------------------------
        */
        $todaySummary = Transaction::where('business_id', $businessId)
            ->whereDate('created_at', $now->toDateString())
            ->selectRaw('
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_amount), 0) as revenue,
                COALESCE(SUM(total_profit), 0) as profit
            ')
            ->first();

        $todayRevenue = (float) $todaySummary->revenue;
        $todayTransactionCount = (int) $todaySummary->transaction_count;
        $todayProfit = (float) $todaySummary->profit;

        /*
        |--------------------------------------------------------------------------
        | Produk dengan stok kritis
        |--------------------------------------------------------------------------
        */
        $criticalStockCount = Product::where('business_id', $businessId)
            ->whereColumn('stock', '<', 'minimum_stock')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Pendapatan tujuh hari terakhir
        |--------------------------------------------------------------------------
        */
        $startDate = $now->copy()->subDays(6)->startOfDay();

        $revenueByDate = Transaction::where('business_id', $businessId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                DATE(created_at) as transaction_date,
                SUM(total_amount) as total_revenue
            ')
            ->groupBy('transaction_date')
            ->pluck('total_revenue', 'transaction_date');

        $chartLabels = [];
        $chartRevenue = [];

        for ($day = 6; $day >= 0; $day--) {
            $date = $now->copy()->subDays($day);
            $dateKey = $date->toDateString();

            $chartLabels[] = strtoupper(
                $date->locale('id')->translatedFormat('D')
            );

            $chartRevenue[] = (float) (
                $revenueByDate[$dateKey] ?? 0
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lima produk unggulan dalam 30 hari terakhir
        |--------------------------------------------------------------------------
        */
        $topProducts = TransactionDetail::query()
            ->join(
                'transactions',
                'transaction_details.transaction_id',
                '=',
                'transactions.id'
            )
            ->join(
                'products',
                'transaction_details.product_id',
                '=',
                'products.id'
            )
            ->where('transactions.business_id', $businessId)
            ->where('products.business_id', $businessId)
            ->where(
                'transactions.created_at',
                '>=',
                $now->copy()->subDays(29)->startOfDay()
            )
            ->select([
                'products.id as product_id',
                'products.name',
                'products.product_code',
                'products.image',
                'products.stock',
                'products.minimum_stock',
                'products.maximum_stock',
                'products.unit',
            ])
            ->selectRaw(
                'SUM(transaction_details.quantity) as sold_quantity'
            )
            ->selectRaw(
                'SUM(transaction_details.subtotal) as total_revenue'
            )
            ->selectRaw('
                SUM(
                    (
                        transaction_details.selling_price
                        - transaction_details.purchase_price
                    ) * transaction_details.quantity
                ) as total_profit
            ')
            ->groupBy([
                'products.id',
                'products.name',
                'products.product_code',
                'products.image',
                'products.stock',
                'products.minimum_stock',
                'products.maximum_stock',
                'products.unit',
            ])
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $revenue = (float) $product->total_revenue;
                $profit = (float) $product->total_profit;

                $product->margin_percentage = $revenue > 0
                    ? ($profit / $revenue) * 100
                    : 0;

                return $product;
            });

        /*
        |--------------------------------------------------------------------------
        | Aktivitas transaksi terbaru
        |--------------------------------------------------------------------------
        */
        $transactionActivities = Transaction::where('business_id', $businessId)
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'invoice_number',
                'total_amount',
                'payment_method',
                'created_at',
            ])
            ->map(function ($transaction) {
                return [
                    'category' => 'transaction',
                    'title' => 'Transaksi ' . $transaction->invoice_number,
                    'description' => 'Pembayaran ' . strtoupper($transaction->payment_method),
                    'amount' => (float) $transaction->total_amount,
                    'quantity' => null,
                    'created_at' => $transaction->created_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Aktivitas stok terbaru selain penjualan POS
        |--------------------------------------------------------------------------
        */
        $stockMovementActivities = StockMovement::where('business_id', $businessId)
            ->where('type', '!=', 'sale')
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'type',
                'quantity',
                'product_name',
                'stock_before',
                'stock_after',
                'created_at',
            ])
            ->map(function ($movement) {
                $movementLabels = [
                    'initial' => 'Stok Awal',
                    'adjustment' => 'Penyesuaian Stok',
                    'restock' => 'Restok Produk',
                    'return' => 'Retur Produk',
                    'refund' => 'Refund Produk',
                    'void' => 'Void Transaksi',
                ];

                return [
                    'category' => 'stock',
                    'title' => $movementLabels[$movement->type]
                        ?? ucwords(str_replace('_', ' ', $movement->type)),
                    'description' => $movement->product_name
                        . ' · ' . $movement->stock_before
                        . ' → ' . $movement->stock_after,
                    'amount' => null,
                    'quantity' => (int) $movement->quantity,
                    'created_at' => $movement->created_at,
                ];
            });

        $recentActivities = $transactionActivities
            ->concat($stockMovementActivities)
            ->sortByDesc(function ($activity) {
                return $activity['created_at']->timestamp;
            })
            ->take(5)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Insight AI Inventory
        |--------------------------------------------------------------------------
        |
        | Cache dipisahkan menggunakan business_id agar hasil prediksi tenant
        | yang satu tidak pernah digunakan oleh tenant lainnya.
        |
        */
        try {
            $aiPrediction = Cache::remember(
                'dashboard.ai-prediction.' . $businessId,
                now()->addMinutes(3),
                function () {
                    return (new PredictionService())->getPrediction();
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            $aiPrediction = [
                'service_status' => 'offline',
                'service_message' => 'Service AI sedang tidak dapat dihubungi.',
                'mode' => 'no_data',
                'mode_label' => 'Belum Ada Prediksi',
                'summary' => 'Prediksi belum tersedia.',
                'products' => [],
            ];
        }

        $aiServiceStatus = $aiPrediction['service_status'] ?? 'offline';
        $aiServiceMessage = $aiPrediction['service_message']
            ?? 'Status service AI belum tersedia.';
        $aiModeLabel = $aiPrediction['mode_label']
            ?? 'Belum Ada Prediksi';
        $aiSummary = $aiPrediction['summary']
            ?? 'Prediksi belum tersedia.';

        $aiRecommendations = collect($aiPrediction['products'] ?? [])
            ->filter(function ($product) {
                return (int) ($product['recommended_restock'] ?? 0) > 0;
            })
            ->sortByDesc(function ($product) {
                return (int) ($product['recommended_restock'] ?? 0);
            })
            ->take(2)
            ->values();

        return view('dashboard', compact(
            'todayRevenue',
            'todayTransactionCount',
            'criticalStockCount',
            'todayProfit',
            'chartLabels',
            'chartRevenue',
            'topProducts',
            'recentActivities',
            'aiServiceStatus',
            'aiServiceMessage',
            'aiModeLabel',
            'aiSummary',
            'aiRecommendations'
        ));
    }
}
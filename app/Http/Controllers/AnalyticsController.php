<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\PredictionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $businessId = (int) Auth::user()->business_id;

        /*
        |--------------------------------------------------------------------------
        | Filter periode
        |--------------------------------------------------------------------------
        */
        $allowedDateRanges = [
            '7_hari' => 7,
            '30_hari' => 30,
            '90_hari' => 90,
        ];

        $dateRange = $request->get('date_range', '30_hari');

        if (!array_key_exists($dateRange, $allowedDateRanges)) {
            $dateRange = '30_hari';
        }

        $periodDays = $allowedDateRanges[$dateRange];
        $periodEnd = now()->endOfDay();
        $periodStart = now()
            ->subDays($periodDays - 1)
            ->startOfDay();

        // Periode sebelumnya memiliki jumlah hari yang sama.
        $previousPeriodEnd = $periodStart->copy()->subSecond();
        $previousPeriodStart = $previousPeriodEnd
            ->copy()
            ->subDays($periodDays - 1)
            ->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Statistik periode aktif
        |--------------------------------------------------------------------------
        */
        $currentSummary = Transaction::where('business_id', $businessId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(AVG(total_amount), 0) as avg_order_value,
                COALESCE(SUM(total_profit), 0) as total_profit
            ')
            ->first();

        $previousRevenue = (float) Transaction::where(
            'business_id',
            $businessId
        )
            ->whereBetween(
                'created_at',
                [$previousPeriodStart, $previousPeriodEnd]
            )
            ->sum('total_amount');

        $currentRevenue = (float) $currentSummary->total_revenue;

        if ($previousRevenue > 0) {
            $revenueGrowth = (
                ($currentRevenue - $previousRevenue) / $previousRevenue
            ) * 100;
        } else {
            // Pertumbuhan dari nilai nol tidak dapat dihitung secara persentase.
            $revenueGrowth = null;
        }

        $stats = [
            'total_revenue' => $currentRevenue,
            'revenue_growth' => $revenueGrowth !== null
                ? round($revenueGrowth, 1)
                : null,
            'total_orders' => (int) $currentSummary->total_orders,
            'avg_order_value' => (float) $currentSummary->avg_order_value,
            'total_profit' => (float) $currentSummary->total_profit,
        ];

        /*
        |--------------------------------------------------------------------------
        | Distribusi pendapatan berdasarkan kategori produk
        |--------------------------------------------------------------------------
        */
        $categoryRevenue = TransactionDetail::query()
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
            ->whereBetween(
                'transactions.created_at',
                [$periodStart, $periodEnd]
            )
            ->selectRaw("
                COALESCE(NULLIF(products.category, ''), 'Tanpa Kategori')
                    as category_name,
                SUM(transaction_details.subtotal) as category_revenue
            ")
            ->groupBy('category_name')
            ->orderByDesc('category_revenue')
            ->get();

        $distributionTotal = (float) $categoryRevenue->sum(
            'category_revenue'
        );

        $distributionColors = [
            '#2563eb',
            '#8b5cf6',
            '#22c55e',
            '#f59e0b',
            '#ef4444',
            '#06b6d4',
            '#64748b',
        ];

        $revenueDistribution = $categoryRevenue
            ->map(function ($item, $index) use (
                $distributionTotal,
                $distributionColors
            ) {
                return [
                    'label' => $item->category_name,
                    'revenue' => (float) $item->category_revenue,
                    'value' => $distributionTotal > 0
                        ? round(
                            ((float) $item->category_revenue
                                / $distributionTotal) * 100,
                            1
                        )
                        : 0,
                    'color' => $distributionColors[
                        $index % count($distributionColors)
                    ],
                ];
            })
            ->values()
            ->all();

        // Kategori dengan kontribusi pendapatan terbesar untuk ringkasan kartu.
        $topRevenueCategory = $revenueDistribution[0] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Lima produk dengan total laba tertinggi
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
            ->whereBetween(
                'transactions.created_at',
                [$periodStart, $periodEnd]
            )
            ->select([
                'products.id',
                'products.name',
                'products.product_code',
            ])
            ->selectRaw(
                'SUM(transaction_details.subtotal) as revenue'
            )
            ->selectRaw('
                SUM(
                    (
                        transaction_details.selling_price
                        - transaction_details.purchase_price
                    ) * transaction_details.quantity
                ) as profit
            ')
            ->groupBy([
                'products.id',
                'products.name',
                'products.product_code',
            ])
            ->orderByDesc('profit')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $revenue = (float) $product->revenue;
                $profit = (float) $product->profit;

                return [
                    'id' => (int) $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'revenue' => $revenue,
                    'profit' => $profit,
                    'margin' => $revenue > 0
                        ? round(($profit / $revenue) * 100, 1)
                        : 0,
                ];
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Prediksi permintaan produk dari service AI
        |--------------------------------------------------------------------------
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

        $aiProducts = collect($aiPrediction['products'] ?? []);

        $aiReadyProducts = $aiProducts
            ->filter(function ($product) {
                return ($product['method'] ?? 'no_data') !== 'no_data'
                    || (int) ($product['predicted_quantity'] ?? 0) > 0;
            });

        $aiAnalyzedProductCount = $aiProducts->count();
        $aiReadyProductCount = $aiReadyProducts->count();
        $aiWaitingProductCount = max(
            0,
            $aiAnalyzedProductCount - $aiReadyProductCount
        );

        $aiForecastProducts = $aiReadyProducts
            ->sortByDesc(function ($product) {
                return (int) ($product['predicted_quantity'] ?? 0);
            })
            ->take(4)
            ->values();

        $maxForecastQuantity = max(
            1,
            (int) $aiForecastProducts->max('predicted_quantity')
        );

        /*
        |--------------------------------------------------------------------------
        | Penjualan terbaru dan riwayat transaksi
        |--------------------------------------------------------------------------
        */
        $transactionSearch = trim(
            (string) $request->get('search', '')
        );

        $transactionBaseQuery = Transaction::query()
            ->leftJoin(
                'users',
                'transactions.user_id',
                '=',
                'users.id'
            )
            ->where('transactions.business_id', $businessId)
            ->whereBetween(
                'transactions.created_at',
                [$periodStart, $periodEnd]
            )
            ->select([
                'transactions.id',
                'transactions.invoice_number',
                'transactions.total_amount',
                'transactions.total_profit',
                'transactions.payment_method',
                'transactions.created_at',
                'users.name as cashier_name',
            ]);

        $recentSales = (clone $transactionBaseQuery)
            ->latest('transactions.created_at')
            ->limit(5)
            ->get();

        $transactions = (clone $transactionBaseQuery)
            ->when(
                $transactionSearch !== '',
                function ($query) use ($transactionSearch) {
                    $query->where(function ($searchQuery) use (
                        $transactionSearch
                    ) {
                        $searchQuery
                            ->where(
                                'transactions.invoice_number',
                                'like',
                                '%' . $transactionSearch . '%'
                            )
                            ->orWhere(
                                'transactions.payment_method',
                                'like',
                                '%' . $transactionSearch . '%'
                            )
                            ->orWhere(
                                'users.name',
                                'like',
                                '%' . $transactionSearch . '%'
                            );
                    });
                }
            )
            ->latest('transactions.created_at')
            ->paginate(10)
            ->withQueryString();

        $trendData = $this->buildTrendData(
            $businessId,
            $periodStart,
            $periodEnd,
            $periodDays
        );

        return view('analytics.index', compact(
            'stats',
            'revenueDistribution',
            'topRevenueCategory',
            'topProducts',
            'aiServiceStatus',
            'aiServiceMessage',
            'aiModeLabel',
            'aiSummary',
            'aiForecastProducts',
            'aiAnalyzedProductCount',
            'aiReadyProductCount',
            'aiWaitingProductCount',
            'maxForecastQuantity',
            'transactions',
            'recentSales',
            'transactionSearch',
            'trendData',
            'dateRange',
            'periodDays',
            'periodStart',
            'periodEnd'
        ));
    }

    public function getChartData(Request $request)
    {
        $businessId = (int) Auth::user()->business_id;

        $allowedDateRanges = [
            '7_hari' => 7,
            '30_hari' => 30,
            '90_hari' => 90,
        ];

        $dateRange = $request->get('date_range', '30_hari');
        $periodDays = $allowedDateRanges[$dateRange] ?? 30;
        $periodEnd = now()->endOfDay();
        $periodStart = now()
            ->subDays($periodDays - 1)
            ->startOfDay();

        return response()->json(
            $this->buildTrendData(
                $businessId,
                $periodStart,
                $periodEnd,
                $periodDays
            )
        );
    }

    private function buildTrendData(
        int $businessId,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $periodDays
    ): array {
        $dailyTotals = Transaction::where('business_id', $businessId)
            ->whereBetween(
                'created_at',
                [$periodStart, $periodEnd]
            )
            ->selectRaw('
                DATE(created_at) as transaction_date,
                SUM(total_amount) as revenue,
                SUM(total_profit) as profit
            ')
            ->groupBy('transaction_date')
            ->get()
            ->keyBy('transaction_date');

        $labels = [];
        $revenue = [];
        $profit = [];
        $cursor = $periodStart->copy();

        while ($cursor->lte($periodEnd)) {
            $dateKey = $cursor->toDateString();
            $dailyData = $dailyTotals->get($dateKey);

            $labels[] = $periodDays <= 7
                ? strtoupper(
                    $cursor->locale('id')->translatedFormat('D')
                )
                : $cursor->locale('id')->translatedFormat('d M');

            $revenue[] = $dailyData
                ? (float) $dailyData->revenue
                : 0;
            $profit[] = $dailyData
                ? (float) $dailyData->profit
                : 0;

            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'profit' => $profit,
        ];
    }
}
<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PredictionService
{
    // Versi terintegrasi: Laravel -> FastAPI -> gambar -> fallback Moving Average.
    private const HISTORY_DAYS = 56;

    private const FORECAST_DAYS = 7;

    private string $apiUrl;

    private int $apiTimeout;

    public function __construct()
    {
        $this->apiUrl = (string) config(
            'prediction.url',
            'http://127.0.0.1:8001/predict'
        );

        $this->apiTimeout = (int) config(
            'prediction.timeout',
            30
        );
    }

    /**
     * Mengambil prediksi untuk bisnis dari user yang sedang login.
     *
     * Python memilih Random Forest atau Moving Average per produk.
     * Jika service Python gagal, Laravel tetap menghasilkan Moving Average.
     */
    public function getPrediction(): array
    {
        $businessId = (int) Auth::user()->business_id;

        $products = Product::where('business_id', $businessId)
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'stock',
                'minimum_stock',
                'maximum_stock',
                'purchase_price',
                'unit',
                'image',
                'created_at',
            ]);

        if ($products->isEmpty()) {
            return $this->emptyResponse();
        }

        $salesByProduct = $this->getDailySales($businessId);

        $dailySeriesByProduct = $products->mapWithKeys(
            function (Product $product) use ($salesByProduct) {
                $salesRows = $salesByProduct->get(
                    $product->id,
                    collect()
                );

                return [
                    (string) $product->id => $this->buildDailySeries(
                        $product,
                        $salesRows
                    ),
                ];
            }
        );

        // Baseline selalu disiapkan agar aplikasi tetap bekerja saat AI mati.
        $baselinePredictions = $products->map(
            function (Product $product) use ($dailySeriesByProduct) {
                $series = $dailySeriesByProduct->get(
                    (string) $product->id,
                    []
                );

                return $this->predictWithMovingAverage(
                    $product,
                    $series
                );
            }
        );

        $historyPayload = $this->buildHistoryPayload(
            $dailySeriesByProduct
        );

        $apiResult = $this->requestPythonPrediction(
            $businessId,
            $historyPayload
        );

        if (!$apiResult['success']) {
            return $this->buildResponse(
                $baselinePredictions,
                'fallback',
                $apiResult['message']
            );
        }

        $predictions = $this->mergePythonPredictions(
            $products,
            $baselinePredictions,
            $apiResult['data']
        );

        return $this->buildResponse(
            $predictions,
            'online',
            'Service AI terhubung dan pemilihan metode berjalan otomatis.'
        );
    }

    /**
     * Mengambil total penjualan harian per produk.
     *
     * Filter dilakukan pada transactions dan products agar detail transaksi
     * yang tidak konsisten tidak dapat mencampurkan data tenant lain.
     */
    private function getDailySales(int $businessId): Collection
    {
        $endDate = now()->endOfDay();
        $startDate = now()
            ->subDays(self::HISTORY_DAYS - 1)
            ->startOfDay();

        return DB::table('transaction_details')
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_details.transaction_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'transaction_details.product_id'
            )
            ->select([
                'transaction_details.product_id',
                DB::raw(
                    'DATE(transactions.created_at) AS sale_date'
                ),
                DB::raw(
                    'SUM(transaction_details.quantity) AS quantity'
                ),
            ])
            ->where('transactions.business_id', $businessId)
            ->where('products.business_id', $businessId)
            ->whereBetween(
                'transactions.created_at',
                [$startDate, $endDate]
            )
            ->groupBy(
                'transaction_details.product_id',
                DB::raw('DATE(transactions.created_at)')
            )
            ->orderBy('sale_date')
            ->get()
            ->groupBy('product_id');
    }

    /**
     * Membuat deret tanggal => jumlah dan mengisi hari tanpa transaksi dengan 0.
     */
    private function buildDailySeries(
        Product $product,
        Collection $salesRows
    ): array {
        $today = now()->startOfDay();

        $historyStart = now()
            ->subDays(self::HISTORY_DAYS - 1)
            ->startOfDay();

        $productCreatedAt = $product->created_at
            ? Carbon::parse($product->created_at)->startOfDay()
            : $historyStart->copy();

        $startDate = $productCreatedAt->greaterThan($historyStart)
            ? $productCreatedAt
            : $historyStart;

        // Melindungi loop jika created_at produk berada di masa depan.
        if ($startDate->greaterThan($today)) {
            $startDate = $today->copy();
        }

        $quantityByDate = $salesRows->mapWithKeys(
            function ($row) {
                return [
                    (string) $row->sale_date => (int) $row->quantity,
                ];
            }
        );

        $series = [];

        for (
            $date = $startDate->copy();
            $date->lessThanOrEqualTo($today);
            $date->addDay()
        ) {
            $dateKey = $date->format('Y-m-d');
            $series[$dateKey] = (int) $quantityByDate->get(
                $dateKey,
                0
            );
        }

        return $series;
    }

    /**
     * Payload hanya berisi produk yang sebelumnya sudah difilter business_id.
     */
    private function buildHistoryPayload(
        Collection $dailySeriesByProduct
    ): array {
        $history = [];

        foreach ($dailySeriesByProduct as $productId => $series) {
            foreach ($series as $date => $quantity) {
                $history[] = [
                    'product_id' => (string) $productId,
                    'date' => $date,
                    'quantity' => (int) $quantity,
                ];
            }
        }

        return $history;
    }

    /**
     * Mengirim histori satu tenant ke service Python.
     */
    private function requestPythonPrediction(
        int $businessId,
        array $history
    ): array {
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout($this->apiTimeout)
                ->post($this->apiUrl, [
                    'business_id' => $businessId,
                    'history' => $history,
                    'prediction_days' => self::FORECAST_DAYS,
                ]);

            if (!$response->successful()) {
                Log::warning('Prediction API mengembalikan error.', [
                    'business_id' => $businessId,
                    'status' => $response->status(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Service AI memberikan respons tidak valid.',
                    'data' => [],
                ];
            }

            $data = $response->json();

            if (!is_array($data) || !isset($data['products'])) {
                return [
                    'success' => false,
                    'message' => 'Format respons service AI tidak sesuai.',
                    'data' => [],
                ];
            }

            return [
                'success' => true,
                'message' => null,
                'data' => $data,
            ];
        } catch (Throwable $error) {
            Log::warning('Prediction API tidak dapat dihubungi.', [
                'business_id' => $businessId,
                'message' => $error->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Service AI tidak dapat dihubungi; Moving Average digunakan sebagai fallback.',
                'data' => [],
            ];
        }
    }

    /**
     * Menggabungkan forecast Python dengan stok dan harga milik Laravel.
     *
     * Hanya ID produk milik tenant yang digunakan. Produk asing dari respons
     * Python akan otomatis diabaikan karena tidak berada di baselinePredictions.
     */
    private function mergePythonPredictions(
        Collection $products,
        Collection $baselinePredictions,
        array $apiData
    ): Collection {
        $productsById = $products->keyBy(
            fn (Product $product) => (string) $product->id
        );

        $apiPredictionsById = collect($apiData['products'] ?? [])
            ->filter(
                fn ($prediction) => is_array($prediction)
                    && isset($prediction['product_id'])
            )
            ->keyBy(
                fn (array $prediction) => (string) $prediction['product_id']
            );

        return $baselinePredictions->map(
            function (array $baseline) use (
                $productsById,
                $apiPredictionsById
            ) {
                $productId = (string) $baseline['product_id'];
                $product = $productsById->get($productId);
                $apiPrediction = $apiPredictionsById->get($productId);

                if (!$product || !$apiPrediction) {
                    return $baseline;
                }

                $allowedMethods = [
                    'no_data',
                    'moving_average',
                    'random_forest',
                ];

                $method = (string) data_get(
                    $apiPrediction,
                    'method',
                    'moving_average'
                );

                if (!in_array($method, $allowedMethods, true)) {
                    $method = 'moving_average';
                }

                $predictedQuantity = max(
                    0,
                    (int) ceil(
                        (float) data_get(
                            $apiPrediction,
                            'predicted_quantity',
                            $baseline['predicted_quantity']
                        )
                    )
                );

                $lastWeekQuantity = max(
                    0,
                    (int) data_get(
                        $apiPrediction,
                        'last_week_quantity',
                        $baseline['last_week_quantity']
                    )
                );

                $dataDays = max(
                    0,
                    (int) data_get(
                        $apiPrediction,
                        'data_days',
                        $baseline['data_days']
                    )
                );

                $salesDays = max(
                    0,
                    (int) data_get(
                        $apiPrediction,
                        'sales_days',
                        $baseline['sales_days']
                    )
                );

                $improvement = data_get(
                    $apiPrediction,
                    'improvement_percent'
                );

                $restock = $this->calculateRestock(
                    $product,
                    $predictedQuantity
                );

                $confidence = $this->predictionConfidence(
                    $method,
                    $dataDays,
                    $salesDays,
                    is_numeric($improvement)
                        ? (float) $improvement
                        : null
                );

                return array_merge($baseline, [
                    'method' => $method,
                    'method_label' => $this->methodLabel($method),
                    'data_days' => $dataDays,
                    'sales_days' => $salesDays,
                    'confidence' => $confidence['value'],
                    'confidence_label' => $confidence['label'],
                    'predicted_quantity' => $predictedQuantity,
                    'last_week_quantity' => $lastWeekQuantity,
                    'change_percent' => $this->calculateChangePercent(
                        $predictedQuantity,
                        $lastWeekQuantity
                    ),
                    'moving_average_mae' => data_get(
                        $apiPrediction,
                        'moving_average_mae'
                    ),
                    'random_forest_mae' => data_get(
                        $apiPrediction,
                        'random_forest_mae'
                    ),
                    'improvement_percent' => $improvement,
                    'recommended_restock' => $restock['quantity'],
                    'estimated_cost' => $restock['estimated_cost'],
                    'reason' => $restock['reason'],
                    'method_reason' => (string) data_get(
                        $apiPrediction,
                        'reason',
                        ''
                    ),
                ]);
            }
        );
    }

    /**
     * Baseline/fallback Weighted Moving Average untuk satu produk.
     */
    private function predictWithMovingAverage(
        Product $product,
        array $series
    ): array {
        $dailyQuantities = array_values($series);
        $dataDays = count($dailyQuantities);

        $salesDays = count(array_filter(
            $dailyQuantities,
            fn (int $quantity) => $quantity > 0
        ));

        $totalQuantity = array_sum($dailyQuantities);

        $lastWeekQuantity = (int) array_sum(
            array_slice($dailyQuantities, -7)
        );

        if ($totalQuantity <= 0) {
            return $this->noDataProductResult(
                $product,
                $dataDays
            );
        }

        $movingAverageWindow = array_slice(
            $dailyQuantities,
            -14
        );

        $dailyAverage = $this->weightedMovingAverage(
            $movingAverageWindow
        );

        $predictedQuantity = (int) ceil(
            $dailyAverage * self::FORECAST_DAYS
        );

        $restock = $this->calculateRestock(
            $product,
            $predictedQuantity
        );

        $confidence = $this->predictionConfidence(
            'moving_average',
            $dataDays,
            $salesDays,
            null
        );

        return [
            'product_id' => (int) $product->id,
            'product_name' => $product->name,
            'image_url' => $this->productImageUrl($product),
            'method' => 'moving_average',
            'method_label' => 'Moving Average',
            'data_days' => $dataDays,
            'sales_days' => $salesDays,
            'confidence' => $confidence['value'],
            'confidence_label' => $confidence['label'],
            'predicted_quantity' => $predictedQuantity,
            'last_week_quantity' => $lastWeekQuantity,
            'change_percent' => $this->calculateChangePercent(
                $predictedQuantity,
                $lastWeekQuantity
            ),
            'moving_average_mae' => null,
            'random_forest_mae' => null,
            'improvement_percent' => null,
            'current_stock' => (int) $product->stock,
            'minimum_stock' => (int) $product->minimum_stock,
            'maximum_stock' => (int) $product->maximum_stock,
            'recommended_restock' => $restock['quantity'],
            'estimated_cost' => $restock['estimated_cost'],
            'unit' => $product->unit,
            'reason' => $restock['reason'],
            'method_reason' => 'Moving Average digunakan sebagai baseline.',
        ];
    }

    private function weightedMovingAverage(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        $weightedTotal = 0;
        $totalWeight = 0;

        foreach (array_values($values) as $index => $value) {
            $weight = $index + 1;
            $weightedTotal += $value * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0
            ? $weightedTotal / $totalWeight
            : 0;
    }

    /**
     * Restok = kebutuhan forecast + safety stock, dibatasi maximum_stock.
     */
    private function calculateRestock(
        Product $product,
        int $predictedQuantity
    ): array {
        $currentStock = (int) $product->stock;
        $minimumStock = (int) $product->minimum_stock;
        $maximumStock = (int) $product->maximum_stock;

        $availableCapacity = max(
            0,
            $maximumStock - $currentStock
        );

        $requiredQuantity = max(
            0,
            $predictedQuantity + $minimumStock - $currentStock
        );

        $recommendedQuantity = min(
            $availableCapacity,
            $requiredQuantity
        );

        $estimatedCost = round(
            $recommendedQuantity
                * (float) $product->purchase_price,
            2
        );

        if ($recommendedQuantity > 0) {
            $reason = sprintf(
                'Prediksi permintaan %d %s dan stok saat ini %d %s.',
                $predictedQuantity,
                $product->unit,
                $currentStock,
                $product->unit
            );
        } else {
            $reason = 'Stok saat ini masih mencukupi prediksi permintaan.';
        }

        return [
            'quantity' => $recommendedQuantity,
            'estimated_cost' => $estimatedCost,
            'reason' => $reason,
        ];
    }

    private function noDataProductResult(
        Product $product,
        int $dataDays
    ): array {
        $currentStock = (int) $product->stock;
        $minimumStock = (int) $product->minimum_stock;
        $maximumStock = (int) $product->maximum_stock;

        $availableCapacity = max(
            0,
            $maximumStock - $currentStock
        );

        $quantityToMinimum = max(
            0,
            $minimumStock - $currentStock
        );

        $recommendedQuantity = min(
            $availableCapacity,
            $quantityToMinimum
        );

        return [
            'product_id' => (int) $product->id,
            'product_name' => $product->name,
            'image_url' => $this->productImageUrl($product),
            'method' => 'no_data',
            'method_label' => 'Belum Ada Prediksi',
            'data_days' => $dataDays,
            'sales_days' => 0,
            'confidence' => null,
            'confidence_label' => 'Belum tersedia',
            'predicted_quantity' => 0,
            'last_week_quantity' => 0,
            'change_percent' => null,
            'moving_average_mae' => null,
            'random_forest_mae' => null,
            'improvement_percent' => null,
            'current_stock' => $currentStock,
            'minimum_stock' => $minimumStock,
            'maximum_stock' => $maximumStock,
            'recommended_restock' => $recommendedQuantity,
            'estimated_cost' => round(
                $recommendedQuantity
                    * (float) $product->purchase_price,
                2
            ),
            'unit' => $product->unit,
            'reason' => 'Belum ada forecast; restok hanya mengikuti stok minimum.',
            'method_reason' => 'Produk belum memiliki riwayat penjualan.',
        ];
    }

    private function predictionConfidence(
        string $method,
        int $dataDays,
        int $salesDays,
        ?float $improvement
    ): array {
        if ($method === 'no_data') {
            return [
                'value' => null,
                'label' => 'Belum tersedia',
            ];
        }

        if ($method === 'random_forest') {
            return [
                'value' => $improvement,
                'label' => ($improvement ?? 0) >= 20
                    ? 'Tinggi'
                    : 'Sedang',
            ];
        }

        return [
            'value' => null,
            'label' => $dataDays >= 28 && $salesDays >= 7
                ? 'Sedang'
                : 'Terbatas',
        ];
    }

    private function calculateChangePercent(
        int $predictedQuantity,
        int $lastWeekQuantity
    ): ?float {
        if ($lastWeekQuantity <= 0) {
            return null;
        }

        return round(
            (($predictedQuantity - $lastWeekQuantity)
                / $lastWeekQuantity) * 100,
            1
        );
    }

    /**
     * Membuat URL publik gambar produk yang sudah difilter berdasarkan tenant.
     */
    private function productImageUrl(Product $product): ?string
    {
        if (!$product->image) {
            return null;
        }

        return asset(
            'storage/products/' . ltrim($product->image, '/')
        );
    }

    private function buildResponse(
        Collection $predictions,
        string $serviceStatus,
        ?string $serviceMessage
    ): array {
        $predictions = $predictions
            ->sortByDesc('recommended_restock')
            ->values();

        $predictedTotal = (int) $predictions->sum(
            'predicted_quantity'
        );

        $lastWeekTotal = (int) $predictions->sum(
            'last_week_quantity'
        );

        $activeMethods = $predictions
            ->pluck('method')
            ->reject(fn ($method) => $method === 'no_data')
            ->unique()
            ->values();

        if ($activeMethods->isEmpty()) {
            $mode = 'no_data';
        } elseif ($activeMethods->count() === 1) {
            $mode = (string) $activeMethods->first();
        } else {
            $mode = 'hybrid';
        }

        $counts = $predictions->countBy('method');

        return [
            'service_status' => $serviceStatus,
            'service_message' => $serviceMessage,
            'mode' => $mode,
            'mode_label' => $this->methodLabel($mode),
            'forecast_days' => self::FORECAST_DAYS,
            'percentage' => $this->calculateChangePercent(
                $predictedTotal,
                $lastWeekTotal
            ),
            'predicted_total' => $predictedTotal,
            'last_week_total' => $lastWeekTotal,
            'method_counts' => [
                'moving_average' => (int) $counts->get(
                    'moving_average',
                    0
                ),
                'random_forest' => (int) $counts->get(
                    'random_forest',
                    0
                ),
                'no_data' => (int) $counts->get(
                    'no_data',
                    0
                ),
            ],
            'products' => $predictions->all(),
            'summary' => $this->buildSummary(
                $mode,
                $predictedTotal,
                $lastWeekTotal,
                $serviceStatus
            ),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function methodLabel(string $method): string
    {
        return match ($method) {
            'moving_average' => 'Moving Average',
            'random_forest' => 'Random Forest',
            'hybrid' => 'Hybrid',
            default => 'Belum Ada Prediksi',
        };
    }

    private function buildSummary(
        string $mode,
        int $predictedTotal,
        int $lastWeekTotal,
        string $serviceStatus
    ): string {
        if ($mode === 'no_data') {
            return 'Belum ada transaksi yang dapat digunakan untuk prediksi.';
        }

        $fallbackText = $serviceStatus === 'fallback'
            ? ' Service AI sedang tidak tersedia sehingga fallback digunakan.'
            : '';

        return sprintf(
            'Prediksi %d unit untuk %d hari menggunakan %s. Penjualan 7 hari terakhir %d unit.%s',
            $predictedTotal,
            self::FORECAST_DAYS,
            $this->methodLabel($mode),
            $lastWeekTotal,
            $fallbackText
        );
    }

    private function emptyResponse(): array
    {
        return [
            'service_status' => 'no_products',
            'service_message' => 'Belum ada produk pada bisnis ini.',
            'mode' => 'no_data',
            'mode_label' => 'Belum Ada Prediksi',
            'forecast_days' => self::FORECAST_DAYS,
            'percentage' => null,
            'predicted_total' => 0,
            'last_week_total' => 0,
            'method_counts' => [
                'moving_average' => 0,
                'random_forest' => 0,
                'no_data' => 0,
            ],
            'products' => [],
            'summary' => 'Belum ada produk yang dapat diprediksi.',
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
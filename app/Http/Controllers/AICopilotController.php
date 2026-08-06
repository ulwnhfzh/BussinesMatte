<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\AICopilotService;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AICopilotController extends Controller
{
    public function index(Request $request)
    {
        $businessId = (int) Auth::user()->business_id;
        $predictionCacheKey = 'dashboard.ai-prediction.' . $businessId;

        /*
        |--------------------------------------------------------------------------
        | Perbarui insight AI
        |--------------------------------------------------------------------------
        |
        | Cache menggunakan business_id sehingga pembaruan hanya memengaruhi
        | hasil prediksi bisnis milik user yang sedang login.
        |
        */
        if ($request->boolean('refresh')) {
            Cache::forget($predictionCacheKey);

            return redirect()
                ->route('ai.copilot')
                ->with('success', 'Insight AI berhasil diperbarui.');
        }

        /*
        |--------------------------------------------------------------------------
        | Statistik bisnis hari ini
        |--------------------------------------------------------------------------
        */
        $todaySummary = Transaction::where('business_id', $businessId)
            ->whereDate('created_at', now()->toDateString())
            ->selectRaw('
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_amount), 0) as revenue
            ')
            ->first();

        $todayRevenue = (float) ($todaySummary->revenue ?? 0);
        $todayTransactionCount = (int) (
            $todaySummary->transaction_count ?? 0
        );

        $criticalStockCount = Product::where('business_id', $businessId)
            ->whereColumn('stock', '<', 'minimum_stock')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Produk terlaris dalam 30 hari terakhir
        |--------------------------------------------------------------------------
        |
        | transactions dan products sama-sama dibatasi business_id untuk menjaga
        | isolasi data tenant meskipun query menggunakan join.
        |
        */
        $bestSellingProduct = TransactionDetail::query()
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
                now()->subDays(29)->startOfDay()
            )
            ->select([
                'products.id',
                'products.name',
                'products.product_code',
            ])
            ->selectRaw(
                'SUM(transaction_details.quantity) as sold_quantity'
            )
            ->selectRaw(
                'SUM(transaction_details.subtotal) as total_revenue'
            )
            ->groupBy([
                'products.id',
                'products.name',
                'products.product_code',
            ])
            ->orderByDesc('sold_quantity')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Prediksi permintaan dari service AI
        |--------------------------------------------------------------------------
        */
        try {
            $aiPrediction = Cache::remember(
                $predictionCacheKey,
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
                'forecast_days' => 7,
                'summary' => 'Prediksi belum tersedia.',
                'generated_at' => null,
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
        $forecastDays = (int) ($aiPrediction['forecast_days'] ?? 7);
        $aiGeneratedAt = $aiPrediction['generated_at'] ?? null;

        $predictionProducts = collect($aiPrediction['products'] ?? []);

        $restockRecommendations = $predictionProducts
            ->filter(function ($product) {
                return (int) ($product['recommended_restock'] ?? 0) > 0;
            })
            ->sortByDesc(function ($product) {
                return (int) ($product['recommended_restock'] ?? 0);
            })
            ->take(4)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Susun insight yang dapat ditindaklanjuti
        |--------------------------------------------------------------------------
        */
        $suggestions = $restockRecommendations
            ->map(function ($product) use ($forecastDays) {
                $productId = (int) ($product['product_id'] ?? 0);
                $unit = $product['unit'] ?? 'unit';
                $recommendedRestock = (int) (
                    $product['recommended_restock'] ?? 0
                );
                $predictedQuantity = (int) (
                    $product['predicted_quantity'] ?? 0
                );

                return [
                    'key' => 'restock-' . $productId,
                    'type' => 'restock',
                    'title' => 'Restok ' . ($product['product_name'] ?? 'Produk'),
                    'message' => $product['reason']
                        ?? 'Produk memerlukan penambahan stok.',
                    'meta' => 'Prediksi ' . $forecastDays . ' hari: '
                        . number_format($predictedQuantity, 0, ',', '.')
                        . ' ' . $unit . ' · '
                        . ($product['method_label'] ?? 'Belum Ada Prediksi'),
                    'badge' => '+' . number_format(
                        $recommendedRestock,
                        0,
                        ',',
                        '.'
                    ) . ' ' . $unit,
                    'estimated_cost' => (float) (
                        $product['estimated_cost'] ?? 0
                    ),
                    'action' => 'Lihat Produk',
                    'action_url' => route('inventory.detail', $productId),
                ];
            });

        $recommendedProductIds = $restockRecommendations
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $criticalProducts = Product::where('business_id', $businessId)
            ->whereColumn('stock', '<', 'minimum_stock')
            ->orderByRaw('(minimum_stock - stock) DESC')
            ->limit(8)
            ->get([
                'id',
                'name',
                'stock',
                'minimum_stock',
                'unit',
            ])
            ->reject(function ($product) use ($recommendedProductIds) {
                return in_array(
                    (int) $product->id,
                    $recommendedProductIds,
                    true
                );
            });

        foreach ($criticalProducts as $product) {
            if ($suggestions->count() >= 4) {
                break;
            }

            $stockShortage = max(
                (int) $product->minimum_stock - (int) $product->stock,
                0
            );

            $suggestions->push([
                'key' => 'critical-' . $product->id,
                'type' => 'critical',
                'title' => 'Stok Kritis: ' . $product->name,
                'message' => 'Stok saat ini ' . $product->stock . ' '
                    . $product->unit . ', berada di bawah batas minimum '
                    . $product->minimum_stock . ' ' . $product->unit . '.',
                'meta' => 'Kekurangan ' . $stockShortage . ' '
                    . $product->unit . ' dari batas minimum.',
                'badge' => 'Kritis',
                'estimated_cost' => null,
                'action' => 'Lihat Produk',
                'action_url' => route('inventory.detail', $product->id),
            ]);
        }

        if ($suggestions->count() < 4 && $bestSellingProduct) {
            $suggestions->push([
                'key' => 'best-selling-' . $bestSellingProduct->id,
                'type' => 'performance',
                'title' => 'Produk Terlaris 30 Hari',
                'message' => $bestSellingProduct->name . ' terjual '
                    . number_format(
                        (int) $bestSellingProduct->sold_quantity,
                        0,
                        ',',
                        '.'
                    ) . ' unit dengan pendapatan Rp '
                    . number_format(
                        (float) $bestSellingProduct->total_revenue,
                        0,
                        ',',
                        '.'
                    ) . '.',
                'meta' => 'Dihitung dari transaksi POS 30 hari terakhir.',
                'badge' => number_format(
                    (int) $bestSellingProduct->sold_quantity,
                    0,
                    ',',
                    '.'
                ) . ' terjual',
                'estimated_cost' => null,
                'action' => 'Lihat Produk',
                'action_url' => route(
                    'inventory.detail',
                    $bestSellingProduct->id
                ),
            ]);
        }

        $suggestions = $suggestions
            ->unique('key')
            ->take(4)
            ->values();

        $primarySuggestion = $suggestions->first();

        return view('ai-copilot.index', compact(
            'todayRevenue',
            'todayTransactionCount',
            'criticalStockCount',
            'bestSellingProduct',
            'aiServiceStatus',
            'aiServiceMessage',
            'aiModeLabel',
            'aiSummary',
            'aiGeneratedAt',
            'forecastDays',
            'suggestions',
            'primarySuggestion'
        ));
    }

    /**
     * Menjawab pertanyaan chatbot menggunakan data bisnis tenant aktif.
     */
    public function chat(
        Request $request,
        AICopilotService $aiCopilotService
    ) {
        $validated = $request->validate([
            'question' => [
                'required',
                'string',
                'min:2',
                'max:500',
            ],
        ], [
            'question.required' => 'Pertanyaan tidak boleh kosong.',
            'question.min' => 'Pertanyaan minimal terdiri dari 2 karakter.',
            'question.max' => 'Pertanyaan maksimal terdiri dari 500 karakter.',
        ]);

        try {
            $result = $aiCopilotService->answer(
                (int) Auth::user()->business_id,
                trim($validated['question'])
            );

            return response()->json([
                'status' => 'success',
                'type' => $result['type'],
                'answer' => $result['answer'],
                'suggestions' => $result['suggestions'] ?? [],
                'action' => $result['action'] ?? null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Pertanyaan belum dapat diproses. Silakan coba kembali.',
            ], 500);
        }
    }
}
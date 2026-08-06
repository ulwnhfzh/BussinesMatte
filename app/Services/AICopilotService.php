<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AICopilotService
{
    /**
     * Menjawab pertanyaan hanya menggunakan data milik business_id aktif.
     */
    public function answer(int $businessId, string $question): array
    {
        $normalizedQuestion = Str::of($question)
            ->lower()
            ->squish()
            ->toString();

        $geminiAttempted = false;

        /*
         * Pertanyaan konsultatif diprioritaskan ke Gemini agar pengguna
         * dapat bertanya dengan bahasa yang lebih bebas. Jika Gemini gagal,
         * proses dilanjutkan ke aturan lokal di bawahnya.
         */
        if ($this->shouldUseGeminiFirst($normalizedQuestion)) {
            $geminiAttempted = true;

            try {
                return $this->geminiAnswer(
                    $businessId,
                    $question
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $ruleAnswer = $this->ruleBasedAnswer(
            $businessId,
            $normalizedQuestion
        );

        if ($ruleAnswer !== null) {
            return $ruleAnswer;
        }

        /*
         * Pertanyaan yang tidak dikenali aturan lokal akan dicoba ke Gemini.
         * Pemanggilan tidak diulang jika sebelumnya sudah gagal.
         */
        if (!$geminiAttempted && $this->geminiIsAvailable()) {
            try {
                return $this->geminiAnswer(
                    $businessId,
                    $question
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $this->helpAnswer();
    }

    /**
     * Menangani pertanyaan faktual menggunakan fondasi aturan lama.
     */
    private function ruleBasedAnswer(
        int $businessId,
        string $normalizedQuestion
    ): ?array {

        if ($this->isGreeting($normalizedQuestion)) {
            return $this->greetingAnswer($businessId);
        }

        if (Str::contains($normalizedQuestion, [
            'metode ai',
            'mode ai',
            'algoritma',
            'random forest',
            'moving average',
        ])) {
            return $this->predictionMethodAnswer($businessId);
        }

        $mentionedProduct = $this->findMentionedProduct(
            $businessId,
            $normalizedQuestion
        );

        if (
            $mentionedProduct
            && Str::contains($normalizedQuestion, [
                'stok',
                'harga',
                'detail',
                'kondisi',
            ])
        ) {
            return $this->productAnswer($mentionedProduct);
        }

        if (Str::contains($normalizedQuestion, [
            'restok',
            'stok ulang',
            'rekomendasi stok',
            'rekomendasi produk',
            'permintaan minggu depan',
            'prediksi permintaan',
            'perlu dibeli',
        ])) {
            return $this->restockAnswer($businessId);
        }

        if (Str::contains($normalizedQuestion, [
            'stok kritis',
            'stok menipis',
            'hampir habis',
            'stok kurang',
            'di bawah minimum',
        ])) {
            return $this->criticalStockAnswer($businessId);
        }

        if (Str::contains($normalizedQuestion, [
            'produk terlaris',
            'paling laku',
            'produk laku',
            'produk unggulan',
        ])) {
            return $this->bestSellingAnswer($businessId);
        }

        if (Str::contains($normalizedQuestion, [
            'pendapatan',
            'omzet',
            'laba',
            'profit',
            'keuntungan',
        ])) {
            return $this->financialAnswer(
                $businessId,
                $normalizedQuestion
            );
        }

        if (Str::contains($normalizedQuestion, [
            'ringkasan inventory',
            'ringkasan inventori',
            'kondisi inventory',
            'kondisi inventori',
            'jumlah produk',
            'semua stok',
        ])) {
            return $this->inventorySummaryAnswer($businessId);
        }

        return null;
    }

    private function isGreeting(string $question): bool
    {
        return Str::startsWith($question, [
            'halo',
            'hai',
            'hello',
            'selamat pagi',
            'selamat siang',
            'selamat sore',
            'selamat malam',
        ]);
    }

    private function greetingAnswer(int $businessId): array
    {
        $totalProducts = Product::where('business_id', $businessId)
            ->count();

        $criticalProducts = Product::where('business_id', $businessId)
            ->whereColumn('stock', '<', 'minimum_stock')
            ->count();

        return $this->response(
            'greeting',
            'Halo! Saya siap membantu membaca data bisnis Anda. Saat ini '
                . 'terdapat ' . number_format($totalProducts, 0, ',', '.')
                . ' produk dan ' . number_format(
                    $criticalProducts,
                    0,
                    ',',
                    '.'
                ) . ' produk berstatus stok kritis.',
            $this->defaultSuggestions()
        );
    }

    private function findMentionedProduct(
        int $businessId,
        string $question
    ): ?Product {
        return Product::where('business_id', $businessId)
            ->get([
                'id',
                'product_code',
                'name',
                'stock',
                'minimum_stock',
                'maximum_stock',
                'purchase_price',
                'selling_price',
                'unit',
            ])
            ->sortByDesc(fn ($product) => mb_strlen($product->name))
            ->first(function ($product) use ($question) {
                return Str::contains(
                    $question,
                    Str::lower($product->name)
                );
            });
    }

    private function productAnswer(Product $product): array
    {
        if ((int) $product->stock < (int) $product->minimum_stock) {
            $stockStatus = 'Kritis';
        } elseif ((int) $product->stock > (int) $product->maximum_stock) {
            $stockStatus = 'Melebihi maksimum';
        } else {
            $stockStatus = 'Optimal';
        }

        $answer = $product->name . ' (' . $product->product_code . ")\n"
            . '• Stok: ' . number_format(
                (int) $product->stock,
                0,
                ',',
                '.'
            ) . ' ' . $product->unit . "\n"
            . '• Batas minimum: ' . number_format(
                (int) $product->minimum_stock,
                0,
                ',',
                '.'
            ) . ' ' . $product->unit . "\n"
            . '• Batas maksimum: ' . number_format(
                (int) $product->maximum_stock,
                0,
                ',',
                '.'
            ) . ' ' . $product->unit . "\n"
            . '• Harga jual: Rp ' . number_format(
                (float) $product->selling_price,
                0,
                ',',
                '.'
            ) . "\n"
            . '• Status: ' . $stockStatus;

        return $this->response(
            'product',
            $answer,
            [
                'Produk apa yang stoknya kritis?',
                'Berikan rekomendasi restok',
                'Tampilkan produk terlaris',
            ],
            [
                'label' => 'Lihat Detail Produk',
                'url' => route('inventory.detail', $product->id),
            ]
        );
    }

    private function criticalStockAnswer(int $businessId): array
    {
        $products = Product::where('business_id', $businessId)
            ->whereColumn('stock', '<', 'minimum_stock')
            ->orderByRaw('(minimum_stock - stock) DESC')
            ->limit(5)
            ->get([
                'id',
                'name',
                'stock',
                'minimum_stock',
                'unit',
            ]);

        if ($products->isEmpty()) {
            return $this->response(
                'critical_stock',
                'Tidak ada produk dengan stok di bawah batas minimum. '
                    . 'Kondisi stok kritis saat ini aman.',
                [
                    'Berikan rekomendasi restok',
                    'Tampilkan produk terlaris',
                    'Berapa pendapatan hari ini?',
                ],
                [
                    'label' => 'Buka Inventory',
                    'url' => route('inventory'),
                ]
            );
        }

        $lines = $products->map(function ($product, $index) {
            $shortage = (int) $product->minimum_stock
                - (int) $product->stock;

            return ($index + 1) . '. ' . $product->name . ': '
                . $product->stock . ' ' . $product->unit
                . ' (kurang ' . $shortage . ' ' . $product->unit
                . ' dari batas minimum)';
        });

        return $this->response(
            'critical_stock',
            'Produk dengan stok paling kritis:' . "\n"
                . $lines->implode("\n"),
            [
                'Berikan rekomendasi restok',
                'Ringkas kondisi inventory',
                'Tampilkan produk terlaris',
            ],
            [
                'label' => 'Buka Inventory',
                'url' => route('inventory', ['status' => 'kritis']),
            ]
        );
    }

    private function restockAnswer(int $businessId): array
    {
        try {
            $prediction = Cache::remember(
                'dashboard.ai-prediction.' . $businessId,
                now()->addMinutes(3),
                function () {
                    return (new PredictionService())->getPrediction();
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->response(
                'restock',
                'Service prediksi sedang tidak dapat dihubungi. Silakan '
                    . 'coba kembali atau periksa produk dengan stok kritis.',
                [
                    'Produk apa yang stoknya kritis?',
                    'Ringkas kondisi inventory',
                ],
                [
                    'label' => 'Buka Inventory',
                    'url' => route('inventory'),
                ]
            );
        }

        $recommendations = collect($prediction['products'] ?? [])
            ->filter(function ($product) {
                return (int) ($product['recommended_restock'] ?? 0) > 0;
            })
            ->sortByDesc('recommended_restock')
            ->take(5)
            ->values();

        if ($recommendations->isEmpty()) {
            return $this->response(
                'restock',
                ($prediction['summary'] ?? 'Prediksi belum tersedia.')
                    . ' Saat ini belum ada produk yang direkomendasikan '
                    . 'untuk direstok.',
                [
                    'Produk apa yang stoknya kritis?',
                    'Tampilkan produk terlaris',
                    'Metode AI apa yang digunakan?',
                ],
                [
                    'label' => 'Buka Inventory',
                    'url' => route('inventory'),
                ]
            );
        }

        $lines = $recommendations->map(function ($product, $index) {
            $unit = $product['unit'] ?? 'unit';

            return ($index + 1) . '. '
                . ($product['product_name'] ?? 'Produk') . ': tambah '
                . number_format(
                    (int) ($product['recommended_restock'] ?? 0),
                    0,
                    ',',
                    '.'
                ) . ' ' . $unit . ' (estimasi biaya Rp '
                . number_format(
                    (float) ($product['estimated_cost'] ?? 0),
                    0,
                    ',',
                    '.'
                ) . ')';
        });

        $firstRecommendation = $recommendations->first();

        return $this->response(
            'restock',
            'Rekomendasi restok menggunakan '
                . ($prediction['mode_label'] ?? 'metode prediksi tersedia')
                . ':' . "\n" . $lines->implode("\n"),
            [
                'Produk apa yang stoknya kritis?',
                'Metode AI apa yang digunakan?',
                'Ringkas kondisi inventory',
            ],
            [
                'label' => 'Lihat Rekomendasi Utama',
                'url' => route(
                    'inventory.detail',
                    (int) $firstRecommendation['product_id']
                ),
            ]
        );
    }

    private function bestSellingAnswer(int $businessId): array
    {
        $products = TransactionDetail::query()
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
            ])
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return $this->response(
                'best_selling',
                'Belum ada transaksi dalam 30 hari terakhir sehingga produk '
                    . 'terlaris belum dapat ditentukan.',
                $this->defaultSuggestions()
            );
        }

        $lines = $products->map(function ($product, $index) {
            return ($index + 1) . '. ' . $product->name . ': '
                . number_format(
                    (int) $product->sold_quantity,
                    0,
                    ',',
                    '.'
                ) . ' unit · Rp '
                . number_format(
                    (float) $product->total_revenue,
                    0,
                    ',',
                    '.'
                );
        });

        return $this->response(
            'best_selling',
            'Produk terlaris dalam 30 hari terakhir:' . "\n"
                . $lines->implode("\n"),
            [
                'Berikan rekomendasi restok',
                'Berapa pendapatan bulan ini?',
                'Ringkas kondisi inventory',
            ],
            [
                'label' => 'Lihat Produk Terlaris',
                'url' => route('inventory.detail', $products->first()->id),
            ]
        );
    }

    private function financialAnswer(
        int $businessId,
        string $question
    ): array {
        [$startDate, $endDate, $periodLabel] = $this->resolvePeriod(
            $question
        );

        $summary = Transaction::where('business_id', $businessId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_amount), 0) as revenue,
                COALESCE(SUM(total_profit), 0) as profit,
                COALESCE(AVG(total_amount), 0) as average_transaction
            ')
            ->first();

        $answer = 'Ringkasan keuangan ' . $periodLabel . ':' . "\n"
            . '• Pendapatan: Rp ' . number_format(
                (float) ($summary->revenue ?? 0),
                0,
                ',',
                '.'
            ) . "\n"
            . '• Laba: Rp ' . number_format(
                (float) ($summary->profit ?? 0),
                0,
                ',',
                '.'
            ) . "\n"
            . '• Transaksi: ' . number_format(
                (int) ($summary->transaction_count ?? 0),
                0,
                ',',
                '.'
            ) . "\n"
            . '• Rata-rata transaksi: Rp ' . number_format(
                (float) ($summary->average_transaction ?? 0),
                0,
                ',',
                '.'
            );

        return $this->response(
            'financial',
            $answer,
            [
                'Berapa pendapatan hari ini?',
                'Berapa laba bulan ini?',
                'Tampilkan produk terlaris',
            ],
            [
                'label' => 'Buka Analytics',
                'url' => route('analytics'),
            ]
        );
    }

    private function resolvePeriod(string $question): array
    {
        if (Str::contains($question, ['kemarin'])) {
            $date = now()->subDay();

            return [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
                'kemarin',
            ];
        }

        if (Str::contains($question, [
            'minggu ini',
            'pekan ini',
            '7 hari',
        ])) {
            return [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
                '7 hari terakhir',
            ];
        }

        if (Str::contains($question, [
            'bulan ini',
            '30 hari',
        ])) {
            return [
                now()->subDays(29)->startOfDay(),
                now()->endOfDay(),
                '30 hari terakhir',
            ];
        }

        return [
            now()->startOfDay(),
            now()->endOfDay(),
            'hari ini',
        ];
    }

    private function inventorySummaryAnswer(int $businessId): array
    {
        $summary = Product::where('business_id', $businessId)
            ->selectRaw('
                COUNT(*) as total_products,
                COALESCE(SUM(CASE
                    WHEN stock < minimum_stock THEN 1 ELSE 0
                END), 0) as critical_products,
                COALESCE(SUM(CASE
                    WHEN stock > maximum_stock THEN 1 ELSE 0
                END), 0) as excess_products,
                COALESCE(SUM(CASE
                    WHEN stock >= minimum_stock
                        AND stock <= maximum_stock THEN 1 ELSE 0
                END), 0) as optimal_products,
                COALESCE(SUM(stock * purchase_price), 0) as inventory_value
            ')
            ->first();

        $answer = 'Ringkasan kondisi inventory:' . "\n"
            . '• Total produk: ' . number_format(
                (int) ($summary->total_products ?? 0),
                0,
                ',',
                '.'
            ) . "\n"
            . '• Stok optimal: ' . number_format(
                (int) ($summary->optimal_products ?? 0),
                0,
                ',',
                '.'
            ) . "\n"
            . '• Stok kritis: ' . number_format(
                (int) ($summary->critical_products ?? 0),
                0,
                ',',
                '.'
            ) . "\n"
            . '• Stok berlebih: ' . number_format(
                (int) ($summary->excess_products ?? 0),
                0,
                ',',
                '.'
            ) . "\n"
            . '• Nilai persediaan: Rp ' . number_format(
                (float) ($summary->inventory_value ?? 0),
                0,
                ',',
                '.'
            );

        return $this->response(
            'inventory_summary',
            $answer,
            [
                'Produk apa yang stoknya kritis?',
                'Berikan rekomendasi restok',
                'Tampilkan produk terlaris',
            ],
            [
                'label' => 'Buka Inventory',
                'url' => route('inventory'),
            ]
        );
    }

    private function predictionMethodAnswer(int $businessId): array
    {
        try {
            $prediction = Cache::remember(
                'dashboard.ai-prediction.' . $businessId,
                now()->addMinutes(3),
                function () {
                    return (new PredictionService())->getPrediction();
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->response(
                'prediction_method',
                'Service prediksi sedang tidak dapat dihubungi. Saat service '
                    . 'tersedia kembali, metode akan dipilih otomatis.',
                $this->defaultSuggestions()
            );
        }

        $counts = $prediction['method_counts'] ?? [];

        $answer = 'Mode prediksi saat ini: '
            . ($prediction['mode_label'] ?? 'Belum Ada Prediksi') . ".\n"
            . '• Moving Average: ' . (int) (
                $counts['moving_average'] ?? 0
            ) . " produk\n"
            . '• Random Forest: ' . (int) (
                $counts['random_forest'] ?? 0
            ) . " produk\n"
            . '• Belum cukup data: ' . (int) (
                $counts['no_data'] ?? 0
            ) . " produk\n\n"
            . 'Sistem memilih metode secara otomatis berdasarkan jumlah '
            . 'riwayat penjualan dan hasil pengujian akurasi.';

        return $this->response(
            'prediction_method',
            $answer,
            [
                'Berikan rekomendasi restok',
                'Produk apa yang stoknya kritis?',
                'Ringkas kondisi inventory',
            ]
        );
    }

    /**
     * Pertanyaan konsultatif membutuhkan penalaran yang lebih fleksibel.
     */
    private function shouldUseGeminiFirst(string $question): bool
    {
        if (!$this->geminiIsAvailable()) {
            return false;
        }

        return Str::contains($question, [
            'strategi',
            'cara menjual',
            'cara meningkatkan',
            'agar laku',
            'supaya laku',
            'promosi',
            'diskon',
            'target penjualan',
            'rencana penjualan',
            'saran bisnis',
            'optimalisasi',
            'potensi produk',
            'waktu terbaik',
            'kapan sebaiknya',
        ]);
    }

    /**
     * Gemini hanya dianggap tersedia ketika seluruh konfigurasi wajib ada.
     */
    private function geminiIsAvailable(): bool
    {
        return (bool) config('ai_copilot.llm_enabled', false)
            && config('ai_copilot.provider') === 'gemini'
            && filled(config('ai_copilot.gemini.api_key'));
    }

    /**
     * Meminta jawaban fleksibel kepada Gemini menggunakan ringkasan data
     * tenant aktif. API key dikirim melalui header agar tidak masuk URL.
     */
    private function geminiAnswer(
        int $businessId,
        string $question
    ): array {
        if (!$this->geminiIsAvailable()) {
            throw new RuntimeException(
                'Konfigurasi Gemini AI Copilot belum lengkap.'
            );
        }

        $apiKey = (string) config('ai_copilot.gemini.api_key');
        $baseUrl = rtrim(
            (string) config('ai_copilot.gemini.base_url'),
            '/'
        );
        $model = (string) config('ai_copilot.gemini.model');
        $timeout = max(
            5,
            (int) config('ai_copilot.gemini.timeout', 30)
        );
        $maxOutputTokens = max(
            128,
            (int) config(
                'ai_copilot.gemini.max_output_tokens',
                900
            )
        );
        $temperature = min(
            1,
            max(
                0,
                (float) config(
                    'ai_copilot.gemini.temperature',
                    0.35
                )
            )
        );

        $context = $this->buildBusinessContext($businessId);
        $contextJson = json_encode(
            $context,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_PRETTY_PRINT
        );

        if ($contextJson === false) {
            throw new RuntimeException(
                'Konteks bisnis gagal disiapkan.'
            );
        }

        $url = $baseUrl . '/models/'
            . rawurlencode($model)
            . ':generateContent';

        $response = Http::acceptJson()
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->timeout($timeout)
            ->post($url, [
                'systemInstruction' => [
                    'parts' => [
                        [
                            'text' => $this->geminiSystemInstruction(),
                        ],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => "DATA BISNIS AKTIF:\n"
                                    . $contextJson
                                    . "\n\nPERTANYAAN PENGGUNA:\n"
                                    . $question,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxOutputTokens,
                ],
            ]);

        if ($response->failed()) {
            $apiMessage = (string) data_get(
                $response->json(),
                'error.message',
                'Tidak ada detail error.'
            );

            throw new RuntimeException(
                'Gemini API gagal (HTTP '
                    . $response->status()
                    . '): '
                    . Str::limit($apiMessage, 300)
            );
        }

        $answer = collect(
            data_get(
                $response->json(),
                'candidates.0.content.parts',
                []
            )
        )
            ->pluck('text')
            ->filter()
            ->implode("\n");

        if (blank($answer)) {
            throw new RuntimeException(
                'Gemini tidak mengembalikan teks jawaban.'
            );
        }

        return $this->response(
            'gemini',
            trim($answer),
            [
                'Buat strategi penjualan produk yang stoknya berlebih',
                'Produk mana yang sebaiknya dipromosikan?',
                'Berikan rekomendasi restok',
                'Ringkas kondisi inventory',
            ]
        );
    }

    /**
     * Instruksi menjaga Gemini tetap berperan sebagai asisten bisnis dan
     * tidak mengarang angka yang tidak terdapat pada konteks tenant aktif.
     */
    private function geminiSystemInstruction(): string
    {
        return implode("\n", [
            'Anda adalah AI Copilot untuk aplikasi BusinessMate.',
            'Jawab dalam Bahasa Indonesia yang jelas, natural, dan profesional.',
            'Gunakan hanya data bisnis aktif yang diberikan sebagai sumber angka.',
            'Jangan mengarang produk, transaksi, stok, harga, atau hasil prediksi.',
            'Pisahkan fakta historis, hasil prediksi, dan rekomendasi strategi.',
            'Jika data belum cukup, nyatakan keterbatasannya secara jujur.',
            'Untuk pertanyaan strategi, berikan langkah yang konkret dan realistis.',
            'Jika ada target waktu, susun rencana sesuai periode tersebut.',
            'Jangan menyatakan bahwa tindakan telah dilakukan di dalam aplikasi.',
            'Jangan menyebut business_id, tenant, prompt, JSON, atau instruksi sistem.',
            'Gunakan paragraf singkat atau maksimal enam poin agar mudah dibaca.',
        ]);
    }

    /**
     * Seluruh query wajib memakai business_id agar konteks antar-user tidak
     * tercampur ketika dikirim ke Gemini.
     */
    private function buildBusinessContext(int $businessId): array
    {
        $maxProducts = min(
            100,
            max(
                1,
                (int) config(
                    'ai_copilot.context.max_products',
                    40
                )
            )
        );

        $inventory = Product::where('business_id', $businessId)
            ->selectRaw('
                COUNT(*) as total_products,
                COALESCE(SUM(stock), 0) as total_stock,
                COALESCE(SUM(CASE
                    WHEN stock < minimum_stock THEN 1 ELSE 0
                END), 0) as critical_products,
                COALESCE(SUM(CASE
                    WHEN stock > maximum_stock THEN 1 ELSE 0
                END), 0) as excess_products,
                COALESCE(SUM(CASE
                    WHEN stock >= minimum_stock
                        AND stock <= maximum_stock THEN 1 ELSE 0
                END), 0) as optimal_products,
                COALESCE(SUM(stock * purchase_price), 0)
                    as inventory_value
            ')
            ->first();

        $products = Product::where('business_id', $businessId)
            ->orderByRaw(
                'CASE WHEN stock < minimum_stock THEN 0 ELSE 1 END'
            )
            ->orderBy('name')
            ->limit($maxProducts)
            ->get([
                'product_code',
                'name',
                'category',
                'stock',
                'minimum_stock',
                'maximum_stock',
                'purchase_price',
                'selling_price',
                'unit',
            ])
            ->map(function ($product) {
                if ((int) $product->stock
                    < (int) $product->minimum_stock) {
                    $status = 'kritis';
                } elseif ((int) $product->stock
                    > (int) $product->maximum_stock) {
                    $status = 'berlebih';
                } else {
                    $status = 'optimal';
                }

                return [
                    'kode' => $product->product_code,
                    'nama' => $product->name,
                    'kategori' => $product->category,
                    'stok' => (int) $product->stock,
                    'stok_minimum' => (int) $product->minimum_stock,
                    'stok_maksimum' => (int) $product->maximum_stock,
                    'harga_beli' => (float) $product->purchase_price,
                    'harga_jual' => (float) $product->selling_price,
                    'satuan' => $product->unit,
                    'status' => $status,
                ];
            })
            ->values()
            ->all();

        $maxTopProducts = max(
            1,
            (int) config(
                'ai_copilot.context.max_top_products',
                10
            )
        );

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
                now()->subDays(29)->startOfDay()
            )
            ->select([
                'products.product_code',
                'products.name',
            ])
            ->selectRaw(
                'SUM(transaction_details.quantity) as sold_quantity'
            )
            ->selectRaw(
                'SUM(transaction_details.subtotal) as revenue'
            )
            ->groupBy([
                'products.product_code',
                'products.name',
            ])
            ->orderByDesc('sold_quantity')
            ->limit($maxTopProducts)
            ->get()
            ->map(fn ($product) => [
                'kode' => $product->product_code,
                'nama' => $product->name,
                'jumlah_terjual' => (int) $product->sold_quantity,
                'pendapatan' => (float) $product->revenue,
            ])
            ->values()
            ->all();

        return [
            'waktu_konteks' => now()
                ->timezone('Asia/Jakarta')
                ->format('Y-m-d H:i:s T'),
            'ringkasan_inventory' => [
                'total_produk' => (int) (
                    $inventory->total_products ?? 0
                ),
                'total_unit_stok' => (int) (
                    $inventory->total_stock ?? 0
                ),
                'produk_optimal' => (int) (
                    $inventory->optimal_products ?? 0
                ),
                'produk_kritis' => (int) (
                    $inventory->critical_products ?? 0
                ),
                'produk_berlebih' => (int) (
                    $inventory->excess_products ?? 0
                ),
                'nilai_persediaan' => (float) (
                    $inventory->inventory_value ?? 0
                ),
            ],
            'keuangan' => [
                'hari_ini' => $this->financialContext(
                    $businessId,
                    1
                ),
                '7_hari_terakhir' => $this->financialContext(
                    $businessId,
                    7
                ),
                '30_hari_terakhir' => $this->financialContext(
                    $businessId,
                    30
                ),
            ],
            'produk' => $products,
            'produk_terlaris_30_hari' => $topProducts,
            'prediksi' => $this->predictionContext($businessId),
        ];
    }

    private function financialContext(
        int $businessId,
        int $days
    ): array {
        $summary = Transaction::where('business_id', $businessId)
            ->whereBetween('created_at', [
                now()->subDays($days - 1)->startOfDay(),
                now()->endOfDay(),
            ])
            ->selectRaw('
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_amount), 0) as revenue,
                COALESCE(SUM(total_profit), 0) as profit,
                COALESCE(AVG(total_amount), 0) as average_transaction
            ')
            ->first();

        return [
            'jumlah_transaksi' => (int) (
                $summary->transaction_count ?? 0
            ),
            'pendapatan' => (float) ($summary->revenue ?? 0),
            'laba' => (float) ($summary->profit ?? 0),
            'rata_rata_transaksi' => (float) (
                $summary->average_transaction ?? 0
            ),
        ];
    }

    /**
     * Prediksi tetap dihitung oleh Moving Average/Random Forest yang sudah
     * ada. Gemini hanya menjelaskan atau menyusun strateginya.
     */
    private function predictionContext(int $businessId): array
    {
        try {
            $prediction = Cache::remember(
                'dashboard.ai-prediction.' . $businessId,
                now()->addMinutes(3),
                function () {
                    return (new PredictionService())->getPrediction();
                }
            );

            $maxRestockProducts = max(
                1,
                (int) config(
                    'ai_copilot.context.max_restock_products',
                    10
                )
            );

            $recommendations = collect(
                $prediction['products'] ?? []
            )
                ->filter(fn ($product) => (int) (
                    $product['recommended_restock'] ?? 0
                ) > 0)
                ->sortByDesc('recommended_restock')
                ->take($maxRestockProducts)
                ->map(fn ($product) => [
                    'nama_produk' => $product['product_name'] ?? null,
                    'metode' => $product['method_label'] ?? null,
                    'prediksi_permintaan' => (int) (
                        $product['predicted_quantity'] ?? 0
                    ),
                    'rekomendasi_restock' => (int) (
                        $product['recommended_restock'] ?? 0
                    ),
                    'estimasi_biaya' => (float) (
                        $product['estimated_cost'] ?? 0
                    ),
                    'satuan' => $product['unit'] ?? 'unit',
                    'alasan' => $product['reason'] ?? null,
                ])
                ->values()
                ->all();

            return [
                'status_service' => $prediction['service_status']
                    ?? 'unknown',
                'mode' => $prediction['mode_label']
                    ?? 'Belum Ada Prediksi',
                'periode_hari' => (int) (
                    $prediction['forecast_days'] ?? 7
                ),
                'total_prediksi' => (int) (
                    $prediction['predicted_total'] ?? 0
                ),
                'ringkasan' => $prediction['summary'] ?? null,
                'rekomendasi_restock' => $recommendations,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'status_service' => 'unavailable',
                'ringkasan' => 'Service prediksi sedang tidak tersedia.',
                'rekomendasi_restock' => [],
            ];
        }
    }

    private function helpAnswer(): array
    {
        return $this->response(
            'help',
            "Saya belum memahami pertanyaan tersebut. Anda dapat menanyakan:\n"
                . "• Produk dengan stok kritis\n"
                . "• Rekomendasi restok minggu depan\n"
                . "• Pendapatan atau laba berdasarkan periode\n"
                . "• Produk terlaris selama 30 hari\n"
                . "• Kondisi dan harga produk tertentu\n"
                . '• Metode prediksi AI yang sedang digunakan',
            $this->defaultSuggestions()
        );
    }

    private function defaultSuggestions(): array
    {
        return [
            'Produk apa yang stoknya kritis?',
            'Berikan rekomendasi restok',
            'Berapa pendapatan hari ini?',
            'Tampilkan produk terlaris',
        ];
    }

    private function response(
        string $type,
        string $answer,
        array $suggestions = [],
        ?array $action = null
    ): array {
        return [
            'type' => $type,
            'answer' => $answer,
            'suggestions' => $suggestions,
            'action' => $action,
        ];
    }
}
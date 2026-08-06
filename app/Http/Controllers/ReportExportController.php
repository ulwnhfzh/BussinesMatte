<?php

namespace App\Http\Controllers;

use App\Exports\AIPredictionReportExport;
use App\Exports\AnalyticsReportExport;
use App\Exports\CompleteReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\SalesReportExport;
use App\Exports\StockMovementReportExport;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Services\PredictionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use ZipArchive;

class ReportExportController extends Controller
{
    /**
     * Memproses seluruh jenis ekspor dari halaman Pengaturan.
     */
    public function export(Request $request)
    {
        $historicalReports = [
            'stock_movements',
            'sales',
            'analytics',
            'complete',
        ];

        $isHistoricalReport = in_array(
            (string) $request->input('report_type'),
            $historicalReports,
            true
        );

        $isCustomPeriod = $isHistoricalReport
            && $request->input('period') === 'custom';

        $validated = $request->validateWithBag(
            'reportExport',
            [
                'report_type' => [
                    'required',
                    Rule::in([
                        'inventory',
                        'stock_movements',
                        'sales',
                        'analytics',
                        'ai_prediction',
                        'complete',
                    ]),
                ],

                'format' => [
                    'required',
                    Rule::in([
                        'pdf',
                        'xlsx',
                        'csv',
                    ]),
                ],

                'period' => [
                    Rule::requiredIf($isHistoricalReport),
                    'nullable',
                    Rule::in([
                        'today',
                        '7_days',
                        '30_days',
                        'custom',
                    ]),
                ],

                'start_date' => [
                    Rule::requiredIf($isCustomPeriod),
                    'nullable',
                    'date_format:Y-m-d',
                    'before_or_equal:today',
                ],

                'end_date' => [
                    Rule::requiredIf($isCustomPeriod),
                    'nullable',
                    'date_format:Y-m-d',
                    'after_or_equal:start_date',
                    'before_or_equal:today',
                ],
            ],
            [
                'report_type.required' => 'Jenis laporan harus dipilih.',
                'report_type.in' => 'Jenis laporan tidak tersedia.',

                'format.required' => 'Format laporan harus dipilih.',
                'format.in' => 'Format laporan tidak tersedia.',

                'period.required' => 'Periode laporan harus dipilih.',
                'period.in' => 'Periode laporan tidak tersedia.',

                'start_date.required' => 'Tanggal mulai harus diisi.',
                'start_date.date_format' => 'Format tanggal mulai tidak valid.',
                'start_date.before_or_equal' => 'Tanggal mulai tidak boleh melewati hari ini.',

                'end_date.required' => 'Tanggal selesai harus diisi.',
                'end_date.date_format' => 'Format tanggal selesai tidak valid.',
                'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
                'end_date.before_or_equal' => 'Tanggal selesai tidak boleh melewati hari ini.',
            ]
        );

        $user = Auth::user();
        $businessId = (int) $user->business_id;
        $businessName = $this->resolveBusinessName($user);

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange(
            $validated['period'] ?? '30_days',
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return match ($validated['report_type']) {
            'inventory' => $this->exportInventory(
                $businessId,
                $businessName,
                $validated['format']
            ),

            'stock_movements' => $this->exportStockMovements(
                $businessId,
                $businessName,
                $validated['format'],
                $startDate,
                $endDate,
                $periodLabel
            ),

            'sales' => $this->exportSales(
                $businessId,
                $businessName,
                $validated['format'],
                $startDate,
                $endDate,
                $periodLabel
            ),

            'analytics' => $this->exportAnalytics(
                $businessId,
                $businessName,
                $validated['format'],
                $startDate,
                $endDate,
                $periodLabel
            ),

            'ai_prediction' => $this->exportAiPrediction(
                $businessId,
                $businessName,
                $validated['format']
            ),

            'complete' => $this->exportComplete(
                $businessId,
                $businessName,
                $validated['format'],
                $startDate,
                $endDate,
                $periodLabel
            ),
        };
    }

    /**
     * Ekspor laporan Inventory.
     */
    private function exportInventory(
        int $businessId,
        string $businessName,
        string $format
    ) {
        $fileName = 'laporan-inventory-'
            . now()->timezone('Asia/Jakarta')->format('Y-m-d-His');

        if ($format === 'pdf') {
            $inventoryData = $this->getInventoryData($businessId);

            return Pdf::loadView('reports.inventory', [
                'businessName' => $businessName,
                'products' => $inventoryData['products'],
                'summary' => $inventoryData['summary'],
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isRemoteEnabled', true)
                ->download($fileName . '.pdf');
        }

        return $this->downloadSpreadsheet(
            new InventoryReportExport($businessId),
            $fileName,
            $format
        );
    }

    /**
     * Ekspor laporan Riwayat Stok.
     */
    private function exportStockMovements(
        int $businessId,
        string $businessName,
        string $format,
        Carbon $startDate,
        Carbon $endDate,
        string $periodLabel
    ) {
        $fileName = 'laporan-riwayat-stok-'
            . now()->timezone('Asia/Jakarta')->format('Y-m-d-His');

        if ($format === 'pdf') {
            $stockMovementData = $this->getStockMovementData(
                $businessId,
                $startDate,
                $endDate
            );

            return Pdf::loadView('reports.stock-movements', [
                'businessName' => $businessName,
                'movements' => $stockMovementData['movements'],
                'summary' => $stockMovementData['summary'],
                'periodLabel' => $periodLabel,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isRemoteEnabled', true)
                ->download($fileName . '.pdf');
        }

        return $this->downloadSpreadsheet(
            new StockMovementReportExport(
                $businessId,
                $startDate,
                $endDate
            ),
            $fileName,
            $format
        );
    }

    /**
     * Ekspor laporan transaksi POS.
     */
    private function exportSales(
        int $businessId,
        string $businessName,
        string $format,
        Carbon $startDate,
        Carbon $endDate,
        string $periodLabel
    ) {
        $fileName = 'laporan-penjualan-pos-'
            . now()->timezone('Asia/Jakarta')->format('Y-m-d-His');

        if ($format === 'pdf') {
            $salesData = $this->getSalesData(
                $businessId,
                $startDate,
                $endDate
            );

            return Pdf::loadView('reports.sales', [
                'businessName' => $businessName,
                'transactions' => $salesData['transactions'],
                'summary' => $salesData['summary'],
                'periodLabel' => $periodLabel,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isRemoteEnabled', true)
                ->download($fileName . '.pdf');
        }

        return $this->downloadSpreadsheet(
            new SalesReportExport(
                $businessId,
                $startDate,
                $endDate
            ),
            $fileName,
            $format
        );
    }

    /**
     * Ekspor laporan Analytics.
     */
    private function exportAnalytics(
        int $businessId,
        string $businessName,
        string $format,
        Carbon $startDate,
        Carbon $endDate,
        string $periodLabel
    ) {
        $fileName = 'laporan-analytics-'
            . now()->timezone('Asia/Jakarta')->format('Y-m-d-His');

        if ($format === 'pdf') {
            $analyticsData = $this->getAnalyticsData(
                $businessId,
                $startDate,
                $endDate
            );

            return Pdf::loadView('reports.analytics', [
                'businessName' => $businessName,
                'dailyAnalytics' => $analyticsData['daily_analytics'],
                'topProducts' => $analyticsData['top_products'],
                'summary' => $analyticsData['summary'],
                'periodLabel' => $periodLabel,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isRemoteEnabled', true)
                ->download($fileName . '.pdf');
        }

        return $this->downloadSpreadsheet(
            new AnalyticsReportExport(
                $businessId,
                $startDate,
                $endDate
            ),
            $fileName,
            $format
        );
    }

    /**
     * Ekspor laporan Prediksi AI.
     */
    private function exportAiPrediction(
        int $businessId,
        string $businessName,
        string $format
    ) {
        $fileName = 'laporan-prediksi-ai-'
            . now()->timezone('Asia/Jakarta')->format('Y-m-d-His');

        $prediction = $this->getPredictionData($businessId);

        if ($format === 'pdf') {
            return Pdf::loadView('reports.ai-prediction', [
                'businessName' => $businessName,
                'prediction' => $prediction,
                'products' => collect($prediction['products'] ?? []),
                'generatedAt' => now()->timezone('Asia/Jakarta'),
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isRemoteEnabled', true)
                ->download($fileName . '.pdf');
        }

        return $this->downloadSpreadsheet(
            new AIPredictionReportExport($prediction),
            $fileName,
            $format
        );
    }

    /**
     * Ekspor laporan lengkap.
     *
     * PDF  : satu dokumen dengan beberapa bagian.
     * XLSX : satu workbook dengan beberapa sheet.
     * CSV  : satu ZIP yang berisi beberapa file CSV.
     */
    private function exportComplete(
        int $businessId,
        string $businessName,
        string $format,
        Carbon $startDate,
        Carbon $endDate,
        string $periodLabel
    ) {
        $fileName = 'laporan-lengkap-'
            . now()->timezone('Asia/Jakarta')->format('Y-m-d-His');

        $prediction = $this->getPredictionData($businessId);

        if ($format === 'xlsx') {
            return Excel::download(
                new CompleteReportExport(
                    $businessId,
                    $startDate,
                    $endDate,
                    $prediction
                ),
                $fileName . '.xlsx',
                ExcelFormat::XLSX
            );
        }

        if ($format === 'csv') {
            return $this->downloadCompleteCsvZip(
                $businessId,
                $startDate,
                $endDate,
                $prediction,
                $fileName
            );
        }

        $inventoryData = $this->getInventoryData($businessId);

        $stockMovementData = $this->getStockMovementData(
            $businessId,
            $startDate,
            $endDate
        );

        $salesData = $this->getSalesData(
            $businessId,
            $startDate,
            $endDate
        );

        $analyticsData = $this->getAnalyticsData(
            $businessId,
            $startDate,
            $endDate
        );

        return Pdf::loadView('reports.complete', [
            'businessName' => $businessName,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now()->timezone('Asia/Jakarta'),

            'inventory' => $inventoryData,
            'stockMovements' => $stockMovementData,
            'sales' => $salesData,
            'analytics' => $analyticsData,
            'prediction' => $prediction,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true)
            ->download($fileName . '.pdf');
    }

    /**
     * Mengambil data dan ringkasan Inventory.
     */
    private function getInventoryData(int $businessId): array
    {
        $products = Product::query()
            ->where('business_id', $businessId)
            ->orderBy('name')
            ->get();

        $summary = [
            'total_products' => $products->count(),

            'total_stock' => (int) $products->sum('stock'),

            'critical_products' => $products
                ->filter(function ($product) {
                    return (int) $product->stock
                        < (int) $product->minimum_stock;
                })
                ->count(),

            'overstock_products' => $products
                ->filter(function ($product) {
                    return (int) $product->stock
                        > (int) $product->maximum_stock;
                })
                ->count(),

            'inventory_value' => (float) $products
                ->sum(function ($product) {
                    return (float) $product->purchase_price
                        * (int) $product->stock;
                }),

            'potential_revenue' => (float) $products
                ->sum(function ($product) {
                    return (float) $product->selling_price
                        * (int) $product->stock;
                }),
        ];

        return [
            'products' => $products,
            'summary' => $summary,
        ];
    }

    /**
     * Mengambil data dan ringkasan Riwayat Stok.
     */
    private function getStockMovementData(
        int $businessId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $movements = StockMovement::query()
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $summary = [
            'total_movements' => $movements->count(),

            'affected_products' => $movements
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->count(),

            'stock_in' => (int) $movements
                ->filter(function ($movement) {
                    return (int) $movement->quantity > 0;
                })
                ->sum('quantity'),

            'stock_out' => abs(
                (int) $movements
                    ->filter(function ($movement) {
                        return (int) $movement->quantity < 0;
                    })
                    ->sum('quantity')
            ),

            'units_sold' => abs(
                (int) $movements
                    ->where('type', 'sale')
                    ->sum('quantity')
            ),
        ];

        return [
            'movements' => $movements,
            'summary' => $summary,
        ];
    }

    /**
     * Mengambil data dan ringkasan transaksi POS.
     */
    private function getSalesData(
        int $businessId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $transactions = Transaction::query()
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $totalTransactions = $transactions->count();

        $totalItemsSold = DB::table('transaction_details as details')
            ->join(
                'transactions as transactions',
                'transactions.id',
                '=',
                'details.transaction_id'
            )
            ->where('transactions.business_id', $businessId)
            ->whereBetween('transactions.created_at', [
                $startDate,
                $endDate,
            ])
            ->sum('details.quantity');

        $summary = [
            'total_transactions' => $totalTransactions,
            'total_items_sold' => (int) $totalItemsSold,
            'subtotal' => (float) $transactions->sum('subtotal'),
            'tax' => (float) $transactions->sum('tax'),
            'discount' => (float) $transactions->sum('discount'),
            'total_revenue' => (float) $transactions->sum('total_amount'),
            'total_cost' => (float) $transactions->sum('total_cost'),
            'total_profit' => (float) $transactions->sum('total_profit'),

            'average_transaction' => $totalTransactions > 0
                ? (float) $transactions->sum('total_amount')
                    / $totalTransactions
                : 0,
        ];

        return [
            'transactions' => $transactions,
            'summary' => $summary,
        ];
    }

    /**
     * Mengambil data Analytics per hari dan produk unggulan.
     */
    private function getAnalyticsData(
        int $businessId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $transactionDetailSummary = DB::table('transaction_details')
            ->select(
                'transaction_id',
                DB::raw('SUM(quantity) as total_items')
            )
            ->groupBy('transaction_id');

        $dailyAnalytics = DB::table('transactions as transactions')
            ->leftJoinSub(
                $transactionDetailSummary,
                'detail_summary',
                function ($join) {
                    $join->on(
                        'detail_summary.transaction_id',
                        '=',
                        'transactions.id'
                    );
                }
            )
            ->where('transactions.business_id', $businessId)
            ->whereBetween('transactions.created_at', [
                $startDate,
                $endDate,
            ])
            ->selectRaw(
                'DATE(transactions.created_at) as transaction_date'
            )
            ->selectRaw(
                'COUNT(transactions.id) as total_transactions'
            )
            ->selectRaw(
                'COALESCE(SUM(detail_summary.total_items), 0) as total_items'
            )
            ->selectRaw(
                'COALESCE(SUM(transactions.subtotal), 0) as total_revenue'
            )
            ->selectRaw(
                'COALESCE(SUM(transactions.total_cost), 0) as total_cost'
            )
            ->selectRaw(
                'COALESCE(SUM(transactions.total_profit), 0) as total_profit'
            )
            ->groupByRaw('DATE(transactions.created_at)')
            ->orderByRaw('DATE(transactions.created_at) ASC')
            ->get()
            ->map(function ($row) {
                $revenue = (float) $row->total_revenue;
                $profit = (float) $row->total_profit;

                $row->profit_margin = $revenue > 0
                    ? ($profit / $revenue) * 100
                    : 0;

                $row->average_transaction =
                    (int) $row->total_transactions > 0
                        ? $revenue / (int) $row->total_transactions
                        : 0;

                return $row;
            });

        $topProducts = DB::table('transaction_details as details')
            ->join(
                'transactions as transactions',
                'transactions.id',
                '=',
                'details.transaction_id'
            )
            ->leftJoin(
                'products as products',
                'products.id',
                '=',
                'details.product_id'
            )
            ->where('transactions.business_id', $businessId)
            ->whereBetween('transactions.created_at', [
                $startDate,
                $endDate,
            ])
            ->select([
                'details.product_id',
                'products.product_code',
                'products.name',
            ])
            ->selectRaw(
                'SUM(details.quantity) as total_quantity'
            )
            ->selectRaw(
                'SUM(details.subtotal) as total_revenue'
            )
            ->selectRaw(
                'SUM(
                    (details.selling_price - details.purchase_price)
                    * details.quantity
                ) as total_profit'
            )
            ->groupBy(
                'details.product_id',
                'products.product_code',
                'products.name'
            )
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $product->name = $product->name
                    ?? 'Produk yang telah dihapus';

                $product->product_code = $product->product_code
                    ?? '-';

                $product->profit_margin =
                    (float) $product->total_revenue > 0
                        ? (
                            (float) $product->total_profit
                            / (float) $product->total_revenue
                        ) * 100
                        : 0;

                return $product;
            });

        $summary = [
            'total_transactions' => (int) $dailyAnalytics
                ->sum('total_transactions'),

            'total_items_sold' => (int) $dailyAnalytics
                ->sum('total_items'),

            'total_revenue' => (float) $dailyAnalytics
                ->sum('total_revenue'),

            'total_cost' => (float) $dailyAnalytics
                ->sum('total_cost'),

            'total_profit' => (float) $dailyAnalytics
                ->sum('total_profit'),
        ];

        $summary['profit_margin'] =
            $summary['total_revenue'] > 0
                ? (
                    $summary['total_profit']
                    / $summary['total_revenue']
                ) * 100
                : 0;

        $summary['average_transaction'] =
            $summary['total_transactions'] > 0
                ? $summary['total_revenue']
                    / $summary['total_transactions']
                : 0;

        return [
            'daily_analytics' => $dailyAnalytics,
            'top_products' => $topProducts,
            'summary' => $summary,
        ];
    }

    /**
     * Memanggil PredictionService yang sudah memiliki pembatas tenant.
     */
    private function getPredictionData(int $businessId): array
    {
        /*
         * Parameter businessId sengaja tidak dikirim ke service.
         * PredictionService mengambil tenant dari Auth::user()
         * agar tenant tidak dapat dipalsukan melalui request.
         */
        $prediction = app(PredictionService::class)
            ->getPrediction();

        if (!is_array($prediction)) {
            return [
                'service_status' => 'offline',
                'service_message' => 'Data prediksi tidak tersedia.',
                'mode' => 'no_data',
                'mode_label' => 'Belum Ada Prediksi',
                'forecast_days' => 7,
                'predicted_total' => 0,
                'last_week_total' => 0,
                'products' => [],
                'summary' => 'Belum tersedia data prediksi.',
            ];
        }

        return $prediction;
    }

    /**
     * Download file Excel atau CSV tunggal.
     */
    private function downloadSpreadsheet(
        object $export,
        string $fileName,
        string $format
    ) {
        if ($format === 'csv') {
            return Excel::download(
                $export,
                $fileName . '.csv',
                ExcelFormat::CSV
            );
        }

        return Excel::download(
            $export,
            $fileName . '.xlsx',
            ExcelFormat::XLSX
        );
    }

    /**
     * Laporan lengkap format CSV dibuat menjadi ZIP karena satu file CSV
     * tidak dapat memiliki beberapa sheet atau beberapa tabel.
     */
    private function downloadCompleteCsvZip(
        int $businessId,
        Carbon $startDate,
        Carbon $endDate,
        array $prediction,
        string $fileName
    ) {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException(
                'Ekstensi ZIP PHP belum aktif.'
            );
        }

        $temporaryDirectory = storage_path(
            'app/report-exports'
        );

        File::ensureDirectoryExists($temporaryDirectory);

        $temporaryZipName = Str::uuid()->toString() . '.zip';

        $temporaryZipPath = $temporaryDirectory
            . DIRECTORY_SEPARATOR
            . $temporaryZipName;

        $zip = new ZipArchive();

        $zipResult = $zip->open(
            $temporaryZipPath,
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );

        if ($zipResult !== true) {
            throw new RuntimeException(
                'Gagal membuat file ZIP laporan.'
            );
        }

        $zip->addFromString(
            '01-inventory.csv',
            Excel::raw(
                new InventoryReportExport($businessId),
                ExcelFormat::CSV
            )
        );

        $zip->addFromString(
            '02-riwayat-stok.csv',
            Excel::raw(
                new StockMovementReportExport(
                    $businessId,
                    $startDate,
                    $endDate
                ),
                ExcelFormat::CSV
            )
        );

        $zip->addFromString(
            '03-penjualan-pos.csv',
            Excel::raw(
                new SalesReportExport(
                    $businessId,
                    $startDate,
                    $endDate
                ),
                ExcelFormat::CSV
            )
        );

        $zip->addFromString(
            '04-analytics.csv',
            Excel::raw(
                new AnalyticsReportExport(
                    $businessId,
                    $startDate,
                    $endDate
                ),
                ExcelFormat::CSV
            )
        );

        $zip->addFromString(
            '05-prediksi-ai.csv',
            Excel::raw(
                new AIPredictionReportExport($prediction),
                ExcelFormat::CSV
            )
        );

        $zip->close();

        return response()
            ->download(
                $temporaryZipPath,
                $fileName . '.zip'
            )
            ->deleteFileAfterSend(true);
    }

    /**
     * Mengubah pilihan periode menjadi tanggal mulai dan selesai.
     */
    private function resolveDateRange(
        string $period,
        ?string $startDate,
        ?string $endDate
    ): array {
        $now = now()->timezone('Asia/Jakarta');

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Hari Ini',
            ],

            '7_days' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
                '7 Hari Terakhir',
            ],

            '30_days' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
                '30 Hari Terakhir',
            ],

            'custom' => [
                Carbon::createFromFormat(
                    'Y-m-d',
                    (string) $startDate,
                    'Asia/Jakarta'
                )->startOfDay(),

                Carbon::createFromFormat(
                    'Y-m-d',
                    (string) $endDate,
                    'Asia/Jakarta'
                )->endOfDay(),

                Carbon::createFromFormat(
                    'Y-m-d',
                    (string) $startDate,
                    'Asia/Jakarta'
                )->format('d/m/Y')
                . ' - '
                . Carbon::createFromFormat(
                    'Y-m-d',
                    (string) $endDate,
                    'Asia/Jakarta'
                )->format('d/m/Y'),
            ],

            default => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
                '30 Hari Terakhir',
            ],
        };
    }

    /**
     * Mengambil nama bisnis tanpa menerima business_id dari form.
     */
    private function resolveBusinessName($user): string
    {
        $business = $user->business;

        return $business->business_name
            ?? $business->name
            ?? $user->business_name
            ?? 'BusinessMate';
    }
}
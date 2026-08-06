<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsReportExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    public function __construct(
        private readonly int $businessId,
        private readonly Carbon $startDate,
        private readonly Carbon $endDate
    ) {
    }

    /**
     * Menghasilkan ringkasan performa bisnis per hari.
     */
    public function collection(): Collection
    {
        /*
         * Detail transaksi dikelompokkan terlebih dahulu agar total
         * transaksi tidak terhitung berulang ketika dilakukan join.
         */
        $transactionDetailSummary = DB::table('transaction_details')
            ->select(
                'transaction_id',
                DB::raw('SUM(quantity) as total_items')
            )
            ->groupBy('transaction_id');

        $analytics = DB::table('transactions as transactions')
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
            ->where('transactions.business_id', $this->businessId)
            ->whereBetween('transactions.created_at', [
                $this->startDate,
                $this->endDate,
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
            ->get();

        return $analytics
            ->values()
            ->map(function ($row, int $index) {
                $revenue = (float) $row->total_revenue;
                $profit = (float) $row->total_profit;

                $profitMargin = $revenue > 0
                    ? ($profit / $revenue) * 100
                    : 0;

                $averageTransaction = (int) $row->total_transactions > 0
                    ? $revenue / (int) $row->total_transactions
                    : 0;

                return [
                    $index + 1,
                    Carbon::parse($row->transaction_date)
                        ->locale('id')
                        ->translatedFormat('d F Y'),
                    (int) $row->total_transactions,
                    (int) $row->total_items,
                    $revenue,
                    (float) $row->total_cost,
                    $profit,
                    round($profitMargin, 2),
                    $averageTransaction,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tanggal',
            'Jumlah Transaksi',
            'Jumlah Produk Terjual',
            'Pendapatan',
            'Total Modal',
            'Laba',
            'Margin Laba',
            'Rata-rata Transaksi',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '"Rp" #,##0',
            'F' => '"Rp" #,##0',
            'G' => '"Rp" #,##0',
            'H' => '0.00"%"',
            'I' => '"Rp" #,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:I1');

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF2563EB',
                ],
            ],
            'alignment' => [
                'vertical' => 'center',
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(26);

        return [];
    }

    public function title(): string
    {
        return 'Analytics';
    }
}
<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly int $businessId,
        private readonly Carbon $startDate,
        private readonly Carbon $endDate
    ) {
    }

    /**
     * Mengambil transaksi hanya milik bisnis yang sedang login.
     */
    public function query(): Builder
    {
        return Transaction::query()
            ->where('business_id', $this->businessId)
            ->whereBetween('created_at', [
                $this->startDate,
                $this->endDate,
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tanggal Transaksi',
            'Nomor Invoice',
            'Metode Pembayaran',
            'Subtotal',
            'Pajak',
            'Diskon',
            'Total Pembayaran',
            'Total Modal',
            'Laba',
        ];
    }

    /**
     * Mengubah setiap transaksi menjadi satu baris laporan.
     */
    public function map($transaction): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $transaction->created_at
                ? $transaction->created_at->timezone('Asia/Jakarta')
                    ->format('d-m-Y H:i')
                : '-',
            $transaction->invoice_number,
            $this->formatPaymentMethod($transaction->payment_method),
            (float) $transaction->subtotal,
            (float) $transaction->tax,
            (float) $transaction->discount,
            (float) $transaction->total_amount,
            (float) $transaction->total_cost,
            (float) $transaction->total_profit,
        ];
    }

    private function formatPaymentMethod(?string $paymentMethod): string
    {
        return match (strtolower((string) $paymentMethod)) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'debit' => 'Kartu Debit',
            'credit' => 'Kartu Kredit',
            default => $paymentMethod
                ? ucwords(str_replace('_', ' ', $paymentMethod))
                : '-',
        };
    }

    public function columnFormats(): array
    {
        return [
            'E' => '"Rp" #,##0',
            'F' => '"Rp" #,##0',
            'G' => '"Rp" #,##0',
            'H' => '"Rp" #,##0',
            'I' => '"Rp" #,##0',
            'J' => '"Rp" #,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:J1');

        $sheet->getStyle('A1:J1')->applyFromArray([
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
        return 'Penjualan POS';
    }
}
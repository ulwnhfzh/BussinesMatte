<?php

namespace App\Exports;

use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMovementReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    private int $businessId;

    private Carbon $startDate;

    private Carbon $endDate;

    private int $rowNumber = 0;

    public function __construct(
        int $businessId,
        Carbon $startDate,
        Carbon $endDate
    ) {
        $this->businessId = $businessId;
        $this->startDate = $startDate->copy();
        $this->endDate = $endDate->copy();
    }

    /**
     * Mengambil riwayat stok tenant aktif berdasarkan periode.
     */
    public function query(): Builder
    {
        return StockMovement::query()
            ->where('business_id', $this->businessId)
            ->whereBetween(
                'created_at',
                [
                    $this->startDate,
                    $this->endDate,
                ]
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Judul kolom pada Excel dan CSV.
     */
    public function headings(): array
    {
        return [
            'No.',
            'Tanggal',
            'Kode Produk',
            'Nama Produk',
            'Jenis Pergerakan',
            'Perubahan Stok',
            'Stok Sebelum',
            'Stok Sesudah',
            'Referensi',
            'Catatan',
        ];
    }

    /**
     * Menyusun setiap baris riwayat stok.
     */
    public function map($movement): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,

            $movement->created_at
                ? $movement->created_at
                    ->timezone('Asia/Jakarta')
                    ->format('d/m/Y H:i')
                : '-',

            $movement->product_code ?: '-',

            $movement->product_name ?: 'Produk tidak tersedia',

            $this->resolveTypeLabel($movement->type),

            (int) $movement->quantity,

            (int) $movement->stock_before,

            (int) $movement->stock_after,

            $this->resolveReference($movement),

            $movement->note ?: '-',
        ];
    }

    /**
     * Mengubah kode jenis pergerakan menjadi label laporan.
     */
    private function resolveTypeLabel(?string $type): string
    {
        return match ($type) {
            'initial' => 'Stok Awal',
            'adjustment' => 'Penyesuaian',
            'sale' => 'Penjualan',
            'restock' => 'Restok',
            'return' => 'Retur',
            'refund' => 'Refund',
            'void' => 'Pembatalan Transaksi',
            default => $type
                ? Str::headline($type)
                : 'Tidak Diketahui',
        };
    }

    /**
     * Menyusun informasi sumber pergerakan stok.
     */
    private function resolveReference($movement): string
    {
        if (
            !$movement->reference_type
            || !$movement->reference_id
        ) {
            return '-';
        }

        $referenceName = Str::headline(
            class_basename($movement->reference_type)
        );

        return $referenceName
            . ' #'
            . $movement->reference_id;
    }

    /**
     * Format angka pada file Excel.
     */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    /**
     * Memberikan gaya pada header Excel.
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        $sheet->setAutoFilter(
            $sheet->calculateWorksheetDimension()
        );

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'argb' => 'FF2563EB',
                    ],
                ],
            ],
        ];
    }

    /**
     * Nama sheet pada file Excel.
     */
    public function title(): string
    {
        return 'Riwayat Stok';
    }
}
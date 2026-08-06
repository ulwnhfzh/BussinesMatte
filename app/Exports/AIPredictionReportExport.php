<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AIPredictionReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithCustomStartCell,
    WithEvents,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly array $prediction
    ) {
    }

    public function collection(): Collection
    {
        return collect($this->prediction['products'] ?? []);
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nama Produk',
            'Metode Prediksi',
            'Jumlah Hari Data',
            'Hari Penjualan',
            'Prediksi Permintaan',
            'Stok Saat Ini',
            'Stok Minimum',
            'Stok Maksimum',
            'Rekomendasi Restok',
            'Estimasi Biaya',
            'Tingkat Keyakinan',
            'Alasan Rekomendasi',
        ];
    }

    public function map($product): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $product['product_name'] ?? '-',
            $product['method_label'] ?? 'Belum Ada Prediksi',
            (int) ($product['data_days'] ?? 0),
            (int) ($product['sales_days'] ?? 0),
            (int) ($product['predicted_quantity'] ?? 0),
            (int) ($product['current_stock'] ?? 0),
            (int) ($product['minimum_stock'] ?? 0),
            (int) ($product['maximum_stock'] ?? 0),
            (int) ($product['recommended_restock'] ?? 0),
            (float) ($product['estimated_cost'] ?? 0),
            $product['confidence_label'] ?? 'Belum tersedia',
            $product['method_reason']
                ?? $product['reason']
                ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'K' => '"Rp" #,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $modeLabel = $this->prediction['mode_label']
                    ?? 'Belum Ada Prediksi';

                $forecastDays = (int) (
                    $this->prediction['forecast_days'] ?? 7
                );

                $summary = $this->prediction['summary']
                    ?? 'Belum tersedia ringkasan prediksi.';

                $sheet->setCellValue(
                    'A1',
                    'LAPORAN PREDIKSI INVENTORY AI'
                );

                $sheet->setCellValue(
                    'A2',
                    'Metode aktif: ' . $modeLabel
                );

                $sheet->setCellValue(
                    'A3',
                    'Periode prediksi: '
                    . $forecastDays
                    . ' hari | '
                    . $summary
                );

                $sheet->mergeCells('A1:M1');
                $sheet->mergeCells('A2:M2');
                $sheet->mergeCells('A3:M3');

                $sheet->getStyle('A1:M1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 15,
                        'color' => [
                            'argb' => 'FFFFFFFF',
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FF1D4ED8',
                        ],
                    ],
                ]);

                $sheet->getStyle('A2:M3')->applyFromArray([
                    'font' => [
                        'color' => [
                            'argb' => 'FF475569',
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFEFF6FF',
                        ],
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(30);

                $sheet->getStyle('A3:M3')
                    ->getAlignment()
                    ->setWrapText(true);
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A6');
        $sheet->setAutoFilter('A5:M5');

        $sheet->getStyle('A5:M5')->applyFromArray([
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
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(5)->setRowHeight(35);

        $sheet->getStyle('M6:M1000')
            ->getAlignment()
            ->setWrapText(true);

        return [];
    }

    public function title(): string
    {
        return 'Prediksi AI';
    }
}
<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    private int $businessId;

    private int $rowNumber = 0;

    public function __construct(int $businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * Mengambil produk hanya dari bisnis pengguna yang sedang login.
     */
    public function query(): Builder
    {
        return Product::query()
            ->where('business_id', $this->businessId)
            ->orderBy('name');
    }

    /**
     * Judul kolom pada Excel dan CSV.
     */
    public function headings(): array
    {
        return [
            'No.',
            'Kode Produk',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Harga Beli',
            'Harga Jual',
            'Stok Saat Ini',
            'Stok Minimum',
            'Stok Maksimum',
            'Status Stok',
            'Nilai Persediaan',
        ];
    }

    /**
     * Menyusun setiap baris produk.
     */
    public function map($product): array
    {
        $this->rowNumber++;

        $stockStatus = $this->resolveStockStatus(
            (int) $product->stock,
            (int) $product->minimum_stock,
            (int) $product->maximum_stock
        );

        return [
            $this->rowNumber,
            $product->product_code,
            $product->name,
            $product->category ?: 'Tanpa Kategori',
            $product->unit,
            (float) $product->purchase_price,
            (float) $product->selling_price,
            (int) $product->stock,
            (int) $product->minimum_stock,
            (int) $product->maximum_stock,
            $stockStatus,
            (float) $product->stock
                * (float) $product->purchase_price,
        ];
    }

    /**
     * Menentukan status stok berdasarkan batas produk.
     */
    private function resolveStockStatus(
        int $stock,
        int $minimumStock,
        int $maximumStock
    ): string {
        if ($stock < $minimumStock) {
            return 'Kritis';
        }

        if ($stock > $maximumStock) {
            return 'Melebihi Maksimum';
        }

        return 'Optimal';
    }

    /**
     * Format kolom angka pada file Excel.
     */
    public function columnFormats(): array
    {
        return [
            'F' => '"Rp" #,##0',
            'G' => '"Rp" #,##0',
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'L' => '"Rp" #,##0',
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
        return 'Inventory';
    }
}
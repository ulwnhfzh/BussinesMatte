<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompleteReportExport implements WithMultipleSheets
{
    public function __construct(
        private readonly int $businessId,
        private readonly Carbon $startDate,
        private readonly Carbon $endDate,
        private readonly array $prediction
    ) {
    }

    /**
     * Membuat satu file Excel dengan beberapa sheet laporan.
     */
    public function sheets(): array
    {
        return [
            new InventoryReportExport(
                $this->businessId
            ),

            new StockMovementReportExport(
                $this->businessId,
                $this->startDate,
                $this->endDate
            ),

            new SalesReportExport(
                $this->businessId,
                $this->startDate,
                $this->endDate
            ),

            new AnalyticsReportExport(
                $this->businessId,
                $this->startDate,
                $this->endDate
            ),

            new AIPredictionReportExport(
                $this->prediction
            ),
        ];
    }
}
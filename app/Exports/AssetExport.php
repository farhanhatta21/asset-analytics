<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Services\MonitoringExportService;

class AssetExport implements WithMultipleSheets
{
    protected $request;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {
        $sheets = [];

        /* SHEET 1 - MONITORING */
        $sheets[] = new MonitoringSheetExport(
            $this->request,
            app(MonitoringExportService::class)
        );


        /* SHEET 2 - BREAKDOWN */
        if (!empty($this->request['include_breakdown'])) {

            $sheets[] = new BreakdownSheetExport(
                $this->request
            );
        }


        /* SHEET 3 - PREDICTION */
        $sheets[] = new PredictionSheetExport(
            $this->request,
            app(MonitoringExportService::class)
        );


        return $sheets;
    }
}
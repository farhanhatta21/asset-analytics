<?php

namespace App\Exports;

use App\Services\MonitoringExportService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Services\AnalysisService;

class MonitoringSheetExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{

    protected $request;
    protected MonitoringExportService $service;

    public function __construct(
        array $request,
        MonitoringExportService $service
    )
    {
        $this->request = $request;
        $this->service = $service;
    }

    public function title(): string
    {
        return 'Dashboard Monitoring';
    }

    public function array(): array
    {
        $rows = $this->service->getMonitoringData($this->request);

        $summary = $this->service->getSummary($rows);

        /* Parameter dipilih user */
        $params = $this->request['params'] ?? [
            'availability',
            'utilisation',
            'mtbf',
            'mttrp',
            'health_score'
        ];

        /* Periode hasil filter */
        $periods = $rows
            ->pluck('periode')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if (
            !empty($this->request['periode_awal']) &&
            !empty($this->request['periode_akhir'])
        ) {

            $periods = $periods->filter(function ($periode) {

                return
                    $periode >= $this->request['periode_awal'] &&
                    $periode <= $this->request['periode_akhir'];

            })->values();

        }

        /* Group alat */
        $assets = $rows
            ->groupBy('nama_alat');

        $data = [];

        /* Header  */
        $data[] = [
            'PT Pelabuhan Indonesia (Persero) Regional 4'
        ];

        $data[] = [
            'LAPORAN MONITORING KINERJA ALAT'
        ];

        $data[] = [
            'Tanggal Export : '.\Carbon\Carbon::now('Asia/Makassar')
            ->translatedFormat('d F Y H:i')
        ];

        $periodeText = 'Semua Periode';
        if (
            !empty($this->request['periode_awal']) &&
            !empty($this->request['periode_akhir'])
        ) {

            $periodeText =
                $this->request['periode_awal']
                .' s/d '.
                $this->request['periode_akhir'];

        }

        $data[] = [
            'Periode : '.$periodeText
        ];

        $data[] = [];

        /* Summary */
        $data[]=[
            'Total Asset',
            $summary['total_asset'].' Alat'
        ];

        $data[]=[
            'Total Breakdown',
            $summary['total_breakdown'].' kejadian'
        ];

        $data[]=[
            'Total Downtime',
            $summary['total_downtime'].' jam'
        ];

        $data[] = [];

        /* HEADER UTAMA */
        $selectedParams = $this->request['params'] ?? [
            'availability',
            'utilisation',
            'mtbf',
            'mttrp',
            'health_score'
        ];

        $label = [
            'availability' => 'Availability',
            'utilisation' => 'Utilisation',
            'mtbf' => 'MTBF',
            'mttrp' => 'MTTRp',
            'health_score' => 'Health Score',
            'total_bd' => 'Total Breakdown',
            'total_downtime' => 'Downtime',
        ];

        $header1 = [
            'No',
            'Nama Alat'
        ];

        $header2 = [
            '',
            ''
        ];

        foreach ($selectedParams as $param) {

            $header1[] = $label[$param];

            for ($i = 1; $i < count($periods); $i++) {

                $header1[] = '';

            }

            foreach ($periods as $periode) {

                $header2[] = $periode;

            }

        }

        $data[] = $header1;
        $data[] = $header2;

        /* DATA */
        $no = 1;

        foreach ($assets as $nama => $history) {

                $row = [];

                $row[] = $no++;

                $row[] = $nama;

                foreach ($selectedParams as $param) {

                    foreach ($periods as $periode) {

                        $record = $history->firstWhere(
                            'periode',
                            $periode
                        );

                        if (!$record) {

                            $row[] = 0;

                            continue;

                        }

                        switch ($param) {
                            case 'availability':
                                $row[] = is_numeric($record->availability)
                                    ? (float) $record->availability
                                    : 0;
                                break;

                            case 'utilisation':
                                $row[] = is_numeric($record->utilisation)
                                    ? (float) $record->utilisation
                                    : 0;
                                break;

                            case 'mtbf':
                                $row[] = is_numeric($record->mtbf)
                                    ? (float) $record->mtbf
                                    : 0;
                                break;

                            case 'mttrp':
                                $row[] = is_numeric($record->mttrp)
                                    ? (float) $record->mttrp
                                    : 0;
                                break;

                            case 'health_score':
                                $row[] = is_numeric($record->health_score)
                                    ? (float) $record->health_score
                                    : 0;
                                break;

                            case 'total_bd':
                                $row[] = (int) $record->total_bd;
                                break;

                            case 'total_downtime':
                                $row[] = is_numeric($record->total_downtime)
                                    ? (float) $record->total_downtime
                                    : 0;
                                break;

                            default:
                                $row[] = 0;
                                break;

                        }

                    }

                }

                $data[] = $row;

        }
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $headerRow1 = 8;
        $headerRow2 = 9;
        $dataRow    = 10;
        
        /* Hitung jumlah periode */
        $rows = $this->service->getMonitoringData(
            $this->request
        );

        $periods = $rows
            ->pluck('periode')
            ->unique()
            ->sort()
            ->values();

        if (
            !empty($this->request['periode_awal']) &&
            !empty($this->request['periode_akhir'])
        ) {

            $periods = $periods->filter(function ($periode) {

                return
                    $periode >= $this->request['periode_awal'] &&
                    $periode <= $this->request['periode_akhir'];

            })->values();

        }

        /* Merge Judul */
        $selectedParams = $this->request['params'] ?? [
            'availability',
            'utilisation',
            'mtbf',
            'mttrp',
            'health_score'
        ];

        $lastColumn = 2 + (count($periods) * count($selectedParams));

        $lastLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate
            ::stringFromColumnIndex($lastColumn);

        $sheet->mergeCells("A1:{$lastLetter}1");
        $sheet->mergeCells("A8:A9");
        $sheet->mergeCells("B8:B9");
        $sheet->mergeCells("A2:{$lastLetter}2");
        $sheet->mergeCells("A3:{$lastLetter}3");

        /* Style Judul */
        $sheet->getStyle("A1")
            ->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet->getStyle("A2")
            ->getFont()
            ->setBold(true)
            ->setSize(14);

        $sheet->getStyle("A3")
            ->getFont()
            ->setItalic(true);

        $sheet->getStyle('A4:A7')->getFont()->setBold(true);

        /* Merge Header Periode */
        $selectedParams = $this->request['params'] ?? [
            'availability',
            'utilisation',
            'mtbf',
            'mttrp',
            'health_score'
        ];

        $startColumn = 3;
        foreach ($selectedParams as $param) {

            $first = \PhpOffice\PhpSpreadsheet\Cell\Coordinate
                ::stringFromColumnIndex($startColumn);

            $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate
                ::stringFromColumnIndex(
                    $startColumn +
                    count($periods) - 1
                );

            $sheet->mergeCells(
                "{$first}{$headerRow1}:{$last}{$headerRow1}"
            );

            $startColumn += count($periods);
        }

        /* Header Style */
        $sheet
            ->getStyle("A{$headerRow1}:{$lastLetter}{$headerRow2}")
            ->applyFromArray([

                'font'=>[
                    'bold'=>true,
                    'color'=>[
                        'rgb'=>'FFFFFF'
                    ]
                ],

                'fill'=>[
                    'fillType'=>'solid',
                    'startColor'=>[
                        'rgb'=>'005BAC'
                    ]
                ],

                'alignment'=>[
                    'horizontal'=>'center',
                    'vertical'=>'center'
                ]

            ]);

        /* Border */
        $lastRow = $sheet->getHighestRow();

        $sheet
            ->getStyle("A{$headerRow1}:{$lastLetter}{$lastRow}")
            ->applyFromArray([

                'borders'=>[
                    'allBorders'=>[
                        'borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ]
                ]

            ]);

            $sheet->getStyle("A{$headerRow1}:{$lastLetter}{$headerRow2}")
                ->getAlignment()
                ->setHorizontal(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                );

            $sheet->getStyle("A{$headerRow1}:{$lastLetter}{$headerRow2}")
                ->getAlignment()
                ->setVertical(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                );

        /* Center Semua Angka */
        $sheet
            ->getStyle("A{$headerRow1}:{$lastLetter}{$lastRow}")
            ->getAlignment()
            ->setVertical('center');

        /* Tebalkan Nama Alat */
        $sheet
            ->getStyle("B{$dataRow}:B{$lastRow}")
            ->getFont()
            ->setBold(true);

        /* Wrap Text */
        $lastRow = $sheet->getHighestRow();

        $sheet
            ->getStyle("A1:{$lastLetter}{$lastRow}")
            ->getAlignment()
            ->setWrapText(true);

        
        /* Center Alignment */
        $sheet
            ->getStyle("A{$headerRow1}:{$lastLetter}{$lastRow}")
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle("A{$headerRow1}:{$lastLetter}{$lastRow}")
            ->getAlignment()
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            );


        /* Tinggi Header */
        $sheet
            ->getRowDimension($headerRow1)
            ->setRowHeight(28);

        $sheet
            ->getRowDimension($headerRow2)
            ->setRowHeight(24);

        /* Header Company */
        $sheet
            ->getStyle("A1")
            ->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet
            ->getStyle("A2")
            ->getFont()
            ->setBold(true)
            ->setSize(13);

        $sheet
            ->getStyle("A3")
            ->getFont()
            ->setItalic(true)
            ->setSize(10);

        /* Auto Filter */
        $sheet
            ->setAutoFilter(
                "A{$headerRow2}:{$lastLetter}{$lastRow}"
            );
        
        return [];
    }

}
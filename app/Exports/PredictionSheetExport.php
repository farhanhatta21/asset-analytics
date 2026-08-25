<?php

namespace App\Exports;

use App\Services\MonitoringExportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PredictionSheetExport implements
    FromArray,
    WithTitle,
    WithStyles
{
    protected $request;

    protected $service;


    public function __construct(
        array $request,
        MonitoringExportService $service
    ) {
        $this->request = $request;

        $this->service = $service;
    }


    public function title(): string
    {
        return 'Prediction';
    }


    public function array(): array
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA PREDICTION
        |--------------------------------------------------------------------------
        */

        $rows = $this->service
            ->getPredictionData(
                $this->request
            );


        $data = [];


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        $data[] = [
            'Prediction Result'
        ];


        $data[] = [
            'Generated Automatically by Asset Analytics Monitoring System'
        ];


        $data[] = [];


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $data[] = [

            'No',

            'Nama Alat',

            'Kelompok Alat',

            'Target Periode Prediksi',

            'Predicted Health Score',

            'Maintenance Risk Score',

            'Status Prediksi'

        ];


        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $no = 1;


        foreach ($rows as $row) {

            $data[] = [

                $no++,

                $row->nama_alat,

                $row->group_alat,

                $row->prediction_period,

                round(
                    (float)
                    $row->predicted_health_score,
                    2
                ),

                round(
                    (float)
                    $row->maintenance_risk_score,
                    2
                ),

                $row->prediction_status

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | BARIS KOSONG SEBELUM CATATAN
        |--------------------------------------------------------------------------
        */

        $data[] = [];


        /*
        |--------------------------------------------------------------------------
        | CATATAN
        |--------------------------------------------------------------------------
        */

        $data[] = [

            '** Maintenance Risk Score dihitung menggunakan rumus 100 - Predicted Health Score. Semakin tinggi nilainya maka semakin tinggi prioritas maintenance.'

        ];


        return $data;
    }


    public function styles(Worksheet $sheet)
    {
        /*
        |--------------------------------------------------------------------------
        | MERGE TITLE
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:G1');

        $sheet->mergeCells('A2:G2');


        /*
        |--------------------------------------------------------------------------
        | STYLE TITLE
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet
            ->getStyle('A2')
            ->getFont()
            ->setItalic(true);


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A3:G3')
            ->applyFromArray([

                'font' => [

                    'bold' => true,

                    'color' => [

                        'rgb' => 'FFFFFF'

                    ]

                ],

                'fill' => [

                    'fillType' =>
                        \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,

                    'startColor' => [

                        'rgb' => '005BAC'

                    ]

                ],

                'alignment' => [

                    'horizontal' =>
                        \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,

                    'vertical' =>
                        \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER

                ]

            ]);


        /*
        |--------------------------------------------------------------------------
        | JUMLAH BARIS DATA
        |--------------------------------------------------------------------------
        |
        | Row 1 = Title
        | Row 2 = Subtitle
        | Row 3 = Header
        | Row 4 sampai sebelum catatan = Data
        | Baris terakhir = Catatan
        |
        */

        $lastRow =
            $sheet->getHighestRow();

        $noteRow =
            $lastRow;

        $dataStartRow = 4;

        $dataEndRow =
            $noteRow - 1;


        /*
        |--------------------------------------------------------------------------
        | BORDER DATA
        |--------------------------------------------------------------------------
        */

        if ($dataEndRow >= $dataStartRow) {

            $sheet
                ->getStyle(
                    "A3:G{$dataEndRow}"
                )
                ->applyFromArray([

                    'borders' => [

                        'allBorders' => [

                            'borderStyle' =>
                                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,

                            'color' => [

                                'rgb' => 'D1D5DB'

                            ]

                        ]

                    ]

                ]);


            /*
            |--------------------------------------------------------------------------
            | ALIGNMENT NOMOR
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "A{$dataStartRow}:A{$dataEndRow}"
                )
                ->getAlignment()
                ->setHorizontal(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | ALIGNMENT KOLOM LAIN
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "C{$dataStartRow}:G{$dataEndRow}"
                )
                ->getAlignment()
                ->setHorizontal(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                );

        }


        /*
        |--------------------------------------------------------------------------
        | CATATAN
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells(
            "A{$noteRow}:G{$noteRow}"
        );


        $sheet
            ->getStyle(
                "A{$noteRow}:G{$noteRow}"
            )
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
            );


        /*
        |--------------------------------------------------------------------------
        | LEBAR KOLOM MANUAL
        |--------------------------------------------------------------------------
        |
        | Jangan gunakan AutoSize.
        | Catatan panjang sudah di-merge A:G.
        |
        */

        $sheet
            ->getColumnDimension('A')
            ->setWidth(8);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(24);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(24);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(24);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(18);


        return [];
    }
}
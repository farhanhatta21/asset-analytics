<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use App\Services\AnalysisService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceSheetExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
    {
    protected $request;
    protected $service;

    public function __construct($request, AnalysisService $service)
    {
        $this->request = $request;
        $this->service = $service;
    }

    public function title(): string
    {
        return 'Performance';
    }

    public function array(): array
    {
        $query = DB::table('assets');

        // FILTER PERIODE RANGE
        if ($this->request['periode_awal']) {
            $query->where('periode', '>=', $this->request['periode_awal']);
        }

        if ($this->request['periode_akhir']) {
            $query->where('periode', '<=', $this->request['periode_akhir']);
        }

        // FILTER ALAT
        if ($this->request['alat']) {
            $query->where('nama_alat', $this->request['alat']);
        }

        $raw = $query
            ->orderBy('periode')
            ->get();

        $dataArray = array_map(fn($i) => (array)$i, $raw->toArray());

        // NORMALISASI
        $mtbf_max = max($raw->pluck('mtbf')->toArray() ?: [1]);
        $mttrp_max = max($raw->pluck('mttrp')->toArray() ?: [1]);

        $analysis = $this->service->analyze(
            $dataArray,
            $mtbf_max,
            $mttrp_max
        );

        $results = $analysis['priority_tools'];

        // PARAMETER YANG DIPILIH USER
        $params = $this->request->params ?? [];

        // HEADER
        $header = [
            'No',
            'Nama Alat',
            'Periode'
        ];

        foreach ($params as $param) {

            $header[] = ucfirst(str_replace('_', ' ', $param));

        }

        $header[] = 'Status';

        $data = [];

        // IDENTITAS PERUSAHAAN
        $data[] = [
            'PT Pelabuhan Indonesia (Persero) Regional 4'
        ];

        $data[] = [
            'Laporan Performance Alat Operasional'
        ];

        $data[] = [
            'Tanggal Export : ' . now()->format('d M Y H:i')
        ];

        $data[] = [];

        // HEADER TABLE
        $data[] = $header;

        // ISI DATA
        foreach ($results as $i => $item) {

            $row = [
                $i + 1,
                $item['nama'],
                $item['periode'] ?? '-'
            ];

            foreach ($params as $param) {

                switch ($param) {

                    case 'availability':
                        $row[] = round(($item[availability] ?? 0) * 100, 2) . '%';
                        break;

                    case 'utilisation':
                        $row[] = round(($item-[utilisation] ?? 0) * 100, 2) . '%';
                        break;

                    case 'mtbf':
                        $row[] = round($item[mtbf] ?? 0, 2);
                        break;

                    case 'mttrp':
                        $row[] = round($item[mttrp] ?? 0, 2);
                        break;

                    case 'health_score':
                        $row[] = round($item[health_score] ?? 0, 2);
                        break;

                }

            }

            $row[] = $item['status'];

            $data[] = $row;
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // TITLE COMPANY
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');

        // COMPANY STYLE
        $sheet->getStyle('A1:A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ]
        ]);

        // HEADER TABLE
        $headerRow = 5;

        $sheet->getStyle("A{$headerRow}:Z{$headerRow}")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ]
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'rgb' => '1E40AF'
                    ]
                ]
            ]);

        // FREEZE HEADER
        $sheet->freezePane('A6');

        // BORDER TABLE
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A5:Z{$lastRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => [
                            'rgb' => 'D1D5DB'
                        ]
                    ]
                ]
            ]);

        // ALIGNMENT
        $sheet->getStyle("A1:Z{$lastRow}")
            ->getAlignment()
            ->setVertical('center');

        return [];
    }
}
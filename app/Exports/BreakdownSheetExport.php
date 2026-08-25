<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BreakdownSheetExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Breakdown';
    }

    public function array(): array
    {
        $query = DB::table('breakdown_logs');

        /* Filter Periode */
        if (!empty($this->request['periode_awal'])) {
            $query->where(
                'periode',
                '>=',
                $this->request['periode_awal']
            );
        }

        if (!empty($this->request['periode_akhir'])) {
            $query->where(
                'periode',
                '<=',
                $this->request['periode_akhir']
            );
        }

        /* Filter Alat */
        if (!empty($this->request['alat'])) {
            $query->where(
                'group_alat',
                $this->request['alat']
            );
        }

        $rows = $query
            ->orderBy('periode')
            ->orderBy('nama_alat')
            ->orderBy('start_bd')
            ->get();

        $totalBreakdown = $rows->count();

        $totalDowntime = round(
            $rows->sum('durasi_bd'),
            2
        );

        /* Informasi Header Laporan */
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

        $data = [];

        /* Header */
        $data[] = [
            'PT Pelabuhan Indonesia (Persero) Regional 4'
        ];

        $data[] = [
            'BREAKDOWN HISTORY'
        ];

        $data[] = [
            'Tanggal Export : ' . \Carbon\Carbon::now('Asia/Makassar')
                ->translatedFormat('d F Y H:i')
        ];

        $data[] = [
            'Periode : '.$periodeText
        ];

        /* Summary */
        $data[] = [
            'Total Breakdown', $totalBreakdown.' kejadian'
        ];

        $data[] = [
            'Total Downtime', $totalDowntime.' jam'
        ];

        /* Spasi */
        $data[] = [];
        $data[] = [];

        /* Header Tabel */
        $data[] = [
            'No',
            'Periode',
            'Nama Alat',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi BD',
            'Part Bermasalah',
            'Detail Kerusakan',
            'Detail Penyebab',
            'Detail Tindakan',
            'Kendala'
        ];

        foreach ($rows as $i => $row) {
            $data[] = [
                $i + 1,
                $row->periode,
                $row->nama_alat,
                $row->start_bd,
                $row->finish_bd,
                $row->durasi_bd,
                $row->part_group,
                $row->detail_kerusakan,
                $row->detail_penyebab,
                $row->detail_tindakan,
                $row->kendala
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');
        $sheet->mergeCells('A4:K4');

        // TITLE STYLE
        $sheet->getStyle('A1')->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet->getStyle('A2')->getFont()
            ->setBold(true)
            ->setSize(14);

        $sheet->getStyle('A3')->getFont()
            ->setBold(false)
            ->setSize(11);

        $sheet->getStyle('A4')->getFont()
            ->setBold(false)
            ->setSize(11);

        $sheet->getStyle('A5')->getFont()
            ->setBold(false)
            ->setSize(11);

        $sheet->getStyle('A6')->getFont()
            ->setBold(false)
            ->setSize(11);

        // HEADER TABLE
        $headerRow = 7;

        $sheet->getStyle("A{$headerRow}:K{$headerRow}")
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
                        'rgb' => 'DC2626'
                    ]
                ]
            ]);

            $sheet->getStyle("A{$headerRow}:K{$headerRow}")
                ->getAlignment()
                ->setHorizontal(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                );

            $sheet->getStyle("A{$headerRow}:K{$headerRow}")
                ->getAlignment()
                ->setVertical(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                );

        // BORDER
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A7:K{$lastRow}")
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
        
        // alignment vertical
        $sheet
            ->getStyle("A8:K{$lastRow}")
            ->getAlignment()
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
            );

        // alignment horizontal
        $sheet
            ->getStyle("A8:B{$lastRow}")
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            );
        
        $sheet
            ->getStyle("D8:F{$lastRow}")
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            );

        // Nama Alat dan kolom teks kerusakan tetap rata kiri
        $sheet
            ->getStyle("C8:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
            );

        $sheet
            ->getStyle("G8:K{$lastRow}")
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
            );

        return [];
    }
}
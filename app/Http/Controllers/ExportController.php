<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AnalysisService;
use App\Services\InsightGeneratorService;
use App\Models\Prediction;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\MonitoringExportService;
use App\Exports\AssetExport;

class ExportController extends Controller
{
    
    public  function buildDetailData($nama, AnalysisService $service, InsightGeneratorService $insightService)
    {
        // Ambil seluruh histori asset
        $data = DB::table('assets')
            ->where('nama_alat', $nama)
            ->orderBy('periode')
            ->get();

        if ($data->isEmpty()) {
            return null;
        }

        // Konversi Collection menjadi Array
        $dataArray = array_map(function ($item) {
            return (array) $item;
        }, $data->toArray());

        // Ambil seluruh data asset (untuk normalisasi)
        $allAssets = DB::table('assets')->get();

        $mtbf_max = max(
            $allAssets->pluck('mtbf')->toArray() ?: [1]
        );

        $mttrp_max = max(
            $allAssets->pluck('mttrp')->toArray() ?: [1]
        );

        // Latest Breakdown
        $latestBreakdown = DB::table('breakdown_logs')
            ->where('nama_alat', $nama)
            ->orderByDesc('start_bd')
            ->first();

        // Breakdown History
        $breakdownHistory = DB::table('breakdown_logs')
            ->where('nama_alat', $nama)
            ->orderByDesc('start_bd')
            ->select(
                'start_bd',
                'durasi_bd',
                'part_group',
                'detail_kerusakan',
                'detail_penyebab',
                'detail_tindakan',
                'kendala',
                'keterangan'
            )
            ->get();

        // Top Problem Parts
        $topProblemParts = DB::table('breakdown_logs')
            ->where('nama_alat', $nama)
            ->select(
                'part_group',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('part_group')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Trend Health Score
        $trend = [];
        $historyData = [];

        foreach ($dataArray as $item) {
            $result = $service->analyze(
                [$item],
                $mtbf_max,
                $mttrp_max
            );

            $score = $result['priority_tools'][0] ?? [];

            $trend[] = [
                'periode' => $item['periode'],
                'health_score' => $score['health_score'] ?? 0,
                'availability' => $item['availability'],
                'utilisation' => $item['utilisation'],
                'mtbf' => $item['mtbf'],
                'mttrp' => $item['mttrp'],
            ];

            $historyData[] = (object)[
                'periode'      => $item['periode'],
                'availability' => $item['availability'],
                'utilisation'  => $item['utilisation'],
                'mtbf'         => $item['mtbf'],
                'mttrp'         => $item['mttrp'],
                'health_score' => round(
                    $score['health_score'] ?? 0,
                    2
                ),

                'priority' => round(
                    $score['priority'] ?? 0,
                    2
                ),

                'status' => $score['status'] ?? '-'

            ];
        }

        $historyData = collect($historyData)

            ->sortByDesc('periode')

            ->values();

        // Trend Status
        $first = $trend[0]['health_score'] ?? 0;

        $last = !empty($trend)
            ? end($trend)['health_score']
            : 0;

        if ($last > $first) {

            $trendStatus = 'improving';

        } elseif ($last < $first) {

            $trendStatus = 'declining';

        } else {

            $trendStatus = 'stable';

        }

        // Latest Analysis
        $latestAnalysis =
            $service->calculateAssetScore(
                $dataArray,
                $mtbf_max,
                $mttrp_max
            );

        // Prediction
        $prediction = Prediction::where(
            'nama_alat',
            $nama

        )->first();
        $insight = null;
        if ($prediction) {
            $insightAsset =
                $insightService->buildInsightAsset(
                    $nama
                );

            if ($insightAsset) {
                $insight =
                    $insightService->generateInsight(
                        $insightAsset,
                        $prediction->predicted_health_score
                    );
            }
        }

        // Status Color
        $statusColor = [
            'bg' => 'bg-slate-50',
            'border' => 'border-slate-200',
            'text' => 'text-slate-700'
        ];

        if ($insight) {
            $statusColor = match (
                $insight['status']['status']
            ) {

                'Sehat' => [
                    'bg' => 'bg-green-50',
                    'border' => 'border-green-200',
                    'text' => 'text-green-700'
                ],

                'Kurang Sehat' => [
                    'bg' => 'bg-yellow-50',
                    'border' => 'border-yellow-200',
                    'text' => 'text-yellow-700'
                ],

                default => [
                    'bg' => 'bg-red-50',
                    'border' => 'border-red-200',
                    'text' => 'text-red-700'
                ]

            };
        }

        return [
            'data' => $historyData,
            'latestBreakdown' => $latestBreakdown,
            'breakdownHistory' => $breakdownHistory,
            'topProblemParts' => $topProblemParts,
            'trend' => $trend,
            'trendStatus' => $trendStatus,
            'latest' => $latestAnalysis,
            'prediction' => $prediction,
            'insight' => $insight,
            'statusColor' => $statusColor,
        ];
    }

    public function exportPDF(Request $request, AnalysisService $service, MonitoringExportService $monitoringService)
    {
        // VALIDASI RENTANG PERIODE
        if (!$request->filled('periode_awal') || !$request->filled('periode_akhir')) {
            return back()->with(
                'error',
                'Silakan pilih rentang periode laporan (Periode Dari dan Periode Sampai) terlebih dahulu sebelum melakukan export PDF.'
            );
        }

        if ($request->periode_awal > $request->periode_akhir) {
            return back()->with(
                'error',
                'Periode awal ("Dari") tidak boleh lebih besar dari periode akhir ("Sampai").'
            );
        }

        //DATA MONITORING
        $rows = $monitoringService
            ->getMonitoringData($request->all());

        if ($rows->isEmpty()) {
            return back()->with(
                'error',
                'Tidak ada data aset yang ditemukan untuk filter periode yang dipilih.'
            );
        }

        // SUMMARY
        $summary = $monitoringService
            ->getSummary($rows);

        $breakdownSummary = $monitoringService
            ->getBreakdownSummary(
                $request->all()
            );

        $predictionRows = $monitoringService
            ->getPredictionData(
                $request->all()
            );

        // JUDUL
        $title = 'Laporan Monitoring Kinerja Alat Operasional';

        $company =
            'PT Pelabuhan Indonesia (Persero) Regional 4';

        // FILTER PERIODE
        $periode =
            $request->periode_awal .
            ' s.d. ' .
            $request->periode_akhir;

        $groupAlat =
            $request->alat ?: 'Seluruh Kelompok Alat';

        $predictionPeriod = optional(
            $predictionRows->first()
        )->prediction_period ?? ($summary['prediction_period'] ?? '-');

        // PDF
        $pdf = Pdf::loadView(
            'exports.pdf',
            [
                'rows' => $rows,
                'summary' => $summary,
                'breakdownSummary'=> $breakdownSummary,
                'prediction_period' => $predictionPeriod,
                'predictionRows' => $predictionRows,
                'recommendations' =>
                    $summary['recommendations'] ?? [],
                'title' => $title,
                'company' => $company,
                'periode' => $periode,
                'group_alat' => $groupAlat,
                'export_date' => now()
            ]
        );

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download(
            'Monitoring_Report_' .now()->format('Ymd_His') .'.pdf'
        );
    }


    public function exportExcel(Request $request)
    {
        // VALIDASI RENTANG PERIODE
        if (!$request->filled('periode_awal') || !$request->filled('periode_akhir')) {
            return back()->with(
                'error',
                'Silakan pilih rentang periode laporan (Periode Dari dan Periode Sampai) terlebih dahulu sebelum melakukan export Excel.'
            );
        }

        if ($request->periode_awal > $request->periode_akhir) {
            return back()->with(
                'error',
                'Periode awal ("Dari") tidak boleh lebih besar dari periode akhir ("Sampai").'
            );
        }

        return Excel::download(
            new AssetExport(
                $request->all()
            ),
            'Asset_Monitoring_Report.xlsx'
        );
    }

    public function exportDetailPDF(
        $nama,
        AnalysisService $service,
        InsightGeneratorService $insightService
    )
    {
        $detailData = $this->buildDetailData(
            $nama,
            $service,
            $insightService
        );

        if (!$detailData) {
            abort(404, 'Data tidak ditemukan');
        }

        $pdf = Pdf::loadView(
            'exports.detail_pdf',
            array_merge(
                $detailData,
                [
                    'nama' => $nama
                ]
            )
        );

        return $pdf->download(
            'Detail_Asset_'.$nama.'.pdf'
        );
    }

    

    private function buildAssetAnalysis($nama, AnalysisService $service, InsightGeneratorService $insightService)
    {
        // DATA ASSET
        $data = DB::table('assets')
            ->where('nama_alat', $nama)
            ->orderBy('periode')
            ->get();

        if ($data->isEmpty()) {
            return null;
        }

        $dataArray = array_map(function ($item) {
            return (array) $item;
        }, $data->toArray());

        $allAssets = DB::table('assets')->get();

        $mtbf_max = max($allAssets->pluck('mtbf')->toArray() ?: [1]);
        $mttrp_max = max($allAssets->pluck('mttrp')->toArray() ?: [1]);

        // TREND
        $trend = [];

        foreach ($dataArray as $item) {

            $result = $service->analyze(
                [$item],
                $mtbf_max,
                $mttrp_max
            );

            $trend[] = [

                'periode'=>$item['periode'],

                'health_score'=>$result['priority_tools'][0]['health_score'] ?? 0,

                'availability'=>$item['availability'],

                'utilisation'=>$item['utilisation'],

                'mtbf'=>$item['mtbf'],

                'mttrp'=>$item['mttrp']

            ];
        }

        $latestAnalysis = $service->calculateAssetScore(
            $dataArray,
            $mtbf_max,
            $mttrp_max
        );
        // BREAKDOWN
        $latestBreakdown = DB::table('breakdown_logs')
            ->where('nama_alat',$nama)
            ->orderByDesc('start_bd')
            ->first();

        $breakdownHistory = DB::table('breakdown_logs')
            ->where('nama_alat',$nama)
            ->orderByDesc('start_bd')
            ->get();

        $topProblemParts = DB::table('breakdown_logs')
            ->where('nama_alat',$nama)
            ->select(
                'part_group',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('part_group')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // PREDICTION
        $prediction = Prediction::where(
            'nama_alat',
            $nama
        )->first();

        $insight = null;

        if ($prediction) {

            $asset = $insightService->buildInsightAsset($nama);

            if ($asset) {

                $insight = $insightService->generateInsight(
                    $asset,
                    $prediction->predicted_health_score
                );
            }
        }

        return [
            'data'=>$data,
            'trend'=>$trend,
            'latest'=>$latestAnalysis,
            'latestBreakdown'=>$latestBreakdown,
            'breakdownHistory'=>$breakdownHistory,
            'topProblemParts'=>$topProblemParts,
            'prediction'=>$prediction,
            'insight'=>$insight
        ];
    }

    private function generateAssetInsight(
        string $nama,
        InsightGeneratorService $insightService
    )
    {
        $prediction = Prediction::where(
            'nama_alat',
            $nama
        )->first();

        if (!$prediction) {
            return [null, null];
        }

        $asset =
            $insightService->buildInsightAsset($nama);

        if (!$asset) {
            return [$prediction, null];
        }

        return [

            $prediction,

            $insightService->generateInsight(
                $asset,
                $prediction->predicted_health_score
            )

        ];
    }

}
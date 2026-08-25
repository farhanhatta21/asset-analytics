<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\AnalysisService;
use Carbon\Carbon;

class MonitoringExportService
{
    protected AnalysisService $analysisService;

    public function __construct(
        AnalysisService $analysisService
    ){
        $this->analysisService = $analysisService;
    }

    public function getMonitoringData(array $request)
    {
        $query = DB::table('assets')
            ->leftJoin(
                'breakdown_logs',
                function ($join) {

                    $join->on(
                        'assets.nama_alat',
                        '=',
                        'breakdown_logs.nama_alat'
                    );

                    $join->on(
                        'assets.periode',
                        '=',
                        'breakdown_logs.periode'
                    );

                }
            );

        /* FILTER PERIODE */
        if (!empty($request['periode_awal'])) {

            $query->where(
                'assets.periode',
                '>=',
                $request['periode_awal']
            );

        }

        if (!empty($request['periode_akhir'])) {
            $query->where(
                'assets.periode',
                '<=',
                $request['periode_akhir']
            );
        }

        /* FILTER KELOMPOK ALAT */
        if (!empty($request['alat'])) {
            $query->where(
                'assets.group_alat',
                $request['alat']
            );
        }

        $rows = $query
            ->select(
                'assets.nama_alat',
                'assets.group_alat',
                'assets.periode',

                'assets.availability',
                'assets.utilisation',
                'assets.mtbf',
                'assets.mttrp',

                DB::raw(
                    'COUNT(DISTINCT breakdown_logs.id) as total_bd'
                ),

                DB::raw(
                    'COALESCE(
                        SUM(breakdown_logs.durasi_bd),
                        0
                    ) as total_downtime'
                ),

                DB::raw(
                    'MAX(breakdown_logs.part_group)
                    as dominant_part'
                )

            )

            ->groupBy(
                'assets.nama_alat',
                'assets.group_alat',
                'assets.periode',
                'assets.availability',
                'assets.utilisation',
                'assets.mtbf',
                'assets.mttrp'
            )

            ->orderBy('assets.group_alat')
            ->orderBy('assets.nama_alat')
            ->orderBy('assets.periode')

            ->get();

        /* NORMALISASI GLOBAL  */
        $allAssets = DB::table('assets')->get();

        $mtbfMax = max(
            $allAssets
                ->pluck('mtbf')
                ->filter()
                ->toArray() ?: [1]
        );

        $mttrpMax = max(
            $allAssets
                ->pluck('mttrp')
                ->filter()
                ->toArray() ?: [1]
        );

        /* ANALISIS */
        foreach ($rows as $row) {
            $result = $this->analysisService->analyze(
                [[
                    'nama_alat' =>
                        $row->nama_alat,

                    'availability' =>
                        $row->availability,

                    'utilisation' =>
                        $row->utilisation,

                    'mtbf' =>
                        $row->mtbf,

                    'mttrp' =>
                        $row->mttrp,

                ]],

                $mtbfMax,
                $mttrpMax

            );

            $analysis =
                $result['priority_tools'][0] ?? [];

            $row->health_score =
                $analysis['health_score'] ?? 0;

            $row->status =
                $analysis['status'] ?? '-';

            $row->priority =
                $analysis['priority'] ?? 0;

        }

        return $rows;
    }

    public function getSummary($rows)
    {
        // Ambil data periode terakhir setiap alat
        $latestRows = $rows
            ->sortByDesc('periode')
            ->unique('nama_alat')
            ->values();

        // PERIODE PREDIKSI
        $predictionSource = DB::table('predictions')
            ->max('prediction_period');

        $predictionPeriod = '-';

        if ($predictionSource) {
            try {
                $predictionPeriod = Carbon::createFromFormat(
                    'Y-m',
                    substr($predictionSource, 0, 7)
                )->translatedFormat('F Y');
            } catch (\Throwable $e) {
                $predictionPeriod = $predictionSource;
            }
        }

        // TOP PRIORITAS
        $highestPriority = $rows
            ->sortByDesc('priority')
            ->first();

        // DOMINANT PART
        $dominantPart = $rows
            ->pluck('dominant_part')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        // REKOMENDASI OTOMATIS
        $recommendations = [];

        if ($highestPriority) {
            $recommendations[] =
                "Prioritaskan maintenance pada alat {$highestPriority->nama_alat} karena memiliki Priority Score tertinggi.";
        }

        if ($dominantPart) {
            $recommendations[] =
                "Komponen yang paling sering mengalami breakdown adalah {$dominantPart}. Disarankan meningkatkan inspeksi preventif pada komponen tersebut.";

        }

        $criticalCount = $latestRows
            ->where('health_score', '<', 70)
            ->count();

        $warningCount = $latestRows
            ->whereBetween('health_score', [70, 84.99])
            ->count();

        if ($criticalCount > 0) {

            $recommendations[] =
                "Terdapat {$criticalCount} aset dengan status Tidak Sehat sehingga perlu diprioritaskan untuk tindakan maintenance.";

        }

        if ($warningCount > 0) {

            $recommendations[] =
                "Terdapat {$warningCount} aset yang memerlukan perhatian dan direkomendasikan untuk dilakukan monitoring lebih lanjut.";

        }

        if ($criticalCount == 0 && $warningCount == 0) {

            $recommendations[] =
                "Seluruh aset berada pada kategori Sehat berdasarkan hasil analisis sistem.";

        }

        $avgHealthScore = round((float) ($latestRows->avg('health_score') ?? 0), 2);
        $avgRiskScore = round((float) max(0, 100 - $avgHealthScore), 2);

        return [
            'total_asset'=>
            $latestRows->count(),

            'healthy' => $latestRows->where(
                'health_score',
                '>=',
                85
            )->count(),

            'warning' => $latestRows->whereBetween(
                'health_score',
                [70,84.99]
            )->count(),

            'critical' => $latestRows->where(
                'health_score',
                '<',
                70
            )->count(),

            'average_health_score' => $avgHealthScore,

            'average_risk_score' => $avgRiskScore,

            'total_breakdown' => (int) $rows->sum('total_bd'),

            'total_downtime' => round(
                (float) ($rows->sum('total_downtime') ?? 0),
                2
            ),

            'prediction_period' => $predictionPeriod,

            'recommendations' => $recommendations,

            'highest_priority_asset' => $highestPriority,
        ];
    }

    public function buildPdfReport(
        array $request,
        AnalysisService $analysisService
    )
    {
        /* FILTER */
        $periodeAwal = $request['periode_awal'] ?? null;
        $periodeAkhir = $request['periode_akhir'] ?? null;
        $groupAlat = $request['alat'] ?? null;

        /* DATA ASSET */
        $assetQuery = DB::table('assets');

        if ($periodeAwal) {
            $assetQuery->where(
                'periode',
                '>=',
                $periodeAwal
            );
        }

        if ($periodeAkhir) {
            $assetQuery->where(
                'periode',
                '<=',
                $periodeAkhir
            );
        }

        if ($groupAlat) {
            $assetQuery->where(
                'group_alat',
                $groupAlat
            );
        }

        $assets = $assetQuery
            ->orderBy('periode')
            ->orderBy('nama_alat')
            ->get();

        if ($assets->isEmpty()) {
            return null;
        }

        /* NORMALISASI GLOBAL */
        $allAssets = DB::table('assets')->get();

        $mtbfMax = max(
            $allAssets->pluck('mtbf')->toArray() ?: [1]
        );

        $mttrpMax = max(
            $allAssets->pluck('mttrp')->toArray() ?: [1]
        );

        /* MONITORING DATA */
        // Statistik breakdown per alat
        $breakdownSummary = DB::table('breakdown_logs')
            ->select(
                'nama_alat',
                DB::raw('COUNT(*) as total_breakdown'),
                DB::raw('SUM(durasi_bd) as total_downtime'),
                DB::raw('AVG(durasi_bd) as avg_downtime')
            )

            ->when($periodeAwal, function ($q) use ($periodeAwal) {
                return $q->where('periode', '>=', $periodeAwal);
            })

            ->when($periodeAkhir, function ($q) use ($periodeAkhir) {
                return $q->where('periode', '<=', $periodeAkhir);
            })

            ->when($groupAlat, function ($q) use ($groupAlat) {
                return $q->where('group_alat', $groupAlat);
            })

            ->groupBy('nama_alat')
            ->get()
            ->keyBy('nama_alat');

        // Prediction Result
        $predictions = DB::table('predictions')
            ->get()
            ->keyBy('nama_alat');

        // Kelompokkan asset berdasarkan nama alat
        $groupedAssets = $assets->groupBy('nama_alat');
        $monitoringData = [];

        foreach ($groupedAssets as $namaAlat => $items) {
            $itemsArray = array_map(
                fn($i) => (array) $i,
                $items->toArray()
            );

            $avgAvailability = collect($itemsArray)->avg('availability');
            $avgUtilisation = collect($itemsArray)->avg('utilisation');
            $avgMtbf = collect($itemsArray)->avg('mtbf');
            $avgMttrp = collect($itemsArray)->avg('mttrp');

            $asset = [
                'nama_alat' => $namaAlat,
                'availability' => $avgAvailability,
                'utilisation' => $avgUtilisation,
                'mtbf' => $avgMtbf,
                'mttrp' => $avgMttrp,
            ];

            $analysis = $analysisService->analyze([$asset], $mtbfMax, $mttrpMax);

            if (empty($analysis['priority_tools'])) {
                continue;
            }
            
            $tool = $analysis['priority_tools'][0];

            $prediction = $predictions[$namaAlat] ?? null;

            $tool['predicted_health_score'] =
                $prediction->predicted_health_score ?? null;

            $tool['maintenance_risk_score'] =
                $prediction->maintenance_risk_score ?? null;

            $tool['prediction_period'] =
                $prediction->prediction_period ?? null;

            $bd = $breakdownSummary[$namaAlat] ?? null;

            $tool['total_breakdown'] =
                $bd->total_breakdown ?? 0;

            $tool['total_downtime'] =
                round($bd->total_downtime ?? 0, 2);

            $tool['avg_downtime'] =
                round($bd->avg_downtime ?? 0, 2);

            $monitoringData[] = $tool;

        }

        usort($monitoringData, fn($a, $b) => $b['priority'] <=> $a['priority']);

        /* SUMMARY REPORT */
        $totalAsset = count($monitoringData);

        $averageHealthScore = round(
            collect($monitoringData)
                ->avg('health_score'), 2
        );

        $totalBreakdown = DB::table('breakdown_logs')

            ->when($periodeAwal, function ($q) use ($periodeAwal) {
                return $q->where('periode', '>=', $periodeAwal);
            })

            ->when($periodeAkhir, function ($q) use ($periodeAkhir) {
                return $q->where('periode', '<=', $periodeAkhir);
            })

            ->when($groupAlat, function ($q) use ($groupAlat) {
                return $q->where('group_alat', $groupAlat);
            })

            ->count();

        $totalDowntime = round(
            DB::table('breakdown_logs')
                ->when($periodeAwal, function ($q) use ($periodeAwal) {
                    return $q->where('periode', '>=', $periodeAwal);
                })

                ->when($periodeAkhir, function ($q) use ($periodeAkhir) {
                    return $q->where('periode', '<=', $periodeAkhir);
                })

                ->when($groupAlat, function ($q) use ($groupAlat) {
                    return $q->where('group_alat', $groupAlat);
                })

                ->sum('durasi_bd'), 2
        );

        $healthy = collect($monitoringData)
            ->where('status', 'Sehat')
            ->count();

        $warning = collect($monitoringData)
            ->where('status', 'Kurang Sehat')
            ->count();

        $critical = collect($monitoringData)
            ->where('status', 'Tidak Sehat')
            ->count();
        
        /* RINGKASAN ANALISIS */
        if ($averageHealthScore >= 85) {
            $analysisSummary =
                'Secara umum kondisi alat operasional berada pada kategori Sehat. Aktivitas operasional berjalan dengan baik dan hanya diperlukan monitoring rutin.';
        }

        elseif ($averageHealthScore >= 70) {
            $analysisSummary =
                'Sebagian besar alat berada pada kondisi Kurang Sehat. Diperlukan monitoring yang lebih intensif dan preventive maintenance pada alat prioritas.';
        }

        else {
            $analysisSummary =
                'Kondisi alat operasional secara umum berada pada kategori Tidak Sehat. Direkomendasikan segera dilakukan evaluasi dan tindakan maintenance terhadap alat prioritas.';
        }

        /* PERIODE PREDIKSI */
        $latestPredictionPeriod = DB::table('predictions')
            ->max('prediction_period');

        $predictionPeriod = null;

        if ($latestPredictionPeriod) {
            $predictionPeriod = Carbon::createFromFormat('Y-m', $latestPredictionPeriod)
            ->addMonth()
            ->format('F Y');
        }

        /* BREAKDOWN SUMMARY */
        $topBreakdownAsset = DB::table('breakdown_logs')
            ->select(
                'nama_alat',

                DB::raw('COUNT(*) as total')
            )

            ->when($periodeAwal, function ($q) use ($periodeAwal) {
                return $q->where('periode','>=',$periodeAwal);
            })

            ->when($periodeAkhir, function ($q) use ($periodeAkhir) {
                return $q->where('periode','<=',$periodeAkhir);
            })

            ->when($groupAlat, function ($q) use ($groupAlat) {
                return $q->where('group_alat',$groupAlat);
            })

            ->groupBy('nama_alat')
            ->orderByDesc('total')
            ->first();

        $topProblemPart = DB::table('breakdown_logs')
            ->select(
                'part_group',

                DB::raw('COUNT(*) as total')
            )

            ->whereNotNull('part_group')

            ->when($periodeAwal, function ($q) use ($periodeAwal) {
                return $q->where('periode','>=',$periodeAwal);
            })

            ->when($periodeAkhir, function ($q) use ($periodeAkhir) {
                return $q->where('periode','<=',$periodeAkhir);
            })

            ->when($groupAlat, function ($q) use ($groupAlat) {
                return $q->where('group_alat',$groupAlat);
            })

            ->groupBy('part_group')
            ->orderByDesc('total')
            ->first();

        /* BREAKDOWN HISTORY */
        $breakdownHistory = DB::table('breakdown_logs')
            ->select(
                'nama_alat',
                'start_bd',
                'finish_bd',
                'durasi_bd',
                'part_group',
                'detail_penyebab'
            )

            ->when($periodeAwal,function($q) use($periodeAwal){
                return $q->where('periode','>=',$periodeAwal);
            })

            ->when($periodeAkhir,function($q) use($periodeAkhir){
                return $q->where('periode','<=',$periodeAkhir);
            })

            ->when($groupAlat,function($q) use($groupAlat){
                return $q->where('group_alat',$groupAlat);
            })

            ->orderByDesc('start_bd')
            ->get();

        /* REKOMENDASI SISTEM */
        $systemRecommendation = [];

        if ($critical > 0) {
            $systemRecommendation[] =
                'Prioritaskan maintenance pada aset dengan status Tidak Sehat.';
        }

        if ($warning > 0) {
            $systemRecommendation[] =
                'Tingkatkan preventive maintenance untuk aset kategori Kurang Sehat.';
        }

        if ($totalBreakdown > 10) {
            $systemRecommendation[] =
                'Evaluasi penyebab breakdown berulang dan lakukan tindakan korektif.';
        }

        if ($averageHealthScore >= 85) {
            $systemRecommendation[] =
                'Pertahankan program maintenance yang berjalan serta lakukan monitoring berkala.';
        }

        if (empty($systemRecommendation)) {
            $systemRecommendation[] =
                'Kondisi sistem stabil. Lanjutkan monitoring rutin terhadap seluruh alat operasional.';
        }

        /* INFORMASI COVER */
        $reportPeriod = 'Semua Periode';

        if ($periodeAwal && $periodeAkhir) {
            $reportPeriod =
                Carbon::createFromFormat(
                    'Y-m',
                    $periodeAwal
                )->translatedFormat('F Y') . ' - ' .
                Carbon::createFromFormat(
                    'Y-m',
                    $periodeAkhir
                )->translatedFormat('F Y');

        }

        elseif ($periodeAwal) {
            $reportPeriod =
                Carbon::createFromFormat(
                    'Y-m',
                    $periodeAwal
                )->translatedFormat('F Y');
        }

        $groupName = $groupAlat ?: 'Semua Alat';

        /*  RETURN PDF */
        return [
            'company' =>
                'PT. Pelabuhan Indonesia (Persero) Regional 4',

            'title' =>
                'Laporan Monitoring Kinerja Alat Operasional Pelabuhan',

            'export_date' => now(),

            'periode' => $reportPeriod,

            'group_alat' => $groupName,

            /* SUMMARY */
            'summary' => [
                'total_asset' => $totalAsset,
                'total_breakdown' => $totalBreakdown,
                'total_downtime' => $totalDowntime,
                'healthy' => $healthy,
                'warning' => $warning,
                'critical' => $critical,
                'average_health_score' => $averageHealthScore,
            ],

            /* ANALISIS */
            'analysis_summary' =>
                $analysisSummary,

            /*  MONITORING */
            'monitoring' =>
                $monitoringData,

            /* BREAKDOWN */
            'top_breakdown_asset' =>
                $topBreakdownAsset,

            'top_problem_part' =>
                $topProblemPart,

            'breakdown_history' =>
                $breakdownHistory,

            /* PREDIKSI */
            'prediction_period' =>
                $predictionPeriod,

            /* REKOMENDASI */
            'recommendations' =>
                $systemRecommendation

        ];


        
    }

    public function getBreakdownSummary(array $request)
    {
        $query = DB::table('breakdown_logs');

        if (!empty($request['periode_awal'])) {
            $query->where(
                'periode',
                '>=',
                $request['periode_awal']
            );
        }

        if (!empty($request['periode_akhir'])) {
            $query->where(
                'periode',
                '<=',
                $request['periode_akhir']
            );
        }

        if (!empty($request['alat'])) {
            $query->where(
                'group_alat',
                $request['alat']
            );
        }

        $breakdowns = $query->get();

        return [
            'total_breakdown' =>
                $breakdowns->count(),

            'total_downtime' =>
                round(
                    (float) ($breakdowns->sum('durasi_bd') ?? 0), 2),

            'average_downtime' =>
                round(
                    (float) ($breakdowns->avg('durasi_bd') ?? 0), 2),

            'top_problem_parts' =>
                DB::table('breakdown_logs')
                    ->select(
                        'part_group',
                        DB::raw('COUNT(*) as total')
                    )

                    ->when(
                        !empty($request['periode_awal']),

                        function ($query) use ($request) {
                            $query->where(
                                'periode',
                                '>=',
                                $request['periode_awal']
                            );
                        }
                    )

                    ->when(
                        !empty($request['periode_akhir']),

                        function ($query) use ($request) {
                            $query->where(
                                'periode',
                                '<=',
                                $request['periode_akhir']
                            );
                        }
                    )

                    ->when(
                        !empty($request['alat']),

                        function ($query) use ($request) {
                            $query->where(
                                'group_alat',
                                $request['alat']
                            );
                        }
                    )
                    ->whereNotNull('part_group')
                    ->where('part_group', '!=', '')
                    ->groupBy('part_group')

                    ->orderByDesc('total')

                    ->limit(5)

                    ->get()
        ];

    }

    public function getPredictionData(array $request)
    {
        $query = DB::table('predictions')
            ->join(
                'assets',
                'predictions.nama_alat',
                '=',
                'assets.nama_alat'
            )
            ->select(
                'predictions.nama_alat',
                'assets.group_alat',
                'predictions.last_period',
                'predictions.prediction_period',
                'predictions.predicted_health_score',
                'predictions.maintenance_risk_score'
            )
            ->distinct();

        /* FILTER KELOMPOK ALAT */
        if (!empty($request['alat'])) {

            $query->where(
                'assets.group_alat',
                $request['alat']
            );
        }

        /* AMBIL DATA PREDICTION */
        $rows = $query
            ->orderBy('assets.group_alat')
            ->orderBy('predictions.nama_alat')
            ->get();

        /* STATUS PREDIKSI */
        foreach ($rows as $row) {
            $score = (float) ($row->predicted_health_score ?? 0);
            if ($score >= 85) {

                $row->prediction_status = 'Sehat';

            } elseif ($score >= 70) {

                $row->prediction_status = 'Kurang Sehat';

            } else {

                $row->prediction_status = 'Tidak Sehat';
            }

            $row->prediction_period =
                $row->prediction_period ?? '-';

            if ($row->maintenance_risk_score === null) {
                $row->maintenance_risk_score = round(max(0, 100 - $score), 2);
            }
        }
        return $rows;
    }
}
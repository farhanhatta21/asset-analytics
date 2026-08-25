<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\AnalysisService;

class MLDatasetService
{
    // method ambil breakdown frequency
    private function getBreakdownFrequency()
    {
        return DB::table('breakdown_logs')
            ->select(
                'nama_alat',
                'periode',
                DB::raw('COUNT(*) as breakdown_frequency')
            )
            ->groupBy('nama_alat', 'periode')
            ->get()
            ->keyBy(function ($item) {
                return $item->nama_alat . '_' . $item->periode;
            });
    }

    // method ambil data assets
    private function getAssetData()
    {
        return DB::table('assets')
            ->orderBy('nama_alat')
            ->orderBy('periode')
            ->get();
    }

    // method generate dataset dasar
    public function buildDataset()
    {
        $assets = $this->getAssetData();

        // 
        $mtbfMax = $assets->max('mtbf') ?: 1;
        $mttrpMax = $assets
            ->where('mttrp', '>', 0)
            ->max('mttrp') ?: 1;

        $breakdowns = $this->getBreakdownFrequency();

        $dataset = [];

        foreach ($assets as $asset) {

            // 
            $healthScore = $this->analysisService->calculateHealthScore(
                    [
                        'availability' => $asset->availability,
                        'utilisation' => $asset->utilisation,
                        'mtbf' => $asset->mtbf,
                        'mttrp' => $asset->mttrp,
                        'accident' => $asset->accident,
                        'available_time' => $asset->available_time,
                    ],
                    $mtbfMax,
                    $mttrpMax
                );

            $key = $asset->nama_alat . '_' . $asset->periode;

            $breakdownFrequency =
                $breakdowns[$key]->breakdown_frequency ?? 0;

            $dataset[] = [
                'nama_alat' => $asset->nama_alat,
                'periode' => $asset->periode,

                'availability' => $asset->availability,
                'utilisation' => $asset->utilisation,
                'mtbf' => $asset->mtbf,
                'mttrp' => $asset->mttrp,

                'accident' => $asset->accident,
                'available_time' => $asset->available_time,
                'breakdown_frequency' => $breakdownFrequency,
                // target machine learning 
                'health_score'  => $healthScore,
            ];
        }

        return $dataset;
    }

    // 
    private function groupByAsset(array $dataset)
    {
        $grouped = [];

        foreach ($dataset as $row) {

            $grouped[$row['nama_alat']][] = $row;
        }

        foreach ($grouped as &$rows) {

            usort($rows, function ($a, $b) {

                return strcmp(
                    $a['periode'],
                    $b['periode']
                );
            });
        }

        return $grouped;
    }

    // 
    private function buildSlidingWindowDataset(array $groupedAssets)
    {
        $trainingData = [];

        foreach ($groupedAssets as $namaAlat => $records) {

            if (count($records) < 4) {
                continue;
            }

            for ($i = 0; $i <= count($records) - 4; $i++) {

                $r1 = $records[$i];
                $r2 = $records[$i + 1];
                $r3 = $records[$i + 2];
                $target = $records[$i + 3];

                $trainingData[] = [

                    // IDENTITAS
                    'nama_alat' => $namaAlat,

                    // AVAILABILITY
                    'avail_t1' => $r1['availability'],
                    'avail_t2' => $r2['availability'],
                    'avail_t3' => $r3['availability'],

                    // UTILISATION
                    'util_t1' => $r1['utilisation'],
                    'util_t2' => $r2['utilisation'],
                    'util_t3' => $r3['utilisation'],

                    // MTBF
                    'mtbf_t1' => $r1['mtbf'],
                    'mtbf_t2' => $r2['mtbf'],
                    'mtbf_t3' => $r3['mtbf'],

                    // MTTRp
                    'mttrp_t1' => $r1['mttrp'],
                    'mttrp_t2' => $r2['mttrp'],
                    'mttrp_t3' => $r3['mttrp'],

                    // BREAKDOWN
                    'breakdown_t1' => $r1['breakdown_frequency'],
                    'breakdown_t2' => $r2['breakdown_frequency'],
                    'breakdown_t3' => $r3['breakdown_frequency'],

                    // TARGET
                    'target_health_score' => $target['health_score'],

                    // prediksi menggunakan periode apa
                    'current_period' => $r3['periode'],

                    // INFORMASI TAMBAHAN
                    'target_period' => $target['periode'],
                ];
            }
        }

        return $trainingData;
    }


    //
    public function exportTrainingDataset()
    {
        $dataset = $this->buildDataset();

        $grouped =
            $this->groupByAsset($dataset);

        $trainingData =
            $this->buildSlidingWindowDataset(
                $grouped
            );

        return collect($trainingData);
    }



    // 
    protected $analysisService;

    // 
    public function __construct(
        AnalysisService $analysisService
    ) {
        $this->analysisService = $analysisService;
    }
    
}
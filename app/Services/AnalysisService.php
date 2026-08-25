<?php

namespace App\Services;

class AnalysisService
{
    public function analyze(array $assets, $mtbf_max = null, $mttrp_max = null)
    {
        $validCount = 0;

        $results = [
            'summary' => [
                'total' => 0,
                'sehat' => 0,
                'kurang_sehat' => 0,
                'tidak_sehat' => 0,
            ],
            'priority_tools' => []
        ];

        foreach ($assets as $asset) {

                // skip data kosong
                if (empty($asset['nama_alat'])) continue;

                // FIX TYPE DATA (TARUH DI SINI)
                $asset['availability'] = floatval($asset['availability'] ?? 0);
                $asset['utilisation'] = floatval($asset['utilisation'] ?? 0);
                $asset['mtbf'] = floatval($asset['mtbf'] ?? 0);
                $asset['mttrc'] = floatval($asset['mttrc'] ?? 0);
                $asset['mttrp'] = floatval($asset['mttrp'] ?? 0);          

                $asset['accident'] = floatval($asset['accident'] ?? 0);
                $asset['available_time'] = floatval($asset['available_time'] ?? 0);
                $asset['breakdown_duration'] = floatval($asset['breakdown_duration'] ?? 0);
                $asset['total_breakdown'] = floatval($asset['total_breakdown'] ?? 0);
                $asset['number_of_breakdowns'] = floatval($asset['number_of_breakdowns'] ?? 0);

                $asset['nama'] = $asset['nama_alat'];

                $validCount++;

                $score = $this->calculateHealthScore($asset);
                $status = $this->categorize($score);
            
                $asset['health_score'] = $score;
                $asset['status'] = $status;
         
                // priority fix (Semakin rendah Health Score, semakin tinggi tingkat prioritas)
                $asset['priority'] = round(100 - $score, 2);

                // generate rekomendasi
                $asset['rekomendasi'] = $this->generateRecommendation($asset);

                if ($status === 'Sehat') $results['summary']['sehat']++;
                elseif ($status === 'Kurang Sehat') $results['summary']['kurang_sehat']++;
                else $results['summary']['tidak_sehat']++;

                // TREND LOGIC
                $asset['trend'] = $this->calculateTrend($asset['nama'], $assets);

                $results['priority_tools'][] = $asset;

        }       

        // set total valid
        $results['summary']['total'] = $validCount;

        // sorting prioritas (terburuk di atas)
        usort($results['priority_tools'], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $results;
    }

    /**
     * Health Score Formula (disepakati bersama):
     *   - Availability (60%) : target absolut 85%. S_Avail = min(avail% / 85 * 100, 100)
     *   - MTBF         (30%) : berbasis frekuensi breakdown (J).
     *                          J=0 (NO BD) → skor 100. J>0 → min(100/J, 100)
     *   - MTTRp        (10%) : berbasis durasi perbaikan vs total jam bulan (L).
     *                          J=0 (NO BD) → skor 100. J>0 → max((1 - MTTRp/L)*100, 0)
     *   - Utilisasi          : tidak termasuk health score (informasi terpisah)
     *
     * Threshold: Sehat >= 85 | Kurang Sehat >= 70 | Tidak Sehat < 70
     */
    public function calculateHealthScore($a, $mtbf_max = null, $mttrp_max = null)
    {
        $availability      = (float) ($a['availability'] ?? 0);
        $mttrp             = (float) ($a['mttrp'] ?? 0);
        $numBreakdowns     = (float) ($a['number_of_breakdowns'] ?? 0);
        $availableTime     = (float) ($a['available_time'] ?? 0);

        // Konversi desimal ke persen jika perlu
        if ($availability <= 1) $availability *= 100;

        // S_Avail: target absolut 85% — alat yang >= 85% dapat skor 100
        $s_avail = min(($availability / 85) * 100, 100);

        // S_MTBF: frekuensi breakdown — NO BD = sempurna (100), tiap tambah breakdown turunkan skor
        if ($numBreakdowns <= 0) {
            $s_mtbf = 100;
        } else {
            $s_mtbf = min(100 / $numBreakdowns, 100);
        }

        // S_MTTRp: durasi perbaikan vs total jam bulan — NO BD = sempurna (100)
        // L (available time) default 720 jam jika tidak tersedia
        $L = $availableTime > 0 ? $availableTime : 720;
        if ($numBreakdowns <= 0) {
            $s_mttrp = 100;
        } else {
            $s_mttrp = max((1 - ($mttrp / $L)) * 100, 0);
        }

        // Health Score: 60% Availability + 30% MTBF + 10% MTTRp
        $score = (0.60 * $s_avail) + (0.30 * $s_mtbf) + (0.10 * $s_mttrp);

        return round(min(max($score, 0), 100), 2);
    }

    public function calculateAssetScore($items, $mtbf_max = null, $mttrp_max = null)
    {
        if (empty($items)) return null;

        $latest = collect($items)->sortBy('periode')->values()->last();

        return $this->analyze([$latest])['priority_tools'][0] ?? null;
    }

    private function categorize($score)
    {
        if ($score >= 85) return 'Sehat';
        if ($score >= 70) return 'Kurang Sehat';
        return 'Tidak Sehat';
    }

    public function getCategory($score)
    {
        return $this->categorize($score);
    }

    private function generateRecommendation($a)
    {
        $avail  = (float) ($a['availability'] ?? 0);
        $j      = (float) ($a['number_of_breakdowns'] ?? 0);
        $mttrp  = (float) ($a['mttrp'] ?? 0);
        $accident = (float) ($a['accident'] ?? 0);

        if ($avail <= 1) $avail *= 100;

        if ($accident > 0 && $avail < 50) {
            return 'Alat terkena accident — segera lakukan evaluasi keselamatan & perbaikan';
        }

        if ($avail < 85) {
            return 'Availability di bawah target (85%) — identifikasi penyebab downtime terbesar';
        }

        if ($j >= 5) {
            return 'Breakdown terlalu sering (>= 5x) — perlu inspeksi kondisi komponen kritis';
        }

        if ($j >= 3) {
            return 'Breakdown cukup sering — pertimbangkan peningkatan jadwal perawatan preventif';
        }

        if ($mttrp > 24 && $j > 0) {
            return 'MTTRp tinggi (>24 jam) — evaluasi ketersediaan sparepart & kapasitas tim';
        }

        return 'Operasional normal — lakukan monitoring rutin';
    }

    private function calculateTrend($nama, $allAssets)
    {
        $filtered = array_values(array_filter($allAssets, fn($a) => $a['nama_alat'] == $nama));

        if (count($filtered) < 2) return 'stable';

        $scores = array_map(fn($item) => $this->calculateHealthScore($item), $filtered);

        $first = reset($scores);
        $last  = end($scores);

        if ($last > $first) return 'improving';
        if ($last < $first) return 'declining';
        return 'stable';
    }
}
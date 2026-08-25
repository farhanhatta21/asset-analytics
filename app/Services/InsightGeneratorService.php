<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\InsightGeneratorService;

class InsightGeneratorService
{
    public function generatePredictionStatus($predictedScore)
    {
        if ($predictedScore >= 85) {
            return [
                'status' => 'Sehat',
                'message' => 'Alat diprediksi tetap berada dalam kondisi sehat pada periode mendatang.'
            ];
        }

        if ($predictedScore >= 70) {
            return [
                'status' => 'Kurang Sehat',
                'message' => 'Alat diprediksi mengalami penurunan performa dan memerlukan perhatian pemeliharaan.'
            ];
        }

        return [
            'status' => 'Tidak Sehat',
            'message' => 'Alat diprediksi memiliki risiko maintenance tinggi dan memerlukan tindak lanjut segera.'
        ];
    }

    public function generateCauses(array $asset)
    {
        $causes = [];

        // 1. Availability Tren & Level (Bobot 60%)
        $avail_t1 = (float)($asset['avail_t1'] ?? 0);
        $avail_t3 = (float)($asset['avail_t3'] ?? 0);
        if ($avail_t1 > 1) $avail_t1 /= 100;
        if ($avail_t3 > 1) $avail_t3 /= 100;

        if ($avail_t1 > 0 && $avail_t3 < ($avail_t1 * 0.9)) {
            $decrease = (($avail_t1 - $avail_t3) / $avail_t1) * 100;
            $causes[] = 'Availability turun sebesar ' . round($decrease, 2) . '% dalam 3 periode terakhir';
        }

        if ($avail_t3 < 0.85) {
            $causes[] = 'Availability berada di bawah target acuan 85% (' . round($avail_t3 * 100, 2) . '%)';
        }

        // 2. MTBF / Reliability (Bobot 30%)
        $mtbf_t1 = (float)($asset['mtbf_t1'] ?? 0);
        $mtbf_t3 = (float)($asset['mtbf_t3'] ?? 0);

        if ($mtbf_t1 > 0 && $mtbf_t3 < ($mtbf_t1 * 0.75) && ($mtbf_t1 - $mtbf_t3) > 10) {
            $causes[] = 'Interval kehandalan (MTBF) menurun signifikan dari ' . round($mtbf_t1, 1) . ' jam menjadi ' . round($mtbf_t3, 1) . ' jam';
        }

        // 3. MTTRp / Kemudahan Perbaikan (Bobot 10%)
        $mttrp_t1 = (float)($asset['mttrp_t1'] ?? 0);
        $mttrp_t3 = (float)($asset['mttrp_t3'] ?? 0);

        if ($mttrp_t1 > 0 && $mttrp_t3 > ($mttrp_t1 * 1.2) && ($mttrp_t3 - $mttrp_t1) > 2) {
            $causes[] = 'MTTRp meningkat dari ' . round($mttrp_t1, 2) . ' jam menjadi ' . round($mttrp_t3, 2) . ' jam';
        }

        if ($mttrp_t3 > 24) {
            $causes[] = 'Durasi rata-rata perbaikan (MTTRp) tinggi (' . round($mttrp_t3, 2) . ' jam)';
        }

        // 4. Frekuensi Breakdown
        $bd_t1 = (int)($asset['breakdown_t1'] ?? 0);
        $bd_t3 = (int)($asset['breakdown_t3'] ?? 0);

        if ($bd_t3 > $bd_t1 && $bd_t1 > 0) {
            $causes[] = 'Frekuensi breakdown meningkat dari ' . $bd_t1 . ' menjadi ' . $bd_t3 . ' kejadian';
        }

        if ($bd_t3 >= 5) {
            $causes[] = 'Frekuensi breakdown tergolong tinggi (' . $bd_t3 . ' kejadian dalam 1 periode)';
        }

        // Fallback
        if (empty($causes)) {
            $causes[] = 'Tidak ditemukan penurunan signifikan pada indikator teknis utama (kondisi stabil).';
        }

        return $causes;
    }

    public function generateBreakdownHistory(string $namaAlat)
    {
        // Ambil periode terbaru
        $latestPeriod = DB::table('breakdown_logs')
            ->where('nama_alat', $namaAlat)
            ->max('periode');

        if (!$latestPeriod) {
            return null;
        }

        // Hitung 6 bulan ke belakang untuk hitung part_group, penyebab, dan tindakan
        $endDate = Carbon::createFromFormat(
            'Y-m',
            $latestPeriod
        );

        $startDate = $endDate
            ->copy()
            ->subMonths(5);

        $startPeriod = $startDate->format('Y-m');

        //  Ambil data
        $logs = DB::table('breakdown_logs')
            ->where('nama_alat', $namaAlat)
            ->whereBetween(
                'periode',
                [$startPeriod, $latestPeriod]
            )
            ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        // Part Group Dominan
        $topPart = $logs
            ->groupBy('part_group')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        $partCount = $logs
            ->where('part_group', $topPart)
            ->count();

        // Penyebab Dominan
        $topCause = $logs
            ->groupBy('detail_penyebab')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        // Tindakan Dominan
        $ignoredActions = [
            '',
            '-',
            'WAITING PARTS'
        ];

        $actionStats = $logs
            ->filter(function ($log) use ($ignoredActions) {

                return !in_array(
                    strtoupper(trim($log->detail_tindakan)),
                    $ignoredActions
                );

            })
            ->groupBy('detail_tindakan')
            ->map->count()
            ->sortDesc();

        $topAction = $actionStats
            ->keys()
            ->first();

        $actionCount = $actionStats
            ->first();

        
        if (!$topAction || $actionCount < 2) {
            $topAction = null;
        }

        return [
            'part_group' => $topPart,
            'part_count' => $partCount,
            'cause' => $topCause,
            'action' => $topAction,
        ];
    }


    public function buildInsightAsset(string $namaAlat)
    {
        $asset = DB::table('assets')
            ->where('nama_alat', $namaAlat)
            ->orderBy('periode')
            ->get();

        if ($asset->count() < 3) {
            return null;
        }

        $last3 = $asset->take(-3)->values();

        $latest = $last3[2];

        return [

            'nama_alat' => $namaAlat,

            'avail_t1' => $last3[0]->availability,
            'avail_t2' => $last3[1]->availability,
            'avail_t3' => $last3[2]->availability,

            'util_t1' => $last3[0]->utilisation,
            'util_t2' => $last3[1]->utilisation,
            'util_t3' => $last3[2]->utilisation,

            'mtbf_t1' => $last3[0]->mtbf,
            'mtbf_t2' => $last3[1]->mtbf,
            'mtbf_t3' => $last3[2]->mtbf,

            'mttrp_t1' => $last3[0]->mttrp,
            'mttrp_t2' => $last3[1]->mttrp,
            'mttrp_t3' => $last3[2]->mttrp,

            'breakdown_t1' => $last3[0]->breakdown_frequency ?? 0,
            'breakdown_t2' => $last3[1]->breakdown_frequency ?? 0,
            'breakdown_t3' => $last3[2]->breakdown_frequency ?? 0,

        ];
    }

    public function generateRecommendations(
        array $asset,
        ?array $history = null
    )
    {
        $recommendations = [];

        // 1. Availability
        $avail_t1 = (float)($asset['avail_t1'] ?? 0);
        $avail_t3 = (float)($asset['avail_t3'] ?? 0);
        if ($avail_t1 > 1) $avail_t1 /= 100;
        if ($avail_t3 > 1) $avail_t3 /= 100;

        if ($avail_t1 > 0 && $avail_t3 < ($avail_t1 * 0.9)) {
            $recommendations[] = 'Disarankan melakukan evaluasi penyebab penurunan availability dan meningkatkan aktivitas preventive maintenance.';
        }

        if ($avail_t3 < 0.85) {
            $recommendations[] = 'Disarankan melakukan evaluasi penyebab availability di bawah target 85% untuk meningkatkan kesiapan operasional alat.';
        }

        // 2. MTBF / Kehandalan
        $mtbf_t1 = (float)($asset['mtbf_t1'] ?? 0);
        $mtbf_t3 = (float)($asset['mtbf_t3'] ?? 0);

        if ($mtbf_t1 > 0 && $mtbf_t3 < ($mtbf_t1 * 0.75) && ($mtbf_t1 - $mtbf_t3) > 10) {
            $recommendations[] = 'Disarankan melakukan audit sistem mekanik/elektrik karena interval kehandalan antar-kerusakan (MTBF) semakin pendek.';
        }

        // 3. MTTRp / Waktu Perbaikan
        $mttrp_t1 = (float)($asset['mttrp_t1'] ?? 0);
        $mttrp_t3 = (float)($asset['mttrp_t3'] ?? 0);

        if ($mttrp_t1 > 0 && $mttrp_t3 > ($mttrp_t1 * 1.2) && ($mttrp_t3 - $mttrp_t1) > 2) {
            $recommendations[] = 'Disarankan melakukan evaluasi proses perbaikan untuk mengurangi waktu pemulihan (MTTRp) saat terjadi gangguan.';
        }

        if ($mttrp_t3 > 24) {
            $recommendations[] = 'Disarankan memastikan ketersediaan suku cadang kritis agar durasi downtime perbaikan (MTTRp > 24 jam) dapat dipangkas.';
        }

        // 4. Breakdown
        $bd_t1 = (int)($asset['breakdown_t1'] ?? 0);
        $bd_t3 = (int)($asset['breakdown_t3'] ?? 0);

        if ($bd_t3 > $bd_t1 && $bd_t1 > 0) {
            $recommendations[] = 'Disarankan meningkatkan frekuensi inspeksi rutin untuk memitigasi tren kenaikan gangguan berulang.';
        }

        if ($bd_t3 >= 5) {
            $recommendations[] = 'Disarankan melakukan inspeksi mendalam (overhaul terfokus) karena frekuensi breakdown tergolong tinggi (>= 5 kejadian).';
        }

        // Komponen dominan
        if (
            $history &&
            !empty($history['part_group'])
        ) {

            $recommendations[] =
                'Disarankan memberikan perhatian khusus pada komponen '
                . $history['part_group']
                . ' berdasarkan riwayat gangguan dalam 6 bulan terakhir.';
        }

        // Penyebab dominan
        if (
            $history &&
            !empty($history['cause'])
        ) {

            $cause = strtoupper(
                trim($history['cause'])
            );

            if (
                str_contains(
                    $cause,
                    'LIFETIME'
                )
            ) {

                $recommendations[] =
                    'Disarankan melakukan evaluasi umur penggunaan komponen yang sering mengalami gangguan.';
            }

            elseif (
                str_contains(
                    $cause,
                    'SHORTED'
                )
                ||
                str_contains(
                    $cause,
                    'KORESLETING'
                )
            ) {

                $recommendations[] =
                    'Disarankan melakukan inspeksi sistem kelistrikan dan koneksi komponen terkait.';
            }

            elseif (
                str_contains(
                    $cause,
                    'POOR DURABILITY'
                )
            ) {

                $recommendations[] =
                    'Disarankan melakukan evaluasi kualitas dan ketahanan komponen yang digunakan.';
            }
        }

        // Fallback
        if (empty($recommendations)) {

            $recommendations[] =
                'Lanjutkan monitoring rutin dan preventive maintenance sesuai jadwal yang telah ditetapkan.';
        }

        return $recommendations;
    }

    public function generateInsight(
        array $asset,
        float $predictedScore
    )
    {
        $history = $this->generateBreakdownHistory(
            $asset['nama_alat']
        );

    
        return [

            'status' => $this->generatePredictionStatus(
                $predictedScore
            ),

            'causes' => $this->generateCauses(
                $asset
            ),

            'history' => $history,

            'recommendations' =>
                $this->generateRecommendations(
                    $asset,
                    $history
                ),
        ];
    }

    public function generateNarrative(
        array $asset,
        float $predictedScore
    )
    {
        $insight = $this->generateInsight(
            $asset,
            $predictedScore
        );

        return [

            'title' =>
                '🧠 Predictive Maintenance Insight',

            'asset' =>
                $asset['nama_alat'],

            'status' =>
                $insight['status']['message'],

            'causes' =>
                $insight['causes'],

            'history' =>
                $insight['history'],

            'recommendations' =>
                $insight['recommendations'],

        ];
    }

}
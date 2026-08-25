<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AnalysisService;
use App\Services\DataPreprocessingService;
use App\Http\Controllers\ExportController;
use App\Services\InsightGeneratorService;
use App\Models\Breakdown;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\Prediction;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;


class DashboardController extends Controller
{
    public function uploadForm()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
            'periode' => 'required'
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);

        // ambil semua sheet
        $allSheets = $spreadsheet->getAllSheets();

        // ambil sheet berdasarkan urutan
        $rows = [];
        $dataBdRows = [];

        // ambil semua nama sheet
        $sheetNames = $spreadsheet->getSheetNames();

        // cari sheet DATA BD otomatis
        $dataBdIndex = null;

        foreach ($allSheets as $i => $sheet) {

            $sheetName = strtoupper(trim($sheet->getTitle()));

            if ($sheetName == 'DATA BD') {
                $dataBdIndex = $i;
            }
        

            if ($sheetName == 'PERFORMANCE') {
                $rows = $sheet->toArray();
            }
        }

        // ambil isi sheet DATA BD
        $dataBdRows = $dataBdIndex !== null
            ? $allSheets[$dataBdIndex]->toArray() : [];
        
        // validasi sheet excel
        if (empty($rows)) {
            return back()->with(
                'error',
                'Upload gagal. Sheet PERFORMANCE tidak ditemukan pada file Excel.'
            );
        }

        if ($dataBdIndex === null) {
            return back()->with(
                'error',
                'Upload gagal. Sheet DATA BD tidak ditemukan pada file Excel.'
            );

        }

        $cleaner = new DataPreprocessingService();

        $periode = $request->periode;

        // CEK PERIODE 
        $periodeExists = DB::table('assets')
            ->where('periode', $periode)
            ->exists();

        if ($periodeExists) {
            return redirect()->back()
                ->with('error', 'Data periode '.$periode.' sudah tersedia di dalam sistem.');
        }

        foreach ($rows as $index => $row) {

            // 🔥 SKIP HEADER
            if ($index < 3) continue;

            // 🔥 validasi nama alat
            if (!isset($row[2]) || trim($row[2]) === '') {
                continue;
            }

            // ambil nama alat dari eprformance
            $nama_alat = strtoupper(trim($row[2]));

            \Log::info('Nama alat: '.$nama_alat);

            // auto generate group alat
            $group_alat = explode('-', $nama_alat)[0];

            // validasi format alat
            if (!preg_match('/^[A-Z]+-\d+$/', $nama_alat)) {
                continue;
            }

            // cek duplikat
            $exists = DB::table('assets')
                ->where('nama_alat', $nama_alat)
                ->where('periode', $periode)
                ->exists();
            
            if ($exists) {
                continue;
            }

            DB::table('assets')->insert([
                'nama_alat' => $nama_alat,
                'group_alat' => $group_alat,
                'periode' => $periode,

                'availability' => $cleaner->cleanNumber($row[13]),
                'mtbf' => $cleaner->cleanNumber($row[14]),
                'mttrc' => $cleaner->cleanNumber($row[15]),
                'mttrp' => $cleaner->cleanNumber($row[16]),
                'utilisation' => $cleaner->cleanNumber($row[17]),

                'accident' => $cleaner->cleanNumber($row[6]),
                'available_time' => $cleaner->cleanNumber($row[11]),
                'breakdown_duration' => $cleaner->cleanNumber($row[7]),
                'total_breakdown' => $cleaner->cleanNumber($row[8]),
                'number_of_breakdowns' => $cleaner->cleanNumber($row[9]),

                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Log::info('Berhasil insert asset '.$nama_alat);
        }

        // IMPORT DATA BREAKDOWN (DATA BD)
        foreach ($dataBdRows as $index => $row) 
            {
            // skip header
            if ($index < 2) continue;

            // validasi nomor alat
            if (!isset($row[1]) || trim($row[1]) == '') {
                continue;
            }

            $nama_alat = strtoupper(trim($row[1]));

            // auto generate group alat
            $group_alat = explode('-', $nama_alat)[0];

            // parsing datetime excel
            $startBd = null;
            $finishBd = null;

            try {

                if (!empty($row[2])) {
                    if (is_numeric($row[2])) {
                        $startBd = Carbon::instance(
                            Date::excelToDateTimeObject($row[2])
                        );
                    } else {
                        $startBd = Carbon::parse($row[2]);
                    }
                    
                }

                if (!empty($row[3])) {
                    if (is_numeric($row[3])) {
                        $finishBd = Carbon::instance(
                            Date::excelToDateTimeObject($row[3])
                        );
                    } else {
                        $finishBd = Carbon::parse($row[3]);
                    }
                    
                }

            } catch (\Exception $e) {

                $startBd = null;
                $finishBd = null;
            }

            Breakdown::create([

                'periode' => $periode,

                'group_alat' => $group_alat,
                'nama_alat' => $nama_alat,

                'start_bd' => $startBd,
                'finish_bd' => $finishBd,

                'durasi_bd' => $cleaner->cleanNumber($row[4]),

                'part_group' => trim($row[5] ?? ''),

                'detail_kerusakan' => trim($row[6] ?? ''),
                'detail_penyebab' => trim($row[7] ?? ''),
                'detail_tindakan' => trim($row[8] ?? ''),

                'kendala' => trim($row[9] ?? ''),
                'keterangan' => trim($row[10] ?? ''),

            ]);
        }

        // GENERATE PREDIKSI OTOMATIS
        try {

            // Buat dataset terbaru dari database
            $status = Artisan::call(
                'app:generate-prediction'
            );

            // Jika proses generate gagal
            if ($status !== 0) {

                \Log::error(
                    'Generate prediction gagal',
                    [
                        'output' => Artisan::output()
                    ]
                );

                return back()->with(
                    'error',
                    'Data berhasil diunggah, namun proses prediksi otomatis gagal dijalankan.'
                );
            }

            // Import hasil prediksi terbaru ke tabel predictions
            $importStatus = Artisan::call(
                'app:import-prediction'
            );

            // Jika proses import gagal
            if ($importStatus !== 0) {

                \Log::error(
                    'Import prediction gagal',
                    [
                        'output' => Artisan::output()
                    ]
                );

                return back()->with(
                    'error',
                    'Dataset berhasil dibuat, namun hasil prediksi gagal disimpan.'
                );
            }
            
            } catch (\Throwable $e) {
                \Log::error(
                    'Prediksi otomatis gagal',
                    [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]
                );

                return back()->with(
                    'error',
                    'Data berhasil diunggah, namun terjadi kesalahan saat proses prediksi otomatis.'
                );
            }

            // REDIRECT SETELAH SELURUH PROSES BERHASIL
            return redirect('/')
                ->with(
                    'success',
                    'Data periode '.$periode.' berhasil diunggah dan prediksi otomatis berhasil diperbarui.'
                );
    }


    // DASHBOARD
    public function index(Request $request, AnalysisService $service)
    {
        $periode = $request->periode ?? null;
        $jenis = $request->jenis ?? null;
        $highestRiskPrediction = null;
        $mostCritical = null;
        $correlation = 0;
        $correlationLabel = '-';
        
        // DETEKSI ALAT BARU
        // inisialisasi collection kosong (biar tidak error kalau periode kosong)
        $current = collect();   // data alat di periode sekarang
        $previous = collect();  // data alat di periode sebelumnya

        if ($periode) {
            // ambil semua nama alat di periode yang dipilih
            $current = DB::table('assets')
                ->where('periode', $periode)
                ->pluck('nama_alat');

            // ambil semua nama alat sebelum periode tersebut
            $previous = DB::table('assets')
                ->where('periode', '<', $periode)
                ->pluck('nama_alat');
        }

        // ambil selisih → alat yang baru muncul di periode sekarang
        $newTools = $current->diff($previous)->values();

        $query = DB::table('assets');

        if ($periode) {
            $query->where('periode', $periode);
        }

        if ($jenis) {
            $query->where('group_alat', $jenis);
        }

        // ambil ddata -> group berdasarkan nama alat
        $assetsRaw = $query
        ->select(
            'nama_alat',
            'periode',
            'availability',
            'utilisation',
            'mtbf',
            'mttrc',
            'mttrp',
            'accident',
            'available_time',
            'breakdown_duration',
            'total_breakdown',
            'number_of_breakdowns'
        )
        ->orderBy('nama_alat')
        ->orderByDesc('periode')
        ->get();

        /* TOP PRIORITY hanya menggunakan PERIODE TERAKHIR setiap alat */
        $latestAssets = $assetsRaw
            ->groupBy('nama_alat')
            ->map(function ($items) {
                return $items
                    ->sortByDesc('periode')
                    ->first();
            })
            ->values();

        // NORMALISASI GLOBAL
        $allData = $assetsRaw;

        // Ambil nilai maksimum global untuk normalisasi MTBF, MTTRc, dan MTTRp
        $mtbf_max = max($allData->pluck('mtbf')->toArray() ?: [1]);
        $mttrp_max = max($allData->pluck('mttrp')->toArray() ?: [1]);

        // BREAKDOWN PER ALAT
        $latestBreakdowns = DB::table('breakdown_logs')
            ->select(
                'nama_alat',
                'periode',
                DB::raw('COUNT(*) as total_breakdown'),
                DB::raw('SUM(durasi_bd) as total_downtime')
            )
            ->groupBy('nama_alat', 'periode')
            ->get()
            ->groupBy('nama_alat');
        
        // RINGKASAN BREAKDOWN PER PERIODE TERAKHIR
        $breakdownSummary = DB::table('breakdown_logs')
            ->select(
                'nama_alat',
                'periode',
                DB::raw('COUNT(*) as total_breakdown'),
                DB::raw('SUM(durasi_bd) as total_downtime')
            )
            ->groupBy('nama_alat', 'periode')
            ->get()
            ->keyBy(function ($item) {
                return $item->nama_alat . '|' . $item->periode;
            });
        
        // PREDICTION RESULT
        $predictions = DB::table('predictions')
            ->get()
            ->keyBy('nama_alat');

        // ANALISIS RECRD ASET PER PERIODE
        // Setiap record pada tabel assets sudah mewakili satu alat pada satu periode, sehingga tidak diperlukan proses agregasi atau perhitungan rata-rata. Setiap record dianalisis secara independen menggunakan AnalysisService.
        $assets = [];

        foreach ($latestAssets as $item) {

            $asset = (array) $item;

            $asset['nama'] = $asset['nama_alat'];

            // breakdown periode terakhir 
            $bdPeriod = $latestBreakdowns
                ->get($asset['nama_alat'], collect())
                ->where('periode', $asset['periode'])
                ->first();

            $analysis = $service->analyze(
                [$asset],
                $mtbf_max,
                $mttrp_max
            );

            if (
                empty($analysis['priority_tools']) ||
                !isset($analysis['priority_tools'][0])
            ) {
                continue;
            }

            $tool = $analysis['priority_tools'][0];

            // total breakdown pada periode terakhir
            $tool['total_breakdown'] = $bdPeriod->total_breakdown ?? 0;

            // total downtime pada periode terakhir
            $tool['latest_downtime'] = $bdPeriod->total_downtime ?? 0;

            // detail breakdown terakhir pada periode tersebut 
            $latestBreakdownDetail = DB::table('breakdown_logs')
                ->where('nama_alat', $asset['nama_alat'])
                ->where('periode', $asset['periode'])
                ->orderByDesc('start_bd')
                ->first();

            $tool['latest_problem'] = $latestBreakdownDetail->detail_kerusakan ?? '-';

            $tool['latest_breakdown_date'] = $latestBreakdownDetail->start_bd ?? null;

            // hasil prediction
            $prediction = $predictions->get($asset['nama_alat']);

            $tool['predicted_health_score'] =
                $prediction->predicted_health_score ?? 0;

            $tool['maintenance_risk_score'] =
                $prediction->maintenance_risk_score ?? 0;

            $tool['prediction_period'] =
                $prediction->prediction_period ?? null;

            $assets[] = $tool;
        }

        $correlation = 0;
        $correlationLabel = 'Tidak Ada Data';
        $mostCritical = null;

        // urutkan berdasarkan priority
        usort($assets, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        // SUMMARY HASIL
        if (empty($assets)) {
            // jika tidak ada data
            $results = [
                'summary' => [
                    'total' => 0,
                    'sehat' => 0,
                    'kurang_sehat' => 0,
                    'tidak_sehat' => 0,
                    'critical' => 0,
                ],
                'priority_tools' => []
            ];


        } else {
            // filter status PELAJARI!
            $filteredAssets = $assets;

            // 🔥 SORT SETELAH FILTER
            usort($filteredAssets, function ($a, $b) {
                return $b['priority'] <=> $a['priority'];
            });

            // 🔥 UPDATE RESULTS FINAL
            $results = [
                'summary' => [],
                'priority_tools' => $filteredAssets
            ];

            // 🔥 UPDATE SUMMARY SESUAI FILTER
            $results['summary'] = [
                'total' => count($filteredAssets),
                'sehat' => 0,
                'kurang_sehat' => 0,
                'tidak_sehat' => 0,
                'critical' => 0,
            ];

            foreach ($filteredAssets as $asset) {
                if ($asset['status'] === 'Sehat') $results['summary']['sehat']++;
                elseif ($asset['status'] === 'Kurang Sehat') $results['summary']['kurang_sehat']++;
                else $results['summary']['tidak_sehat']++;

                if ($asset['priority'] > 60) {
                    $results['summary']['critical']++;
                }
            }

            // sort prioritas
            usort($results['priority_tools'], function ($a, $b) {
                return $b['priority'] <=> $a['priority'];
            });

            // most critical
            $mostCritical = $results['priority_tools'][0] ?? null;

            // prediksi paling tinggi
            $highestRiskPrediction = DB::table('predictions')
                ->orderBy('predicted_health_score')
                ->first();

            // correlation
            $healthScores = collect($results['priority_tools'])->pluck('health_score')->toArray();
            $priorities   = collect($results['priority_tools'])->pluck('priority')->toArray();

            $correlation = count($healthScores) > 1 
                ? $this->pearsonCorrelation($healthScores, $priorities)
                : 0;
            
            // interpretasi otomatis
            $correlationLabel = '';

            if ($correlation <= -0.8) {
                $correlationLabel = 'Sangat Kuat';
            } elseif ($correlation <= -0.6) {
                $correlationLabel = 'Kuat';
            } elseif ($correlation <= -0.4) {
                $correlationLabel = 'Sedang';
            } else {
                $correlationLabel = 'Lemah';
            }
        }

        // CHART FILTER KHUSUS
        $chartParam = $request->chart_param ?? 'utilisation';
        $chartPeriode = $request->chart_periode;
        $chartGroup = $request->chart_group;

        $chartQuery = DB::table('assets');

        if ($chartPeriode) {
            $chartQuery->where('periode', $chartPeriode);
        }

        // filter group 
        if ($chartGroup) {

            $chartQuery->whereRaw("
                SUBSTRING_INDEX(nama_alat, '-', 1) = ?
            ", [$chartGroup]);

        }

        $chartData = $chartQuery
            ->select(
                'nama_alat',
                DB::raw("AVG($chartParam) as value")
            )
            ->groupBy('nama_alat')
            ->orderBy('value', 'desc')
            ->get();    
        
        // BREAKDOWN INSIGHT
        // opsi 1 Downtime Bulanan
        $downtimeTrend = DB::table('breakdown_logs')

            ->select(
                'periode',
                DB::raw('SUM(durasi_bd) as total_downtime')
            )

            ->when($jenis, function ($q) use ($jenis) {
                return $q->where('group_alat', $jenis);
            })

            ->groupBy('periode')
            ->orderBy('periode')
            ->get();


        // opsi 2 Komponen paling sering rusak
        $topProblemParts = DB::table('breakdown_logs')

            ->select(
                'part_group',
                DB::raw('COUNT(*) as total')
            )

            ->whereNotNull('part_group')

            ->when($jenis, function ($q) use ($jenis) {
                return $q->where('group_alat', $jenis);
            })

            ->groupBy('part_group')
            ->orderByDesc('total')
            ->limit(7)
            ->get();


        // opsi 3 Breakdown per Group Alat
        $groupBreakdowns = DB::table('breakdown_logs')

            ->select(
                'group_alat',
                DB::raw('COUNT(*) as total')
            )

            ->whereNotNull('group_alat')

            ->groupBy('group_alat')
            ->orderByDesc('total')
            ->get();

        // LIST JENIS ALAT (FILTER)
        $jenisList = DB::table('assets')
            ->select('group_alat')
            ->distinct()
            ->whereNotNull('group_alat')
            ->orderBy('group_alat')
            ->pluck('group_alat');

        // simpan hasil ke session (untuk export PDF)
        session(['results' => $results]);

        // 
        if (!$highestRiskPrediction && !empty($results['priority_tools'])) {
            $topTool = collect($results['priority_tools'])
                ->sortByDesc('maintenance_risk_score')
                ->first();

            if ($topTool) {
                $highestRiskPrediction = (object) $topTool;
            }
        }

        // kirim semua data ke view
        return view('dashboard', compact(
            'results',
            'newTools',
            'correlation',
            'correlationLabel',
            'mostCritical',
            'highestRiskPrediction',
            'jenisList',
            'periode',
            'jenis',
            'chartData',
            'chartParam',
            'downtimeTrend',
            'topProblemParts',
            'groupBreakdowns'
            ));
    }

    public function dataAlat(Request $request, AnalysisService $service)
    {
        $periode = $request->periode ?? null;
        $jenis = $request->jenis ?? null;

        $current = collect();
        $previous = collect();

        if ($periode) {
            $current = DB::table('assets')->where('periode', $periode)->pluck('nama_alat');
            $previous = DB::table('assets')->where('periode', '<', $periode)->pluck('nama_alat');
        }

        $newTools = $current->diff($previous)->values();

        $query = DB::table('assets');
        if ($periode) $query->where('periode', $periode);
        if ($jenis) $query->where('group_alat', $jenis);

        $assetsRaw = $query->select(
            'nama_alat', 'periode', 'availability', 'utilisation',
            'mtbf', 'mttrc', 'mttrp', 'accident', 'available_time',
            'breakdown_duration', 'total_breakdown', 'number_of_breakdowns'
        )->orderBy('nama_alat')->orderByDesc('periode')->get();

        $latestAssets = $assetsRaw->groupBy('nama_alat')->map(fn($items) => $items->sortByDesc('periode')->first())->values();

        $mtbf_max = max($assetsRaw->pluck('mtbf')->toArray() ?: [1]);
        $mttrp_max = max($assetsRaw->pluck('mttrp')->toArray() ?: [1]);

        $latestBreakdowns = DB::table('breakdown_logs')
            ->select('nama_alat', 'periode', DB::raw('COUNT(*) as total_breakdown'), DB::raw('SUM(durasi_bd) as total_downtime'))
            ->groupBy('nama_alat', 'periode')->get()->groupBy('nama_alat');

        $predictions = DB::table('predictions')->get()->keyBy('nama_alat');

        $assets = [];
        foreach ($latestAssets as $item) {
            $asset = (array) $item;
            $asset['nama'] = $asset['nama_alat'];

            $bdPeriod = $latestBreakdowns->get($asset['nama_alat'], collect())->where('periode', $asset['periode'])->first();
            $analysis = $service->analyze([$asset], $mtbf_max, $mttrp_max);

            if (empty($analysis['priority_tools']) || !isset($analysis['priority_tools'][0])) continue;

            $tool = $analysis['priority_tools'][0];
            $tool['total_breakdown'] = $bdPeriod->total_breakdown ?? 0;
            $tool['latest_downtime'] = $bdPeriod->total_downtime ?? 0;

            $prediction = $predictions->get($asset['nama_alat']);
            $tool['predicted_health_score'] = $prediction->predicted_health_score ?? 0;
            $tool['maintenance_risk_score'] = $prediction->maintenance_risk_score ?? 0;
            $tool['prediction_period'] = $prediction->prediction_period ?? null;

            $assets[] = $tool;
        }

        usort($assets, fn($a, $b) => $b['priority'] <=> $a['priority']);

        $jenisList = DB::table('assets')->select('group_alat')->distinct()->whereNotNull('group_alat')->orderBy('group_alat')->pluck('group_alat');
        $periodeList = DB::table('assets')->select('periode')->distinct()->whereNotNull('periode')->orderByDesc('periode')->pluck('periode');

        $results = [
            'summary' => ['total' => count($assets)],
            'priority_tools' => $assets
        ];

        return view('data-alat', compact('results', 'newTools', 'jenisList', 'periodeList', 'periode', 'jenis'));
    }

    public function detail($nama, AnalysisService $service, InsightGeneratorService $insightService, ExportController $exportController)
    {
        $detailData = $exportController->buildDetailData(
            $nama,
            $service,
            $insightService
        );
        
        if (!$detailData) {

            return redirect('/')
                ->with('error', 'Data tidak ditemukan');

        }
        
        return view(
            'detail',

            array_merge(
                $detailData,
                [
                    'nama' => $nama
                ]
            )

        );
    }

    // function pearson correlation
    private function pearsonCorrelation($x, $y)
    {
        $n = count($x);

        $sumX = array_sum($x);
        $sumY = array_sum($y);

        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denominator = sqrt((($n * $sumX2) - pow($sumX,2)) * (($n * $sumY2) - pow($sumY,2)));

        return $denominator == 0 ? 0 : round($numerator / $denominator, 4);
    }

}
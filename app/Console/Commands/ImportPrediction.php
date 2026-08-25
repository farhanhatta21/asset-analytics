<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prediction;

class ImportPrediction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-prediction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import prediction results from CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        logger()->info('IMPORT PREDICTION DIMULAI');

        $file = base_path(
            'python/prediction_results.csv'
        );

        if (!file_exists($file)) {

            $this->error(
                'prediction_results.csv tidak ditemukan'
            );

            return Command::FAILURE;
        }

        // hapus prediksi lama
        Prediction::truncate();

        $this->info('Prediction lama berhasil dihapus.');

        logger()->info('TRUNCATE BERHASIL');

        $handle = fopen(
            $file,
            'r'
        );

        // skip header
        fgetcsv($handle);

        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {

            // validasi jumlah kolom
            if (count($row) < 5) {
                logger()->warning('Baris CSV tidak valid, dilewati.');
                continue;
            }

            $predictedHealthScore = round((float) $row[3], 2);

            Prediction::create([
                'nama_alat' => $row[0],
                'last_period' => $row[1],
                'prediction_period' => $row[2],
                'predicted_health_score' => $predictedHealthScore,
                'maintenance_risk_score' => (float)$row[4],
            ]);

            logger()->info(
                "Import : ".$row[0]." - ".$row[1]
            );

            $count++;
        }

        fclose($handle);
        $this->info("File CSV berhasil dibaca.");

        logger()->info("TOTAL IMPORT : ".$count);

        $this->info(
            "{$count} prediction berhasil diimport"
        );

        return Command::SUCCESS;
    }
}
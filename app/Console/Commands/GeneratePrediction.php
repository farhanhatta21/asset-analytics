<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePrediction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-prediction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Predicition using Python model';

    /*Execute the console command.*/
    public function handle()
    {
        // Mulai menghitung waktu proses pipeline
        $start = microtime(true);

        $python = env('PYTHON_PATH', 'python');

        $this->info("Python Path : ".$python);

        $basePath = base_path('python');

        $this->info('Generate dataset...');

        exec(
            "\"{$python}\" \"{$basePath}/generate_dataset.py\" 2>&1",
            $output1,
            $status1
        );

        $this->line(implode(PHP_EOL, $output1));

        if ($status1 !== 0) {

            $this->error('Generate dataset gagal.');

            return Command::FAILURE;
        }

        $this->info('Training model...');

        exec(
            "\"{$python}\" \"{$basePath}/train_model.py\" 2>&1",
            $outputTrain,
            $statusTrain
        );

        $this->line(
            implode(PHP_EOL, $outputTrain)
        );

        if ($statusTrain !== 0) {

            $this->error('Training model gagal.');

            return Command::FAILURE;
}

        $this->info('Generate prediction...');

        exec(
            "\"{$python}\" \"{$basePath}/predict.py\" 2>&1",
            $output2,
            $status2
        );

        $this->line(implode(PHP_EOL, $output2));

        if ($status2 !== 0) {

            $this->error('Prediction gagal.');

            return Command::FAILURE;
        }

        $this->info('Prediction selesai.');

        // Hitung total waktu proses
        $elapsed = round(
            microtime(true) - $start,
            2
        );

        $this->newLine();

        $this->info(
            "Total waktu proses : {$elapsed} detik"
        );

        return Command::SUCCESS;
    }
}

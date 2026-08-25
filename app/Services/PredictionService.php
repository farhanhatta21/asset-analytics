<?php

namespace App\Services;

use App\Models\Prediction;

class PredictionService
{
    public function getPrediction(
        string $namaAlat
    )
    {
        return Prediction::where(
            'nama_alat',
            $namaAlat
        )
        ->latest()
        ->first();
    }
}
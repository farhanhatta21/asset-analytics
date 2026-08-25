<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = [
        'nama_alat',
        'last_period',
        'prediction_period',
        'predicted_health_score',
        'maintenance_risk_score',
    ];

    protected $casts = [
        'predicted_health_score' => 'float',
        'maintenance_risk_score' => 'float',
    ];
}
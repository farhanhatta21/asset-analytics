<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Breakdown extends Model
{
    protected $table = 'breakdown_logs';

    protected $fillable = [
        'periode',
        'group_alat',
        'nama_alat',
        'start_bd',
        'finish_bd',
        'durasi_bd',
        'part_group',
        'detail_kerusakan',
        'detail_penyebab',
        'detail_tindakan',
        'kendala',
        'keterangan',
    ];
}
<?php

namespace App\Http\Controllers;

use App\Services\AssetHealthService;

class AssetController extends Controller
{
    public function show($nama, AssetHealthService $service)
    {
        // DATA DUMMY (sementara, nanti DB / Excel)
        $assets = [
            [
                'nama' => 'RS-02',
                'availability' => 92,
                'utilisation' => 45,
                'mtbf' => 120,
                'mttrp' => 6,
                'produksi' => 500,
                'bb' => 300
            ],
            [
                'nama' => 'CC-01',
                'availability' => 75,
                'utilisation' => 60,
                'mtbf' => 80,
                'mttrp' => 10,
                'produksi' => 420,
                'bb' => 380
            ],
            [
                'nama' => 'FL-07',
                'availability' => 55,
                'utilisation' => 40,
                'mtbf' => 50,
                'mttrp' => 15,
                'produksi' => 300,
                'bb' => 350
            ]
        ];

        // ANALISIS SEMUA ASET (konsisten dengan dashboard)
        $analysis = $service->analyze($assets);

        // FILTER SATU ASET BERDASARKAN NAMA
        $asset = collect($analysis['priority_tools'])
                    ->firstWhere('nama', $nama);

        if (!$asset) {
            abort(404);
        }

        return view('asset-detail', compact('asset'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MonitoringExportService;

class ReportController extends Controller
{
    public function index(Request $request,MonitoringExportService $service)
    {
        $data = $service->getMonitoringData(
            $request->all()
        );

        $alatList = DB::table('assets')
            ->select('group_alat')
            ->distinct()
            ->orderBy('group_alat')
            ->pluck('group_alat');

        return view(
            'laporan',
            compact(
                'data',
                'alatList'
            )
        );
    }
}
@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<div class="min-h-screen">

<!-- SIDEBAR -->
<x-sidebar />

<!-- MAIN CONTENT -->
<div class="ml-0 md:ml-64 min-h-screen">

<!-- TOPBAR -->
    <x-topbar
        title="Dashboard"
        subtitle="Monitoring Parameter">

    </x-topbar>

    @include('components.flash')

    <!-- CONTENT -->
    <div class="p-5 space-y-5">

<!-- KPI -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    
    <!-- TOTAL -->
    <div class="bg-white px-3 py-3 rounded-xl shadow-sm border flex items-center gap-3">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
            <i data-lucide="boxes" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-xs text-gray-600 font-bold">Total Alat</p>
            <p class="text-base font-bold">{{ $results['summary']['total'] }}</p>
            <p class="text-xs text-gray-600">Semua alat terdaftar</p>
        </div>
    </div>

    <!-- SEHAT -->
    <div class="bg-white px-3 py-3 rounded-xl shadow-sm border flex items-center gap-3">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-green-100 text-green-600">
            <i data-lucide="heart-pulse" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-xs text-gray-600 font-bold">Sehat</p>
            <p class="text-lg font-bold text-green-600">{{ $results['summary']['sehat'] }}</p>
            <p class="text-xs text-gray-600">
                {{ round(($results['summary']['sehat'] / max(1,$results['summary']['total'])) * 100,1) }}%
            </p>
        </div>
    </div>

    <!-- Kurang Sehat -->
    <div class="bg-white px-3 py-3 rounded-xl shadow-sm border flex items-center gap-3">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-xs text-gray-600 font-bold">Kurang Sehat</p>
            <p class="text-lg font-bold text-yellow-600">{{ $results['summary']['kurang_sehat'] }}</p>
            <p class="text-xs text-gray-600">
                {{ round(($results['summary']['kurang_sehat'] / max(1,$results['summary']['total'])) * 100,1) }}%
            </p>
        </div>
    </div>

    <!-- Tidak Sehat -->
    <div class="bg-white p-4 rounded-xl shadow-sm border flex items-center gap-4 hover:shadow-md transition">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-red-100 text-red-600">
            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-xs text-gray-600 font-bold">Tidak Sehat</p>
            <p class="text-lg font-bold text-red-600">{{ $results['summary']['tidak_sehat'] }}</p>
            <p class="text-xs text-gray-600">
                {{ round(($results['summary']['tidak_sehat'] / max(1,$results['summary']['total'])) * 100,1) }}%
            </p>
        </div>
    </div>

</div>

<!-- ALAT PALING KRITIS -->
@if($mostCritical)

<a href="{{ route('alat.detail',$mostCritical['nama']) }}"
   class="block mb-5">

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm
                hover:shadow-md transition overflow-hidden">

        <!-- HEADER -->
        <div class="flex items-center gap-3 px-5 py-3
                    border-b border-slate-200">

            <div class="w-8 h-8 rounded-full bg-red-100
                        flex items-center justify-center">

                <i data-lucide="siren"
                   class="w-4 h-4 text-red-600">
                </i>

            </div>

            <div>

                <h3 class="font-semibold text-slate-800">
                    Aset yang Memerlukan Perhatian Segera
                </h3>

                <p class="text-xs text-slate-600">
                    Aset dengan prioritas maintenance tertinggi saat ini
                </p>

            </div>

        </div>


        <!-- BODY -->
        <div class="px-5 py-5">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">

                <!-- INFORMASI ASET -->
                <div class="flex-1">

                    <h2 class="text-2xl font-bold text-red-600">
                        {{ $mostCritical['nama'] }}
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4
                                gap-x-6 gap-y-2 mt-3
                                text-xs text-slate-600">

                        <p>
                            <span class="font-semibold text-slate-600">
                                Status
                            </span>
                            <br>

                            <span class="text-red-600 font-medium">
                                {{ $mostCritical['status'] }}
                            </span>
                        </p>


                        <p>
                            <span class="font-semibold text-slate-600">
                                Total Breakdown
                            </span>
                            <br>

                            {{ $mostCritical['total_breakdown'] }} kali
                        </p>


                        <p>
                            <span class="font-semibold text-slate-600">
                                Total Downtime
                            </span>
                            <br>

                            {{ $mostCritical['latest_downtime'] }} jam
                        </p>


                        <p>
                            <span class="font-semibold text-slate-700">
                                Detail Kerusakan
                            </span>
                            <br>

                            {{ $mostCritical['latest_problem'] }}
                        </p>

                    </div>

                </div>


                <!-- HEALTH SCORE -->
                <div class="w-full md:w-auto text-left md:text-right border-t md:border-t-0 md:border-l border-slate-200 pt-4 md:pt-0 md:pl-8">

                    <p class="text-xs text-slate-700">
                        Health Score
                    </p>

                    <p class="text-3xl font-bold text-red-600">
                        {{ number_format($mostCritical['health_score'],2) }}
                    </p>

                    <p class="text-xs text-slate-700 mt-1">
                        Kondisi saat ini
                    </p>

                </div>

            </div>

        </div>

    </div>

</a>

@endif


<!-- Predictive Maintenance -->
@if($highestRiskPrediction)

<a href="{{ route('alat.detail', $highestRiskPrediction->nama_alat) }}"
   class="block mb-6">

<div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden">

    <!-- HEADER -->
    <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-200">

        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
            <i data-lucide="wrench"
               class="w-4 h-4 text-blue-700">
            </i>
        </div>

        <span class="font-semibold text-blue-900 text-sm">
            Predictive Maintenance
        </span>

    </div>

    <!-- BODY -->
    <div class="px-5 py-5">
        <!-- Nama Alat -->

        <h2 class="text-3xl font-bold text-blue-700">
            {{ $highestRiskPrediction->nama_alat }}
        </h2>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
            <!-- HEALTH -->
            <div class="flex gap-3 items-center">

                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <i data-lucide="heart-pulse"
                       class="w-6 h-6 text-blue-700">
                    </i>
                </div>

                <div>
                    <div class="text-[11px] text-gray-700">
                        Health Score
                    </div>

                    <div class="text-2xl
                                font-bold
                                text-blue-700">
                        {{ number_format($highestRiskPrediction->predicted_health_score,2) }}
                    </div>

                    <div class="text-[11px] text-gray-700 mt-1">
                        Semakin tinggi nilainya, kondisi aset diperkirakan semakin baik.
                    </div>
                </div>
            </div>

            <!-- RISK -->
            <div class="flex gap-3 items-center md:border-l md:border-gray-200 md:pl-6">

                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0">

                    <i data-lucide="shield-alert"
                       class="w-6 h-6 text-blue-700">
                    </i>

                </div>

                <div>
                    <div class="text-[11px] text-gray-700">
                        Maintenance Risk Score
                    </div>

                    <div class="text-2xl font-bold text-blue-700">
                        {{ number_format($highestRiskPrediction->maintenance_risk_score,2) }}
                    </div>

                    <div class="text-[11px] text-gray-700 mt-1">
                        Semakin tinggi nilainya, semakin tinggi prioritas maintenance.
                    </div>
                </div>
            </div>

            <!-- LEVEL -->
            <div class="flex gap-3 items-center
                        md:border-l md:border-gray-200
                        md:pl-6">

                <div class="w-12 h-12
                            rounded-full

                            @if($highestRiskPrediction->maintenance_risk_score>=30)

                                bg-red-100

                            @elseif($highestRiskPrediction->maintenance_risk_score>=15)

                                bg-yellow-100

                            @else

                                bg-green-100

                            @endif

                            flex
                            items-center
                            justify-center
                            shrink-0">

                    <i data-lucide="triangle-alert"
                    @if($highestRiskPrediction->maintenance_risk_score>=30)
                        class="w-6 h-6 text-red-600"

                    @elseif($highestRiskPrediction->maintenance_risk_score>=15)
                        class="w-6 h-6 text-yellow-600"

                    @else
                        class="w-6 h-6 text-green-600"
                    @endif>

                    </i>

                </div>

                <div>

                    <div class="text-[11px] text-gray-700">

                        Risk Level

                    </div>

                    <div class="flex items-center gap-2">

                        <span class="w-3 h-3 rounded-full

                        @if($highestRiskPrediction->maintenance_risk_score>=30)

                            bg-red-500

                        @elseif($highestRiskPrediction->maintenance_risk_score>=15)

                            bg-yellow-500

                        @else

                            bg-green-500

                        @endif">

                        </span>

                        <span class="font-bold text-xl">

                        @if($highestRiskPrediction->maintenance_risk_score>=30)
                            HIGH

                        @elseif($highestRiskPrediction->maintenance_risk_score>=15)
                            MEDIUM

                        @else
                            LOW

                        @endif

                        </span>

                    </div>

                    <div class="text-[11px] text-gray-700 mt-1">
                        Tingkat prioritas maintenance berdasarkan hasil prediksi.
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="px-5 py-3 bg-blue-50 border-t border-blue-100">
        <p class="text-xs text-blue-700 leading-relaxed">

            <strong>Catatan:</strong>
            Informasi pada bagian ini merupakan
            <strong>prediksi kondisi aset untuk periode {{ $highestRiskPrediction->prediction_period }}</strong>
            berdasarkan <strong>model Machine Learning.</strong> Informasi ini digunakan sebagai pendukung dalam menentukan prioritas maintenance dan bukan menunjukkan kondisi aktual aset saat ini.

        </p>

    </div>

</div>

</a>

@endif

<!-- TOP 5 PRIORITAS -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

    <!-- HEADER -->
    <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-200">
        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
            <i data-lucide="list-checks" class="w-4 h-4 text-orange-600"></i>
        </div>

        <div>
            <h2 class="font-semibold text-gray-800">
                Top 5 Prioritas Maintenance
            </h2>
            <p class="text-xs text-gray-700">
                Daftar aset dengan prioritas maintenance tertinggi
            </p>
        </div>
    </div>

    <!-- BODY -->
    <div class="p-4 space-y-3">

        @foreach(collect($results['priority_tools'] ?? [])->take(5) as $index => $item)

        <div
            onclick="window.location='{{ url('/alat/'.urlencode($item['nama'])) }}'"
            class="cursor-pointer rounded-xl border transition hover:shadow-md p-3

            @if($index==0)
                border-red-300
            @elseif($index==1)
                border-orange-300
            @elseif($index==2)
                border-yellow-300
            @else
                border-gray-200
            @endif">

            <div class="flex items-center justify-between gap-3">

                <!-- LEFT -->
                <div class="flex items-center gap-3 min-w-0 flex-1">

                    <!-- RANK -->
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0

                        @if($index==0)
                            bg-red-600 text-white
                        @elseif($index==1)
                            bg-orange-600 text-white
                        @elseif($index==2)
                            bg-yellow-400 text-black
                        @else
                            bg-gray-200 text-gray-700
                        @endif">

                        {{ $index + 1 }}

                    </div>

                    <!-- INFO -->
                    <div class="min-w-0 flex-1">

                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-gray-800 truncate">
                                {{ $item['nama'] }}
                            </h3>

                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] font-medium

                                @if($item['status']=='Tidak Sehat')
                                    bg-red-100 text-red-700
                                @elseif($item['status']=='Kurang Sehat')
                                    bg-yellow-100 text-yellow-700
                                @else
                                    bg-green-100 text-green-700
                                @endif">

                                {{ $item['status'] }}

                            </span>
                        </div>

                        <div class="mt-2 grid grid-cols-3 gap-2 text-[11px] text-gray-600">
                            <div>
                                <p class="text-gray-700">Downtime</p>
                                <p class="font-semibold text-gray-700">
                                    {{ number_format($item['latest_downtime'],2) }} jam
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-700">Problem</p>
                                <p class="font-semibold text-gray-700 truncate"
                                title="{{ $item['latest_problem'] }}">
                                {{ $item['latest_problem'] }}
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- RIGHT -->
                <div class="text-right shrink-0">

                    <div>
                        <p class="text-[11px] text-gray-700">
                            Health
                        </p>

                        <p
                            class="text-xl font-bold leading-none

                            @if($item['status']=='Tidak Sehat')
                                text-red-600
                            @elseif($item['status']=='Kurang Sehat')
                                text-yellow-600
                            @else
                                text-green-600
                            @endif">

                            {{ number_format($item['health_score'],2) }}

                        </p>
                    </div>

                    <div class="mt-2">
                        <p class="text-[11px] text-gray-700">
                            Priority
                        </p>

                        <p class="text-sm font-bold text-blue-700 leading-none">
                            {{ number_format($item['priority'],2) }}
                        </p>
                    </div>

                </div>

            </div>

        </div>

        @endforeach

    </div>

</div>

<!-- DOWNTIME INSIGHT -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden max-w-5xl mx-auto">

    <!-- HEADER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-4 border-b border-gray-200">

        <div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <i data-lucide="chart-column"class="w-4 h-4 text-blue-600"></i>
                </div>

                <div>
                    <h2 class="font-semibold text-gray-900">
                        Downtime Insight
                    </h2>

                    <p class="text-xs text-gray-700">
                        Monitoring downtime dan kerusakan alat operasional

                    </p>

                </div>

                <!-- INFO -->
                <div class="relative group">

                    <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 text-xs flex items-center justify-center cursor-pointer">
                        i
                    </div>

                    <!-- TOOLTIP -->
                    <div class="absolute left-0 top-7 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg px-3 py-2 w-64 z-50 shadow-md">
                        Grafik ini menampilkan analisis downtime, kerusakan komponen,
                        dan distribusi breakdown alat berdasarkan data historis.
                    </div>

                </div>

            </div>

        </div>

        <!-- FILTER -->
        <label for="breakdownInsightSelect" class="sr-only">
            Pilih jenis analisis downtime
        </label>

        <select id="breakdownInsightSelect"
            class="border rounded-lg px-3 py-2 text-sm">

            <option value="downtime">
                Trend Downtime Bulanan
            </option>

            <option value="part">
                Komponen Paling Sering Rusak
            </option>

            <option value="group">
                Breakdown per Group Alat
            </option>

        </select>


    </div>

    <!-- CHART -->
    <div class="p-2">
        <div class="h-[320px]">
            <canvas id="breakdownInsightChart"></canvas>
        </div>
    </div>

    <!-- INSIGHT OTOMATIS -->
            @php
                $highestDowntime = collect($downtimeTrend)->sortByDesc('total_downtime')->first();
            @endphp

            @if($highestDowntime)
            <div class="mt-2 bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded-lg">

                Downtime tertinggi terjadi pada periode
                <b>{{ $highestDowntime->periode }}</b>

                dengan total downtime sekitar

                <b>{{ round($highestDowntime->total_downtime, 2) }} jam</b>.

            </div>
            @endif

</div>

<!-- Visualisasi Parameter  -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-4 border-b border-gray-200">

        <div class="flex items-center gap-3">

            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">

                <i data-lucide="bar-chart-3"
                class="w-4 h-4 text-indigo-600"></i>

            </div>

            <div>

                <h2 class="font-semibold text-gray-900">

                    Visualisasi Parameter Alat

                </h2>

                <p class="text-xs text-gray-700">

                    Monitoring parameter berdasarkan filter

                </p>

            </div>

        </div>

        <!-- FILTER KHUSUS CHART -->
        <form method="GET" action="/" class="flex gap-2 flex-wrap">

            <!-- PARAMETER -->
            <select name="chart_param"
                class="border rounded-lg px-3 py-2 text-sm">

                <option value="availability"
                    {{ request('chart_param') == 'availability' ? 'selected' : '' }}>
                    Availability
                </option>

                <option value="utilisation"
                    {{ request('chart_param') == 'utilisation' ? 'selected' : '' }}>
                    Utilisation
                </option>

                <option value="mtbf"
                    {{ request('chart_param') == 'mtbf' ? 'selected' : '' }}>
                    MTBF
                </option>

                <option value="mttrp"
                    {{ request('chart_param') == 'mttrp' ? 'selected' : '' }}>
                    MTTRp
                </option>

            </select>

            <!-- PERIODE -->
            <input type="month"
                name="chart_periode"
                value="{{ request('chart_periode') }}"
                class="border rounded-lg px-3 py-2 text-sm">

            <!-- GROUP -->
            <select name="chart_group"
                class="border rounded-lg px-3 py-2 text-sm">

                <option value="">Semua Alat</option>

                @foreach($jenisList as $jenis)
                    <option value="{{ $jenis }}"
                        {{ request('chart_group') == $jenis ? 'selected' : '' }}>
                        {{ $jenis }}
                    </option>
                @endforeach

            </select>

            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                Terapkan
            </button>

        </form>

    </div>

    <div class="p-5">
        <div class="h-[320px]">
            <canvas id="utilChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // BREAKDOWN INSIGHT CHART
    const insightSelect = document.getElementById('breakdownInsightSelect');
    const insightCtx = document.getElementById('breakdownInsightChart');

    let breakdownChart;

    // DATA
    const downtimeData = {
        labels: @json(collect($downtimeTrend)->pluck('periode')),
        values: @json(collect($downtimeTrend)->pluck('total_downtime'))
    };

    const partData = {
        labels: @json(collect($topProblemParts)->pluck('part_group')),
        values: @json(collect($topProblemParts)->pluck('total'))
    };

    const groupData = {
        labels: @json(collect($groupBreakdowns)->pluck('group_alat')),
        values: @json(collect($groupBreakdowns)->pluck('total'))
    };


    // FUNCTION RENDER
    function renderBreakdownChart(type)
    {
        if (breakdownChart) {
            breakdownChart.destroy();
        }

        let config = {};

        // DOWNTIME
        if (type === 'downtime') {

            config = {
                chartType: 'line',
                label: 'Total Downtime',
                data: downtimeData
            };
        }

        // PART
        else if (type === 'part') {

            config = {
                chartType: 'bar',
                label: 'Jumlah Kerusakan',
                data: partData,
                horizontal: true
            };
        }

        // GROUP
        else {

            config = {
                chartType: 'bar',
                label: 'Total Breakdown',
                data: groupData
            };
        }

        breakdownChart = new Chart(insightCtx, {

            type: config.chartType,

            data: {
                labels: config.data.labels,

                datasets: [{
                    label: config.label,
                    data: config.data.values,
                    borderWidth: 2,
                    borderRadius: 8,
                    tension: 0.3
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 5,
                            bottom: 5,
                            left: 10,
                            right: 10
                        }
                    },

                indexAxis: config.horizontal ? 'y' : 'x',

                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {

                                if (
                                    insightSelect.value === 'downtime'
                                ) {
                                    return context.raw + ' jam';
                                }

                                return context.raw + ' breakdown';
                            }
                        }
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true,

                        title: {
                            display: true,
                            text: insightSelect.value === 'downtime'
                                ? 'Jam'
                                : 'Total'
                        }
                    }
                }
            }
        });
    }

    // DEFAULT
    renderBreakdownChart('downtime');

    // EVENT
    insightSelect.addEventListener('change', function () {
        renderBreakdownChart(this.value);
    });

    // ===== ALERT =====
    const top = @json($results['priority_tools'][0] ?? null);

    if (top && top.priority > 60) {
        const toast = document.getElementById('toast');

        toast.innerHTML = `
            ⚠️ PRIORITAS TINGGI <br>
            ${top.nama} (Score: ${top.health_score})
        `;

        toast.classList.remove('hidden');

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 5000);
    }

    // ===== ALERT KESELURUHAN SISTEM =====
    const criticalCount = {{ $results['summary']['tidak_sehat'] ?? 0 }};

        if (criticalCount > 0) {
            const toast = document.getElementById('toast');

            toast.innerHTML = `
                🚨 ${criticalCount} alat membutuhkan perhatian segera!
            `;

            toast.classList.remove('hidden');

            setTimeout(() => {
                toast.classList.add('hidden');
            }, 4000);
        }

    });

    // ===== SEARCH & PAGINATION SCRIPT =====
    const searchInput = document.getElementById('searchInput');
    const tableRows = Array.from(document.querySelectorAll("tbody tr")).filter(r => r.querySelector("td:nth-child(2)"));
    const pageSize = 10;
    let currentPage = 1;
    let filteredRows = [...tableRows];

    function renderTablePage() {
        const total = filteredRows.length;
        const totalPages = Math.ceil(total / pageSize) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;

        tableRows.forEach(r => r.style.display = 'none');
        filteredRows.slice(start, end).forEach(r => r.style.display = '');

        const infoEl = document.getElementById('paginationInfo');
        if (infoEl) {
            infoEl.innerText = total === 0 ? 'Tidak ada data ditemukan' : `Menampilkan ${start + 1} - ${Math.min(end, total)} dari ${total} data`;
        }

        const controlsEl = document.getElementById('paginationControls');
        if (controlsEl) {
            if (totalPages <= 1) {
                controlsEl.innerHTML = '';
                return;
            }

            let btns = `<button type="button" ${currentPage === 1 ? 'disabled class="px-2.5 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded border border-gray-300 hover:bg-gray-50 text-gray-700" onclick="changeDashboardPage(${currentPage - 1})"'}>Prev</button>`;
            
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    btns += `<button type="button" class="px-2.5 py-1 rounded border ${i === currentPage ? 'bg-blue-600 border-blue-600 text-white font-bold' : 'border-gray-300 hover:bg-gray-50 text-gray-700'}" onclick="changeDashboardPage(${i})">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    btns += `<span class="px-1 text-gray-400">...</span>`;
                }
            }

            btns += `<button type="button" ${currentPage === totalPages ? 'disabled class="px-2.5 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded border border-gray-300 hover:bg-gray-50 text-gray-700" onclick="changeDashboardPage(${currentPage + 1})"'}>Next</button>`;
            controlsEl.innerHTML = btns;
        }
    }

    window.changeDashboardPage = function(page) {
        currentPage = page;
        renderTablePage();
    };

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase().trim();
            filteredRows = tableRows.filter(row => {
                const nameCell = row.querySelector("td:nth-child(2)");
                return nameCell && nameCell.innerText.toLowerCase().includes(val);
            });
            currentPage = 1;
            renderTablePage();
        });
    }

    renderTablePage();

</script>

    <!-- LUCIDE -->
    <script>
        lucide.createIcons();
    </script>

</div> 

    <!-- script visualisasi parameter alat -->
    <script>

    const utilCtx = document.getElementById('utilChart');

    if (utilCtx) {

        const labels = @json($chartData->pluck('nama_alat'));
        const values = @json($chartData->pluck('value'));

        const selectedParam = "{{ $chartParam }}";

        let labelY = 'Nilai';

        if (
            selectedParam === 'availability' ||
            selectedParam === 'utilisation'
        ) {
            labelY = 'Persentase (%)';
        }
        else if (selectedParam === 'mtbf') {
            labelY = 'Jam Operasional';
        }
        else {
            labelY = 'Jam Perbaikan';
        }

        new Chart(utilCtx, {

            type: 'bar',

            data: {

                labels: labels,

                datasets: [{

                    label: labelY,
                    data: values,
                    borderSkipped: false,
                    barThickness: 13,
                    maxBarThickness: 16,
                    borderRadius: 5,

                    backgroundColor: function(context) {

                        const value = context.raw;

                        if (selectedParam === 'mttrp') {

                            if (value > 20) {
                                return '#ef4444';
                            }
                            else if (value > 10) {
                                return '#f59e0b';
                            }

                            return '#10b981';
                        }

                        else {

                            if (value < 0.7) {
                                return '#ef4444';
                            }
                            else if (value < 0.85) {
                                return '#f59e0b';
                            }

                            return '#3b82f6';
                        }
                    }
                }]
            },

            options: {

                interaction: {
                    intersect: false,
                    mode: 'index'
                },

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        display: false
                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                let value = context.raw;

                                if (
                                    selectedParam === 'availability' ||
                                    selectedParam === 'utilisation'
                                ) {
                                    value = (value * 100).toFixed(2) + '%';
                                }
                                else {
                                    value = value + ' jam';
                                }

                                return value;
                            }
                        }
                    }
                },

                scales: {

                    x: {

                        title: {
                            display: true,
                            text: 'Nama Alat',
                            color: '#6b7280',
                            font: {
                                size: 13,
                                weight: '600'
                            },

                            padding: {
                                top: 10
                            }
                        },

                        ticks: {
                            color: '#6b7280'
                        },

                        grid: {
                            display: false
                        }
                    },

                    y: {

                        beginAtZero: true,

                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },

                        title: {

                            display: true,
                            text: labelY,
                            color: '#6b7280',
                            font: {
                                size: 13,
                                weight: '600'
                            },

                            padding: {
                                bottom: 10
                            }
                        },

                        ticks: {

                            callback: function(value) {

                                if (
                                    selectedParam === 'availability' ||
                                    selectedParam === 'utilisation'
                                ) {
                                    return (value * 100) + '%';
                                }

                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

    </script>

    <script>
        lucide.createIcons();
    </script>

<!-- </body>

</html> -->
@endsection
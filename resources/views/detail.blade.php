<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Alat - {{ $nama }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Icon -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
</head>

<body class="bg-slate-100 text-slate-700">

@php
    $statusClass = match($latest['status']) {
        'Tidak Sehat' => 'bg-red-100 text-red-700 border-red-200',
        'Kurang Sehat' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        default => 'bg-green-100 text-green-700 border-green-200'
    };

    $priorityClass = $latest['priority'] > 70
        ? 'text-red-600'
        : ($latest['priority'] > 40
            ? 'text-yellow-600'
            : 'text-green-600');

    $first = collect($trend)->first();
    $last = collect($trend)->last();

    $availabilityDelta = ($last['availability'] ?? 0) - ($first['availability'] ?? 0);
    $utilisationDelta  = ($last['utilisation'] ?? 0) - ($first['utilisation'] ?? 0);
    $mtbfDelta = ($last['mtbf'] ?? 0) - ($first['mtbf'] ?? 0);
    $mttrpDelta = ($last['mttrp'] ?? 0) - ($first['mttrp'] ?? 0);
@endphp

<x-sidebar />
<div class="ml-0 md:ml-64">

    <!-- TOPBAR -->
    <x-topbar
        title="Detail Asset Analytics"
        subtitle="Monitoring detail performa alat"
        :asset="$nama">

        <x-slot:actions>

            <a href="{{ route('detail.export.pdf',$nama) }}"
                class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow text-sm">
                <i data-lucide="file-down"></i>
                Export PDF
            </a>

        </x-slot:actions>
    </x-topbar>

    <main class="px-6 pt-2 pb-6">
        <!-- CONTENT -->
        <div class="space-y-2">

        <!-- RINGKASAN KONDISI ALAT -->
            @php

            if($latest['health_score'] >= 85){

                $healthColor='text-green-600';
                $healthBar='bg-green-500';

                $alertBg='bg-green-50';
                $alertBorder='border-green-200';
                $alertText='text-green-700';

                $alertIcon='fa-solid fa-circle-check';

                $alertTitle='Kondisi Sangat Baik';

                $alertMessage='Performa alat optimal dan siap digunakan.';

            }elseif($latest['health_score'] >=70){

                $healthColor='text-yellow-500';
                $healthBar='bg-yellow-400';

                $alertBg='bg-yellow-50';
                $alertBorder='border-yellow-200';
                $alertText='text-yellow-700';

                $alertIcon='fa-solid fa-circle-exclamation';

                $alertTitle='Perlu Monitoring';

                $alertMessage='Performa alat cukup baik namun perlu dipantau secara berkala.';

            }else{

                $healthColor='text-red-600';
                $healthBar='bg-red-500';

                $alertBg='bg-red-50';
                $alertBorder='border-red-200';
                $alertText='text-red-700';

                $alertIcon='fa-solid fa-triangle-exclamation';

                $alertTitle='Risiko Tinggi';

                $alertMessage='Berpotensi terjadi gangguan operasional segera lakukan inspeksi untuk pencegahan.';

            }

            @endphp
            
            <!--  -->
            @if($latest)
            <section class="bg-white rounded-xl shadow-sm border overflow-hidden">

                <!-- Title -->
                <div class="px-6 py-3 border-b border-gray-100 bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fa-solid fa-heart-pulse text-blue-700 text-ml"></i>
                        </div>

                        <div class="flex items-center justify-between w-full">
                            <div>
                                <h2 class="text-sm font-bold text-gray-800">
                                    Ringkasan Kondisi Alat
                                </h2>

                                <p class="text-[11px] text-gray-500">
                                    Monitoring Performa Terkini
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-[10px] text-gray-400">
                                    Periode Analisis
                                </p>

                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $latest['periode'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Content -->
                <div class="grid xl:grid-cols-5 gap-5 p-5">

                    <!-- LEFT : HEALTH SCORE -->
                    <div class="xl:col-span-2 space-y-4">

                        <!-- Health Score Card -->
                        <div>

                            <p class="text-sm font-medium text-slate-500">
                                Health Score
                            </p>

                            <div class="flex items-end gap-2 mt-2">

                                <h3 class="text-4xl font-bold {{ $healthColor }}">
                                    {{ round($latest['health_score'],2) }}
                                </h3>

                                <span class="text-slate-400 mb-1">
                                    /100
                                </span>

                            </div>

                            <!-- Progress -->
                            <div class="mt-5 h-3 bg-slate-200 rounded-full overflow-hidden">

                                <div
                                    class="h-full rounded-full {{ $healthBar }}"
                                    style="width: {{ $latest['health_score'] }}%">
                                </div>

                            </div>

                            <!-- Priority -->
                            <div class="flex gap-3 mt-5">

                                <div class="px-4 py-2 rounded-xl bg-white border">

                                    Prioritas :

                                    <span class="font-bold {{ $priorityClass }}">
                                        {{ round($latest['priority'],2) }}
                                    </span>

                                </div>

                                <div class="px-4 py-2 rounded-xl border {{ $statusClass }}">

                                    {{ $latest['status'] }}

                                </div>

                            </div>

                        </div>

                        <!-- Alert -->
                        <div class="rounded-lg border {{ $alertBorder }} {{ $alertBg }} px-4 py-3">

                            <div class="flex items-start gap-3">

                                <!-- Icon -->
                                <div class="w-8 h-8 rounded-full bg-white/80 flex items-center justify-center shrink-0">

                                    <i class="{{ $alertIcon }} text-sm {{ $alertText }}"></i>

                                </div>

                                <!-- Text -->
                                <div>

                                    <p class="text-sm font-semibold {{ $alertText }}">
                                        {{ $alertTitle }}
                                    </p>

                                    <p class="text-xs leading-relaxed mt-1 {{ $alertText }}">
                                        {{ $alertMessage }}
                                    </p>

                                </div>

                            </div>

                        </div>
                    </div>

                    <!-- RIGHT : METRICS -->
                    <div class="xl:col-span-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Availability -->
                        <div class="border rounded-xl p-5 bg-blue-50">

                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-slate-500 font-medium">
                                    Availability
                                </p>

                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fa-solid fa-clock text-blue-600"></i>
                                </div>
                            </div>

                            <h3 class="text-2xl font-bold text-blue-600">
                                {{ round($latest['availability'] * 100,2) }}%
                            </h3>

                            <p class="text-xs text-slate-500 mt-2">
                                Presentase kesiapan alat.
                            </p>

                        </div>


                        <!-- Utilisation -->
                        <div class="border rounded-xl p-5 bg-green-50">

                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-slate-500 font-medium">
                                    Utilisation
                                </p>

                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fa-solid fa-chart-column text-green-600"></i>
                                </div>
                            </div>

                            <h3 class="text-2xl font-bold text-green-600">
                                {{ round($latest['utilisation'] * 100,2) }}%
                            </h3>

                            <p class="text-xs text-slate-500 mt-2">
                                Presentase tingkat penggunaan alat.
                            </p>

                        </div>


                        <!-- MTBF -->
                        <div class="border rounded-xl p-5 bg-purple-50">

                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-slate-500 font-medium">
                                    MTBF
                                </p>

                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <i class="fa-solid fa-shield-heart text-purple-600"></i>
                                </div>
                            </div>

                            <h3 class="text-2xl font-bold text-purple-600">
                                {{ round($latest['mtbf'],2) }}
                            </h3>

                            <p class="text-xs text-slate-500 mt-2">
                                Total waktu antar kerusakan.
                            </p>

                        </div>


                        <!-- MTTRp -->
                        <div class="border rounded-xl p-5 bg-orange-50">

                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-slate-500 font-medium">
                                    MTTRp
                                </p>

                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
                                    <i class="fa-solid fa-screwdriver-wrench text-orange-600"></i>
                                </div>
                            </div>

                            <h3 class="text-2xl font-bold text-orange-600">
                                {{ round($latest['mttrp'],2) }}
                            </h3>

                            <p class="text-xs text-slate-500 mt-2">
                                Total waktu perbaikan alat (Mean Time to Repair).
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- AI -->
            @if(isset($insight))
            <section class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mt-8">
                <div class="px-6 py-3 border-b border-gray-100 bg-white">
                    <div class="flex items-center justify-between">

                        <div class="flex items-center gap-3">

                            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">

                                <i class="fa-solid fa-brain text-blue-700 text-sm"></i>

                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-gray-800">
                                    Predictive Maintenance Insight
                                </h2>

                                <p class="text-[11px] text-gray-500">
                                    Prediksi Machine Learning untuk periode maintenance selajutnya
                                </p>

                            </div>

                        </div>
                    </div>
                </div>
            
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                        <!-- card 1 -->
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-5 flex flex-col justify-between min-h-[140px]">
                            <p class="text-xs text-gray-500">Prediksi Health Score</p>
                            
                            <h2 class="text-3xl font-bold text-blue-700 mt-1">
                                {{ round($prediction->predicted_health_score,2) }}
                            </h2>
                            
                            <p class="text-xs text-gray-500 mt-2">Prediksi health score alat di periode selanjutnya</p>
                        </div>
                        
                        <!-- card 2 -->
                        <div class="rounded-xl border border-red-100 bg-red-50 p-5 flex flex-col justify-between min-h-[140px]">
                            <p class="text-xs text-gray-500">Tingkat Resiko Maintenance</p>
                                    
                            <h2 class="text-3xl font-bold text-red-600 mt-1">
                                {{ round($prediction->maintenance_risk_score,2) }}
                            </h2>
                            
                            <p class="text-xs text-gray-500 mt-2">Prediksi tingkat risiko maintenance di periode selanjutnya</p>
                        </div>
                        
                        <!-- card 3 -->
                        <div class="rounded-xl border border-gray-200 bg-white p-5 flex flex-col justify-between min-h-[140px]">
                            <p class="text-xs text-gray-500">
                                Status Prediksi
                            </p>
                            
                            <div class="flex items-center gap-3 mt-3">
                                <span class="w-3 h-3 rounded-full
                                    
                                    @if($prediction->predicted_health_score>=80)
                                        bg-green-500
                                    
                                    @elseif($prediction->predicted_health_score>=60)
                                        bg-yellow-400
                                    
                                    @else
                                        bg-red-500
                                    
                                    @endif
                                "></span>
                                
                                <span class="font-semibold">
                                    @if($prediction->predicted_health_score>=80)

                                        LOW RISK    

                                    @elseif($prediction->predicted_health_score>=60)    

                                        MEDIUM RISK    

                                    @else    

                                        HIGH RISK    

                                    @endif        

                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Prediction Summary -->
                    <div class="pt-1 pb-1">                            
                        <div class="rounded-xl border {{ $statusColor['border'] }} {{ $statusColor['bg'] }} p-4">
                            <div class="flex gap-3">
                                <div>
                                    <p class="text-ml font-bold {{ $statusColor['text'] }}">
                                        Kesimpulan Prediksi
                                    </p>
                                        
                                    <p class="text-sm mt-1 leading-relaxed {{ $statusColor['text'] }}">
                                        {{ $insight['status']['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>  
                            
                    <!-- FAKTOR + REKOMENDASI -->
                    <div class="grid lg:grid-cols-2 gap-6">

                        <!-- FAKTOR -->
                        <div class="border rounded-xl p-5">

                            <h3 class="font-bold text-slate-700 mb-4">
                                Faktor Utama Prediksi
                            </h3>

                            <div class="space-y-4">

                                @foreach($insight['causes'] as $cause)

                                <div class="flex items-start gap-3">

                                    <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center shrink-0">

                                        <i class="fa-solid fa-circle-exclamation text-orange-500 text-xs"></i>

                                    </div>

                                    <p class="text-sm leading-6 text-gray-700">
                                        {{ $cause }}
                                    </p>

                                </div>

                                @endforeach

                            </div>

                        </div>

                        <!-- REKOMENDASI -->
                        <div class="border rounded-xl p-5">

                            <h3 class="font-bold text-slate-700 mb-4">
                                Rekomendasi Pemeliharaan
                            </h3>

                            <div class="space-y-4">

                                @foreach($insight['recommendations'] as $recommendation)

                                <div class="flex items-start gap-3">

                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">

                                        <i class="fa-solid fa-check text-blue-600 text-xs"></i>

                                    </div>

                                    <p class="text-sm leading-6 text-gray-700">
                                        {{ $recommendation }}
                                    </p>

                                </div>

                                @endforeach

                            </div>

                        </div>

                    </div>

                    </div> {{-- END p-6 space-y-6 --}}

                    
                    </section>
                    @endif

            <!-- PERFORMA DAN TREND -->
            <section class="mt-8">
                <div class="bg-white rounded-xl shadow-sm border p-4">

                    <div class="mb-3">
                        <div class="px-2 py-3 border-b border-gray-100 bg-white">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                                    <i class="fa-solid fa-chart-line text-blue-700 text-ml"></i>
                                </div>

                                <div>
                                    <h2 class="text-sm font-bold text-gray-800">
                                        Performa & Trend
                                    </h2>
                                    
                                    <p class="text-[11px] text-gray-500">
                                        Trend health score 12 periode terakhir
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- Chart -->
                    <div class="h-[280px]">
                        <canvas id="chart"></canvas>
                    </div>


                    <!-- Metric Trend -->
                    <div class="grid md:grid-cols-4 gap-4 mt-4">

                        <!-- Availability -->
                        <div class="p-3 rounded-2xl border bg-slate-50">
                            <p class="text-xs text-slate-400 mb-1">Availability</p>

                            <h3 class="text-lg font-bold text-slate-700">
                                {{ round($latest['availability'] * 100,2) }}%
                            </h3>

                            <p class="text-xs mt-1 {{ $availabilityDelta < 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ $availabilityDelta < 0 ? '↓' : '↑' }}
                                {{ round(abs($availabilityDelta)*100,2) }}%
                            </p>
                        </div>


                        <!-- Utilisation -->
                        <div class="p-3 rounded-2xl border bg-slate-50">
                            <p class="text-xs text-slate-400 mb-1">Utilisation</p>

                            <h3 class="text-lg font-bold text-slate-700">
                                {{ round($latest['utilisation'] * 100,2) }}%
                            </h3>

                            <p class="text-xs mt-1 {{ $utilisationDelta < 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ $utilisationDelta < 0 ? '↓' : '↑' }}
                                {{ round(abs($utilisationDelta)*100,2) }}%
                            </p>
                        </div>


                        <!-- MTBF -->
                        <div class="p-3 rounded-2xl border bg-slate-50">
                            <p class="text-xs text-slate-400 mb-1">MTBF</p>

                            <h3 class="text-lg font-bold text-slate-700">
                                {{ round($latest['mtbf'],2) }}
                            </h3>

                            <p class="text-xs mt-1 {{ $mtbfDelta < 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ $mtbfDelta < 0 ? '↓' : '↑' }}
                                {{ round(abs($mtbfDelta),2) }}
                            </p>
                        </div>


                        <!-- MTTRp -->
                        <div class="p-3 rounded-2xl border bg-slate-50">

                            <p class="text-xs text-slate-400 mb-1">
                                Mean Time to Repair (MTTRp)
                            </p>

                            <h3 class="text-lg font-bold text-slate-700">
                                {{ round($latest['mttrp'],2) }}
                            </h3>

                            <p class="text-xs mt-2 {{ $mttrpDelta > 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ $mttrpDelta > 0 ? '↑' : '↓' }}
                                {{ round(abs($mttrpDelta),2) }}
                            </p>

                        </div>

                    </div>

                </div>

            </section>


            <!-- BREAKDOWN TERAKHIR -->
            @if(isset($latestBreakdown) && $latestBreakdown)
            <section class="bg-white rounded-xl shadow-sm border p-4">

                <!-- Title -->
                 <div class="px-2 py-3 bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-blue-700 text-ml"></i>
                        </div>
                        
                        <div>
                            <h2 class="text-ml font-bold text-gray-800">
                                Breakdown Terakhir
                            </h2>
                            
                            <p class="text-[11px] text-gray-500">
                                <!-- belum tau mau diisi apa -->
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-5 gap-3">

                    <div class="p-4 rounded-2xl border bg-red-50">
                        <p class="text-xs text-slate-400 mb-1">Waktu Breakdown</p>

                        <h3 class="font-bold text-red-600 leading-relaxed">
                            {{ \Carbon\Carbon::parse($latestBreakdown->start_bd)->format('d M Y H:i') }}
                        </h3>
                    </div>


                    <div class="p-4 rounded-2xl border bg-orange-50">
                        <p class="text-xs text-slate-400 mb-1">Total Downtime</p>

                        <h3 class="font-bold text-orange-600">
                            {{ round($latestBreakdown->durasi_bd,2) }} jam
                        </h3>
                    </div>


                    <div class="p-4 rounded-2xl border">
                        <p class="text-xs text-slate-400 mb-1">Part Bermasalah</p>

                        <h3 class="font-semibold">
                            {{ $latestBreakdown->part_group ?? '-' }}
                        </h3>
                    </div>


                    <div class="p-4 rounded-2xl border">
                        <p class="text-xs text-slate-400 mb-1">Penyebab</p>

                        <h3 class="font-semibold leading-relaxed text-sm">
                            {{ $latestBreakdown->detail_penyebab ?? '-' }}
                        </h3>
                    </div>


                    <div class="p-3 rounded-2xl border">
                        <p class="text-xs text-slate-400 mb-1">Tindakan</p>

                        <h3 class="font-semibold leading-relaxed text-sm">
                            {{ $latestBreakdown->detail_tindakan ?? '-' }}
                        </h3>
                    </div>

                </div>

            </section>
            @endif


            <!-- PART BERMASALAH + HISTORI BREAKDOWN -->
            <section class="grid xl:grid-cols-3 gap-3">


                <!-- PART BERMASALAH -->
                @if(isset($topProblemParts) && count($topProblemParts))

                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">

                    <!-- Header -->
                    <div class="px-6 py-3 border-b border-gray-100 bg-white">

                        <div class="flex items-center gap-3">

                            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">

                                <i class="fa-solid fa-screwdriver-wrench text-blue-700 text-sm"></i>

                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-gray-800">
                                    Analisis Part Bermasalah
                                </h2>

                                <p class="text-[11px] text-gray-500">
                                    Part dengan frekuensi breakdown tertinggi
                                </p>

                            </div>

                        </div>

                    </div>

                    <!-- Content -->
                    <div class="p-5">

                        <div class="space-y-3">

                            @foreach($topProblemParts as $index => $part)

                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">

                                <div class="flex items-center gap-3">

                                    <div class="w-7 h-7 rounded-full bg-red-100 text-red-600 text-xs font-bold flex items-center justify-center">

                                        {{ $index + 1 }}

                                    </div>

                                    <div>

                                        <p class="font-semibold text-sm text-gray-800">

                                            {{ $part->part_group }}

                                        </p>

                                        <p class="text-xs text-gray-500">

                                            Total Breakdown

                                        </p>

                                    </div>

                                </div>

                                <div>

                                    <span class="px-3 py-1 rounded-full bg-red-50 text-red-600 text-xs font-bold">

                                        {{ $part->total }} kali

                                    </span>

                                </div>

                            </div>

                            @endforeach

                        </div>

                    </div>

                </div>

                @endif

                <!-- HISTORI BREAKDOWN -->
                <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border overflow-hidden p-4">

                    <!-- Title -->
                    <div class="px-2 py-3 border-b border-gray-100 bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="fa-solid fa-clock-rotate-left text-blue-700 text-sm"></i>
                            </div>
                            
                            <div>
                                <h2 class="text-ml font-bold text-gray-800">
                                    Histori Breakdown
                                </h2>
                                
                                <p class="text-[11px] text-gray-500">
                                    Lampiran detail breakdown
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- scroll + kunci ukuran tabel -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[320px]">
                        <table class="w-full text-sm">

                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide sticky top-0 z-10">
                                <tr>
                                    <th class="p-4 text-left">Waktu</th>
                                    <th class="p-4 text-left">Part</th>
                                    <th class="p-4 text-left">Kerusakan</th>
                                    <th class="p-4 text-left">Penyebab</th>
                                    <th class="p-4 text-center">Downtime</th>
                                </tr>
                            </thead>


                            <tbody>

                                @forelse($breakdownHistory as $bd)
                                <tr class="border-t hover:bg-slate-50 transition">

                                    <td class="p-3 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($bd->start_bd)->format('d M Y H:i') }}
                                    </td>

                                    <td class="p-3 font-semibold whitespace-nowrap">
                                        {{ $bd->part_group }}
                                    </td>

                                    <td class="p-3">
                                        {{ $bd->detail_kerusakan }}
                                    </td>

                                    <td class="p-3">
                                        {{ $bd->detail_penyebab }}
                                    </td>

                                    <td class="p-3 text-center whitespace-nowrap">
                                        {{ round($bd->durasi_bd,2) }} jam
                                    </td>

                                </tr>
                                @empty

                                <tr>
                                    <td colspan="5"
                                        class="p-4 text-center text-slate-400">
                                        Tidak ada histori breakdown
                                    </td>
                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>


            <!-- HISTORI DATA -->
            <section class="bg-white rounded-xl shadow-sm border">
                <div class="p-4">
                    <div class="px-2 py-3 border-b border-gray-100 bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="fa-solid fa-table-list text-blue-700 text-ml"></i>
                            </div>
                            
                            <div>
                                <h2 class="text-ml font-bold text-gray-800">
                                    Histori Performa Alat
                                </h2>
                                
                                <p class="text-[11px] text-gray-500">
                                    Lampiran detail performa alat
                                </p>
                            </div>
                        </div>
                    </div>


                <div class="overflow-x-auto relative">
                    <!-- Loading Overlay -->
                    <div id="historyLoading" class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-20 hidden transition-opacity duration-150">
                        <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 bg-white px-4 py-2 rounded-full shadow border border-blue-100">
                            <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Memuat data...
                        </div>
                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide">

                            <tr>
                                <th class="p-4 text-left">Periode</th>
                                <th class="p-4 text-left">Availability</th>
                                <th class="p-4 text-left">Utilisation</th>
                                <th class="p-4 text-left">MTBF</th>
                                <th class="p-4 text-left">MTTRp</th>
                                <th class="p-4 text-left">Health Score</th>
                                <th class="p-4 text-left">Status</th>
                            </tr>

                        </thead>


                        <tbody id="historyTableBody">

                            @foreach($data as $d)
                            <tr class="border-t hover:bg-slate-50 transition">

                                <td class="p-4 whitespace-nowrap font-medium">
                                    {{ $d->periode }}
                                </td>

                                <td class="p-4 whitespace-nowrap">
                                    {{ round($d->availability * 100,2) }}%
                                </td>

                                <td class="p-4 whitespace-nowrap">
                                    {{ round($d->utilisation * 100,2) }}%
                                </td>

                                <td class="p-4 whitespace-nowrap">
                                    {{ round($d->mtbf,2) }}
                                </td>

                                <td class="p-4 whitespace-nowrap">
                                    {{ round($d->mttrp,2) }}
                                </td>

                                <td class="p-4 whitespace-nowrap font-bold">
                                        {{ isset($d->health_score) ? round($d->health_score, 2) : '-' }}
                                </td>

                                <td class="p-4 whitespace-nowrap">

                                    @php
                                        $rowStatusClass = match($d->status ?? '') {
                                            'Tidak Sehat' => 'bg-red-100 text-red-700 border-red-200',
                                            'Kurang Sehat' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                            default => 'bg-green-100 text-green-700 border-green-200'
                                        };
                                    @endphp

                                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $rowStatusClass }}">
                                        {{ $d->status ?? '-' }}
                                    </span>

                                </td>

                            </tr>
                            @endforeach

                            @if(count($data) == 0)
                            <tr>
                                <td colspan="7" class="p-6 text-center text-slate-400">
                                    Tidak ada data performa alat
                                </td>
                            </tr>
                            @endif

                        </tbody>

                    </table>

                </div>

                <!-- PAGINATION -->
                <div id="historyPagination" class="px-4 py-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600 mt-2">
                    <div id="historyPaginationInfo">Menampilkan data...</div>
                    <div class="flex items-center gap-1.5" id="historyPaginationControls"></div>
                </div>

                </div>

            </section>

        </div>

    </main>

</div>


<!-- =========================================================
| CHART JS
========================================================== -->
<script>

const ctx = document.getElementById('chart');

new Chart(ctx, {

    type: 'line',

    data: {

        labels: @json(collect($trend)->pluck('periode')),

        datasets: [

            {
                label: 'Health Score',
                data: @json(collect($trend)->pluck('health_score')),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6
            },

            {
                label: 'Healthy Threshold',
                data: Array(@json(count($trend))).fill(85),
                borderColor: '#22c55e',
                borderDash: [5,5],
                pointRadius: 0,
                borderWidth: 2
            },

            {
                label: 'Warning Threshold',
                data: Array(@json(count($trend))).fill(70),
                borderColor: '#f59e0b',
                borderDash: [5,5],
                pointRadius: 0,
                borderWidth: 2
            }

        ]
    },


    options: {

        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                position: 'top'
            }
        },

        scales: {

            y: {
                min: 0,
                max: 100,
                grid: {
                    color: '#e5e7eb'
                }
            },

            x: {
                grid: {
                    display: false
                }
            }
        }
    }

});

</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();

    // ===== PAGINATION & LAZY LOADING HISTORI PERFORMA ALAT =====
    document.addEventListener("DOMContentLoaded", function () {
        const tableRows = Array.from(document.querySelectorAll("#historyTableBody tr")).filter(r => r.querySelectorAll("td").length > 1);
        const loadingEl = document.getElementById('historyLoading');
        const pageSize = 10;
        let currentPage = 1;

        function renderHistoryPage(showLoading = false) {
            if (showLoading && loadingEl) {
                loadingEl.classList.remove('hidden');
            }

            requestAnimationFrame(() => {
                const total = tableRows.length;
                const totalPages = Math.ceil(total / pageSize) || 1;
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const start = (currentPage - 1) * pageSize;
                const end = start + pageSize;

                tableRows.forEach(r => r.style.display = 'none');
                tableRows.slice(start, end).forEach(r => r.style.display = '');

                const infoEl = document.getElementById('historyPaginationInfo');
                if (infoEl) {
                    infoEl.innerText = total === 0 ? 'Tidak ada data' : `Menampilkan ${start + 1} - ${Math.min(end, total)} dari ${total} data`;
                }

                const controlsEl = document.getElementById('historyPaginationControls');
                if (controlsEl) {
                    if (totalPages <= 1) {
                        controlsEl.innerHTML = '';
                    } else {
                        let btns = `<button type="button" ${currentPage === 1 ? 'disabled class="px-2.5 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded border border-gray-300 hover:bg-gray-50 text-gray-700" onclick="changeHistoryPage(${currentPage - 1})"'}>Prev</button>`;
                        
                        for (let i = 1; i <= totalPages; i++) {
                            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                                btns += `<button type="button" class="px-2.5 py-1 rounded border ${i === currentPage ? 'bg-blue-600 border-blue-600 text-white font-bold' : 'border-gray-300 hover:bg-gray-50 text-gray-700'}" onclick="changeHistoryPage(${i})">${i}</button>`;
                            } else if (i === currentPage - 2 || i === currentPage + 2) {
                                btns += `<span class="px-1 text-gray-400">...</span>`;
                            }
                        }

                        btns += `<button type="button" ${currentPage === totalPages ? 'disabled class="px-2.5 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded border border-gray-300 hover:bg-gray-50 text-gray-700" onclick="changeHistoryPage(${currentPage + 1})"'}>Next</button>`;
                        controlsEl.innerHTML = btns;
                    }
                }

                if (showLoading && loadingEl) {
                    setTimeout(() => loadingEl.classList.add('hidden'), 100);
                }
            });
        }

        window.changeHistoryPage = function(page) {
            currentPage = page;
            renderHistoryPage(true);
        };

        renderHistoryPage();
    });
</script>

</body>
</html>
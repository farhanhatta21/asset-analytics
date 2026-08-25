@extends('layouts.app')
@section('title','Export Laporan')
@section('content')

<body class="bg-slate-100 min-h-screen text-slate-700">

<!-- SIDEBAR -->
<x-sidebar />

<div class="ml-0 md:ml-64 min-h-screen">
    <x-topbar title="Laporan Monitoring" subtitle="Export laporan performa dan histori breakdown aset">
        <x-slot:actions>
            
        </x-slot:actions>
    </x-topbar>

<!-- CONTENT -->
<div class="max-w-7xl mx-auto p-6 space-y-6">

    @if(session('error'))
    <div class="rounded-2xl bg-red-50 border border-red-200/80 p-4 text-red-700 text-sm flex items-start gap-3 shadow-xs">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-red-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    @if(session('success'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-emerald-700 text-sm flex items-start gap-3 shadow-xs">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-emerald-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Dynamic Client Error Notification -->
    <div id="jsErrorAlert" class="hidden rounded-2xl bg-amber-50 border border-amber-300 p-4 text-amber-800 text-sm flex items-start gap-3 shadow-xs transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-amber-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <div class="flex-1">
            <p class="font-bold text-amber-900">Perhatian</p>
            <p id="jsErrorMessage" class="text-xs text-amber-800 mt-0.5"></p>
        </div>
        <button type="button" onclick="document.getElementById('jsErrorAlert').classList.add('hidden')" class="text-amber-600 hover:text-amber-800 text-xs font-bold px-2 py-1">✕</button>
    </div>

    <form id="laporanForm"
          method="GET"
          action="/laporan"
          class="bg-white border rounded-2xl shadow-sm p-6 space-y-6">

        <!-- TITLE -->
        <div>

            <h2 class="text-lg font-bold text-slate-800">
                Filter Laporan
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Tentukan data yang ingin dimasukkan ke laporan export.
            </p>

        </div>

        <!-- FILTER GRID -->
        <div class="grid lg:grid-cols-2 gap-6">


            <!-- PERIODE -->
            <div id="periodeSection" class="space-y-4">

                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-slate-700">
                            Periode Laporan
                        </h3>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 px-2 py-0.5 rounded-md">Wajib Dipilih</span>
                    </div>

                    <p class="text-sm text-slate-500 mt-0.5">
                        Pilih rentang periode awal dan akhir data untuk diexport.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">

                    <!-- DARI -->
                    <div>

                        <label class="block text-sm font-medium mb-2">
                            Dari <span class="text-red-500">*</span>
                        </label>

                        <input type="month"
                               id="periode_awal"
                               name="periode_awal"
                               value="{{ request('periode_awal') }}"
                               oninput="this.classList.remove('border-red-500', 'ring-2', 'ring-red-200'); document.getElementById('jsErrorAlert').classList.add('hidden');"
                               class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">

                    </div>

                    <div>

                        <label class="block text-sm font-medium mb-2">
                            Sampai <span class="text-red-500">*</span>
                        </label>

                        <input type="month"
                               id="periode_akhir"
                               name="periode_akhir"
                               value="{{ request('periode_akhir') }}"
                               oninput="this.classList.remove('border-red-500', 'ring-2', 'ring-red-200'); document.getElementById('jsErrorAlert').classList.add('hidden');"
                               class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">

                    </div>

                </div>

            </div>


            <!-- FILTER ALAT -->
            <div class="space-y-4">

                <div>
                    <h3 class="font-semibold text-slate-700">
                        Filter Kelompok Alat
                    </h3>

                    <p class="text-sm text-slate-500">
                        Pilih kelompok alat tertentu atau tampilkan semua alat.
                    </p>
                </div>

                <select name="alat"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3">

                    <option value="">
                        Semua Alat
                    </option>

                    @foreach($alatList as $alat)
                        <option value="{{ $alat }}"
                            {{ request('alat') == $alat ? 'selected' : '' }}>
                            {{ $alat }}
                        </option>
                    @endforeach

                </select>

            </div>

        </div>


        <!-- PARAMETER -->
        <div>

            <div class="mb-4">

                <h3 class="font-semibold text-slate-700">
                    Parameter Performance
                </h3>

                <p class="text-sm text-slate-500">
                    Pilih parameter yang ingin dimasukkan ke laporan.
                </p>

            </div>

            <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-4">

                @php
                    $params = [
                        'availability' => 'Availability',
                        'utilisation' => 'Utilisation',
                        'mtbf' => 'MTBF',
                        'mttrp' => 'MTTRp',
                        'health_score' => 'Health Score',
                    ];

                    $selectedParams = request()->has('params')
                        ? request('params')
                        : array_keys($params);
                @endphp

                @foreach($params as $key => $label)

                <label class="flex items-center gap-3 border rounded-xl px-4 py-3 cursor-pointer hover:border-blue-300 transition">

                    <input type="checkbox"
                           name="params[]"
                           value="{{ $key }}"
                           {{ in_array($key, $selectedParams) ? 'checked' : '' }}>

                    <span class="text-sm font-medium">
                        {{ $label }}
                    </span>

                </label>

                @endforeach

            </div>

        </div>


        <!-- INCLUDE BREAKDOWN -->
        <div class="border rounded-2xl p-5 bg-slate-50">

            <label class="flex items-start gap-4 cursor-pointer">

                <input type="checkbox"
                       name="include_breakdown"
                       value="1"
                       {{ request('include_breakdown') ? 'checked' : '' }}
                       class="mt-1">

                <div>

                    <h3 class="font-semibold text-slate-700">
                        Sertakan Data Breakdown
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        Tambahkan histori breakdown alat ke dalam laporan.
                    </p>

                </div>

            </label>

        </div>

        <!-- ACTION -->
        <div class="flex flex-wrap gap-3 justify-between items-center border-t pt-6">

            <!-- LEFT -->
            <div class="text-sm text-slate-500">
                Pilih periode awal & akhir, lalu download laporan.
            </div>

            <!-- RIGHT -->
            <div class="flex flex-wrap gap-3">

                <!-- EXCEL -->
                <button
                    type="button"
                    id="btnExportExcel"
                    onclick="handleExportClick('{{ route('export.excel') }}', this)"
                    class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl transition shadow-sm hover:shadow active:scale-[0.99] disabled:opacity-75 disabled:cursor-not-allowed">

                    <i class="fa-solid fa-file-excel"></i>

                    <span>Export Excel</span>

                </button>

                <!-- PDF -->
                <button
                    type="button"
                    id="btnExportPdf"
                    onclick="handleExportClick('{{ route('export.pdf') }}', this)"
                    class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-xl transition shadow-sm hover:shadow active:scale-[0.99] disabled:opacity-75 disabled:cursor-not-allowed">

                    <i class="fa-solid fa-file-pdf"></i>

                    <span>Export PDF</span>

                </button>

            </div>

        </div>

    </form>

</div>

</div>

<script>
    function handleExportClick(actionUrl, btnElement) {
        const awal = document.getElementById('periode_awal');
        const akhir = document.getElementById('periode_akhir');
        const alertBox = document.getElementById('jsErrorAlert');
        const alertMsg = document.getElementById('jsErrorMessage');
        const form = document.getElementById('laporanForm');

        // Reset error styling
        awal.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
        akhir.classList.remove('border-red-500', 'ring-2', 'ring-red-200');

        // 1. Cek apakah periode_awal atau periode_akhir kosong
        if (!awal.value || !akhir.value) {
            alertMsg.textContent = 'Silakan pilih rentang periode laporan (Periode "Dari" dan "Sampai") terlebih dahulu sebelum melakukan export.';
            alertBox.classList.remove('hidden');

            if (!awal.value) awal.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            if (!akhir.value) akhir.classList.add('border-red-500', 'ring-2', 'ring-red-200');

            document.getElementById('periodeSection').scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (!awal.value) awal.focus();
            else akhir.focus();
            return false;
        }

        // 2. Cek apakah periode_awal > periode_akhir
        if (awal.value > akhir.value) {
            alertMsg.textContent = 'Periode awal ("Dari") tidak boleh lebih besar dari periode akhir ("Sampai"). Silakan perbaiki tanggal periode.';
            alertBox.classList.remove('hidden');

            awal.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            akhir.classList.add('border-red-500', 'ring-2', 'ring-red-200');

            document.getElementById('periodeSection').scrollIntoView({ behavior: 'smooth', block: 'center' });
            awal.focus();
            return false;
        }

        // Sembunyikan alert jika valid
        alertBox.classList.add('hidden');

        // Cegah spam click / double submit
        if (btnElement.dataset.clicked === 'true') {
            return false;
        }
        btnElement.dataset.clicked = 'true';
        btnElement.disabled = true;
        btnElement.classList.add('opacity-75', 'cursor-not-allowed');

        setTimeout(() => {
            btnElement.dataset.clicked = 'false';
            btnElement.disabled = false;
            btnElement.classList.remove('opacity-75', 'cursor-not-allowed');
        }, 3500);

        // Submit form ke action export yang dituju
        form.action = actionUrl;
        form.method = 'GET';
        form.submit();
    }
</script>

@endsection
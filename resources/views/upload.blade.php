@extends('layouts.admin')

@php
    $title = 'Upload Data';
    $subtitle = 'Upload data performa alat operasional';
@endphp

@section('title','Upload Data')

@section('content')



<!-- CONTENT -->
<div class="max-w-5xl mx-auto -mt-2 space-y-5">
    <!-- FORM -->
    <form
        id="uploadForm"
        action="/upload"
        method="POST"
        enctype="multipart/form-data"
        class="bg-white rounded-xl border shadow-sm px-6 py-5 space-y-2"
        onsubmit="event.preventDefault(); const btn = document.getElementById('uploadBtn'); if(btn.dataset.submitted === 'true') { return false; } if(!document.getElementById('file').files.length) { alert('Silakan pilih file Excel terlebih dahulu.'); return false; } btn.dataset.submitted = 'true'; btn.disabled = true; btn.classList.add('opacity-75', 'cursor-not-allowed'); document.getElementById('uploadText').classList.add('hidden'); document.getElementById('uploadLoading').classList.remove('hidden'); setTimeout(() => { document.getElementById('uploadForm').submit(); }, 600);">
        @csrf
        
        <!-- NOTE ADMIN -->
        <div class="flex items-start gap-3 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3">
            <div class="w-7 h-7 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="shield-check" class="w-4 h-4 text-yellow-600"></i>
            </div>

            <div>
                <h3 class="font-medium text-xs text-yellow-700">
                    Khusus Admin
                </h3>

                <p class="text-xs text-yellow-700 mt-0.5 leading-relaxed">
                    Fitur upload data hanya diperuntukkan bagi admin atau petugas
                    yang memiliki hak akses pengelolaan data.
                </p>
            </div>

        </div>

        <!-- PERIODE -->
        <div class="pb-8 border-b border-slate-200">
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-800">
                    Periode Laporan
                </h3>

                <p class="text-sm text-slate-500 mt-1">
                    Pilih periode data yang akan dimasukkan ke sistem.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <!-- Bulan -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">
                        Bulan
                    </label>

                    <select id="bulan" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>

                <!-- Tahun -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">
                        Tahun
                    </label>

                    <select id="tahun" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($year = 2024; $year <= 2035; $year++)
                            <option value="{{ $year }}">
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        
            <!-- Hidden Periode -->
            <input type="hidden" name="periode" id="periode">

        </div>

        <!-- FILE UPLOAD -->
        <div class="pt-8">
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-800">
                    File Excel
                </h3>

                <p class="text-sm text-slate-500 mt-1">
                    Upload file Excel data performa alat.
                </p>
            </div>

            <!-- DROPZONE -->
            <label for="file" class="border-2 border-dashed border-blue-200 rounded-xl p-8 bg-blue-50/40 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-blue-50 transition">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-3">
                    <i data-lucide="file-spreadsheet" class="w-8 h-8 text-blue-600"></i>
                </div>

                <h3 class="font-bold text-slate-700 text-lg">
                    Drag & Drop File Excel
                </h3>

                <p class="text-sm text-slate-500 mt-2">
                    atau klik area ini untuk memilih file
                </p>

                <p class="text-xs text-slate-400 mt-3">
                    Format: .xlsx atau .xls
                </p>

                <input type="file" name="file" id="file" accept=".xlsx,.xls" required class="hidden">
            </label>

            <!-- FILE NAME -->
            <div id="fileName" class="mt-4 hidden items-center gap-3 bg-slate-50 border rounded-xl px-4 py-3 text-sm">
                <i data-lucide="file-spreadsheet" class="w-5 h-5 text-green-600"></i>
                
                <span id="selectedFile"></span>
            </div>
        </div>

        <!-- ACTION BUTTON -->
        <div class="flex justify-end pt-4 border-t">
            <button
                id="uploadBtn"
                type="submit"
                class="flex items-center justify-center min-w-[140px] gap-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white px-5 py-2.5 rounded-lg text-sm font-medium shadow transition disabled:opacity-75 disabled:cursor-not-allowed">
                <span id="uploadText" class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Upload Data</span>
                </span>
                <span id="uploadLoading" class="hidden inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Mengunggah...</span>
                </span>
            </button>
        </div>
    </form>
</div>

<!-- SCRIPT -->
<script>
    // FILE NAME
    const fileInput = document.getElementById('file');
    const fileNameBox = document.getElementById('fileName');
    const selectedFile = document.getElementById('selectedFile');

    fileInput.addEventListener('change', function () {

        if (this.files.length > 0) {

            fileNameBox.classList.remove('hidden');
            fileNameBox.classList.add('flex');

            selectedFile.textContent = this.files[0].name;
        }

    });

    // PERIODE FORMAT
    const bulan = document.getElementById('bulan');
    const tahun = document.getElementById('tahun');
    const periode = document.getElementById('periode');

    function updatePeriode() {
        periode.value = tahun.value + '-' + bulan.value;
    }

    bulan.addEventListener('change', updatePeriode);
    tahun.addEventListener('change', updatePeriode);

    updatePeriode();

</script>

@endsection
@extends('layouts.app')

@section('title', 'Data Alat')

@section('content')

<div class="min-h-screen">

<!-- SIDEBAR -->
<x-sidebar />

<!-- MAIN CONTENT -->
<div class="ml-0 md:ml-64 min-h-screen">

    <!-- TOPBAR -->
    <x-topbar
        title="Data Alat"
        subtitle="Daftar dan monitoring kondisi seluruh aset operasional">
    </x-topbar>

    @include('components.flash')

    <!-- CONTENT -->
    <div class="p-5 space-y-5">

        <!-- FILTER & SEARCH CARD -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <form method="GET" action="{{ route('data-alat') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                <div class="flex flex-wrap gap-2 items-center flex-1">
                    <!-- SEARCH -->
                    <div class="relative flex-1 min-w-[200px]">
                        <input
                            type="text"
                            id="searchInput"
                            placeholder="🔍 Cari nama alat..."
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <!-- PERIODE -->
                    <select name="periode" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Periode</option>
                        @foreach($periodeList as $p)
                            <option value="{{ $p }}" {{ $periode == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>

                    <!-- GROUP ALAT -->
                    <select name="jenis" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisList as $j)
                            <option value="{{ $j }}" {{ $jenis == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        Filter
                    </button>
                    @if($periode || $jenis)
                    <a href="{{ route('data-alat') }}" class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-2 rounded-lg text-sm transition">
                        Reset
                    </a>
                    @endif
                </div>

                <div class="text-xs text-gray-500 self-center">
                    Total: <strong class="text-gray-800">{{ count($results['priority_tools'] ?? []) }}</strong> alat
                </div>
            </form>
        </div>

        <!-- DATA HISTORIS ALAT TABLE -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto relative">
                <!-- Loading Overlay -->
                <div id="tableLoading" class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-20 hidden transition-opacity duration-150">
                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 bg-white px-4 py-2 rounded-full shadow border border-blue-100">
                        <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Memuat data...
                    </div>
                </div>

                <table class="min-w-[1200px] w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600 text-xs tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Rank</th>
                            <th class="w-44 px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-right">Availability</th>
                            <th class="px-4 py-3 text-right">Utilisation</th>
                            <th class="px-4 py-3 text-right">Health Score</th>
                            <th class="px-4 py-3 text-right">Predicted Health</th>
                            <th class="px-4 py-3 text-right">Maintenance Risk</th>
                            <th class="px-4 py-3 text-left">Priority Level</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                    @foreach($results['priority_tools'] ?? [] as $index => $item)
                        <tr onclick="window.location='{{ url('/alat/'.urlencode($item['nama'])) }}'"
                            class="border-t border-gray-100 cursor-pointer hover:bg-sky-50 transition">
                            
                            <!-- RANK -->
                            <td class="px-4 py-3 font-bold">
                                @if($index==0)
                                    <span class="bg-red-600 text-white px-2 py-1 rounded-full text-xs">#1</span>
                                @elseif($index==1)
                                    <span class="bg-orange-600 text-white px-2 py-1 rounded-full text-xs">#2</span>
                                @elseif($index==2)
                                    <span class="bg-yellow-400 text-black px-2 py-1 rounded-full text-xs">#3</span>
                                @else
                                    #{{ $index+1 }}
                                @endif
                            </td>

                            <!-- NAMA -->
                            <td class="w-44 px-4 py-3 font-semibold text-blue-600 whitespace-nowrap">
                                {{ $item['nama'] }}
                                @if(!empty($newTools) && in_array($item['nama'], $newTools->toArray()))
                                    <span class="ml-2 bg-green-600 text-white px-2 py-1 rounded text-xs">Alat Baru</span>
                                @endif
                            </td>

                            <!-- AVAILABILITY -->
                            <td class="px-4 py-3 text-right font-medium">
                                {{ round($item['availability']*100, 2) }}%
                            </td>

                            <!-- UTILISATION -->
                            <td class="px-4 py-3 text-right font-medium">
                                {{ round($item['utilisation']*100, 2) }}%
                            </td>

                            <!-- HEALTH SCORE -->
                            <td class="px-4 py-3 text-right font-medium {{ $item['health_score'] < 70 ? 'text-red-600' : ($item['health_score'] < 85 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $item['health_score'] }}
                            </td>

                            <!-- PREDICTED HEALTH -->
                            <td class="px-4 py-3 text-right font-medium">
                                {{ number_format($item['predicted_health_score'], 2) }}
                            </td>

                            <!-- MAINTENANCE RISK -->
                            <td class="px-4 py-3 text-right font-medium">
                                {{ number_format($item['maintenance_risk_score'], 2) }}
                            </td>

                            <!-- PRIORITY -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-16 bg-gray-100 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $item['status']=='Tidak Sehat' ? 'bg-red-500' : ($item['status']=='Kurang Sehat' ? 'bg-yellow-400' : 'bg-green-500') }}"
                                             style="width:{{ min($item['priority'], 100) }}%">
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold whitespace-nowrap">
                                        {{ round($item['priority'], 2) }}
                                    </span>
                                </div>
                            </td>

                            <!-- STATUS -->
                            <td class="px-4 py-3">
                                <span class="inline-flex whitespace-nowrap px-3 py-1 rounded-full text-xs font-semibold {{ $item['status']=='Sehat' ? 'bg-green-100 text-green-700' : ($item['status']=='Kurang Sehat' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach

                    @if(empty($results['priority_tools']))
                        <tr id="emptyRow">
                            <td colspan="9" class="text-center py-10 text-gray-600">
                                Tidak ada data tersedia
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div id="tablePagination" class="px-5 py-3 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
                <div id="paginationInfo">Menampilkan data...</div>
                <div class="flex items-center gap-1.5" id="paginationControls"></div>
            </div>
        </div>

    </div>
</div>

</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const tableLoading = document.getElementById('tableLoading');
    const tableRows = Array.from(document.querySelectorAll("#tableBody tr")).filter(r => r.querySelector("td:nth-child(2)"));
    const pageSize = 10;
    let currentPage = 1;
    let filteredRows = [...tableRows];
    let searchDebounceTimer = null;

    function renderTablePage(showLoading = false) {
        if (showLoading && tableLoading) {
            tableLoading.classList.remove('hidden');
        }

        requestAnimationFrame(() => {
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
                } else {
                    let btns = `<button type="button" ${currentPage === 1 ? 'disabled class="px-2.5 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded border border-gray-300 hover:bg-gray-50 text-gray-700" onclick="changePage(${currentPage - 1})"'}>Prev</button>`;
                    
                    for (let i = 1; i <= totalPages; i++) {
                        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                            btns += `<button type="button" class="px-2.5 py-1 rounded border ${i === currentPage ? 'bg-blue-600 border-blue-600 text-white font-bold' : 'border-gray-300 hover:bg-gray-50 text-gray-700'}" onclick="changePage(${i})">${i}</button>`;
                        } else if (i === currentPage - 2 || i === currentPage + 2) {
                            btns += `<span class="px-1 text-gray-400">...</span>`;
                        }
                    }

                    btns += `<button type="button" ${currentPage === totalPages ? 'disabled class="px-2.5 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded border border-gray-300 hover:bg-gray-50 text-gray-700" onclick="changePage(${currentPage + 1})"'}>Next</button>`;
                    controlsEl.innerHTML = btns;
                }
            }

            if (showLoading && tableLoading) {
                setTimeout(() => tableLoading.classList.add('hidden'), 100);
            }
        });
    }

    window.changePage = function(page) {
        currentPage = page;
        renderTablePage(true);
    };

    // Lazy debounced search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchDebounceTimer);
            if (tableLoading) tableLoading.classList.remove('hidden');

            searchDebounceTimer = setTimeout(() => {
                const val = searchInput.value.toLowerCase().trim();
                filteredRows = tableRows.filter(row => {
                    const nameCell = row.querySelector("td:nth-child(2)");
                    return nameCell && nameCell.innerText.toLowerCase().includes(val);
                });
                currentPage = 1;
                renderTablePage(true);
            }, 250);
        });
    }

    renderTablePage();
</script>

@endsection

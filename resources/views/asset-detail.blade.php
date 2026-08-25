<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Asset {{ $asset['nama'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-10">

<a href="{{ url('/') }}" class="text-blue-600 hover:underline mb-6 inline-block">
    ← 
</a>

<h1 class="text-2xl font-bold mb-6">
    Detail Kesehatan Asset: {{ $asset['nama'] }}
</h1>

{{-- KPI --}}
<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Health Score</p>
        <h2 class="text-3xl font-bold">{{ $asset['health_score'] }}</h2>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Availability</p>
        <h2 class="text-3xl font-bold">{{ $asset['availability'] }}%</h2>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Utilisation</p>
        <h2 class="text-3xl font-bold">{{ $asset['utilisation'] }}%</h2>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Status</p>
        <span class="
            inline-block mt-2 px-4 py-2 rounded text-white font-semibold
            {{ $asset['status'] == 'Sehat' ? 'bg-green-600' :
               ($asset['status'] == 'Kurang Sehat' ? 'bg-yellow-500' : 'bg-red-600') }}">
            {{ $asset['status'] }}
        </span>
    </div>
</div>

{{-- INTERPRETASI --}}
<div class="bg-white p-6 rounded shadow mb-8">
    <h2 class="text-lg font-semibold mb-3">Interpretasi Kondisi</h2>

    @if($asset['status'] === 'Tidak Sehat')
        <p class="text-red-700">
            Asset memiliki risiko tinggi terhadap gangguan operasional.
            Perlu inspeksi teknis menyeluruh dan evaluasi komponen utama.
        </p>
    @elseif($asset['status'] === 'Kurang Sehat')
        <p class="text-yellow-700">
            Asset masih dapat beroperasi namun menunjukkan penurunan performa.
            Disarankan preventive maintenance dalam waktu dekat.
        </p>
    @else
        <p class="text-green-700">
            Asset berada dalam kondisi optimal dan aman untuk operasional normal.
            Tetap lakukan monitoring rutin.
        </p>
    @endif
</div>

{{-- MTBF & MTTRp --}}
<div class="grid grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow">
        <h3 class="font-semibold mb-2">MTBF</h3>
        <p class="text-2xl font-bold">{{ $asset['mtbf'] }} jam</p>
        <p class="text-gray-500 text-sm">
            Rata-rata waktu antar kegagalan
        </p>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h3 class="font-semibold mb-2">MTTRp</h3>
        <p class="text-2xl font-bold">{{ $asset['mttrp'] }} jam</p>
        <p class="text-gray-500 text-sm">
            Rata-rata waktu perbaikan
        </p>
    </div>
</div>

{{-- CHART --}}
<div class="bg-white p-6 rounded shadow mb-8 w-1/2">
    <h2 class="text-lg font-semibold mb-4">
        Komponen Penilaian Health Score
    </h2>
    <canvas id="scoreChart"></canvas>
</div>

<script>
const ctx = document.getElementById('scoreChart');

new Chart(ctx, {
    type: 'radar',
    data: {
        labels: ['Availability', 'Utilisation', 'Reliability'],
        datasets: [{
            label: 'Nilai (%)',
            data: [
                {{ $asset['availability'] }},
                {{ $asset['utilisation'] }},
                {{ round(($asset['mtbf'] / ($asset['mtbf'] + $asset['mttrp'])) * 100, 2) }}
            ],
            backgroundColor: 'rgba(37, 99, 235, 0.3)',
            borderColor: '#2563eb'
        }]
    },
    options: {
        scales: {
            r: {
                max: 100,
                min: 0
            }
        }
    }
});
</script>

</body>
</html>

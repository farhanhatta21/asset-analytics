<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Asset Analytics Dashboard</title>
    @vite('resources/css/app.css')
</head>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('healthChart'), {
        type: 'bar',
        data: {
            labels: ['A', 'B', 'C'],
            datasets: [{
                label: 'Tes',
                data: [10, 20, 30]
            }]
        }
    });
});
</script>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-800 text-white p-6">
        <h1 class="text-xl font-bold mb-6">Asset Analytics</h1>
        <ul class="space-y-3">
            <li class="hover:text-sky-400 cursor-pointer">Dashboard</li>
            <li class="hover:text-sky-400 cursor-pointer">Analisis Alat</li>
            <li class="hover:text-sky-400 cursor-pointer">Laporan</li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6">

        <h2 class="text-2xl font-semibold mb-6">Dashboard Kesehatan Alat</h2>

        <!-- Cards -->
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-5 rounded shadow">
                <p class="text-gray-500">Total Alat</p>
                <p class="text-2xl font-bold">120</p>
            </div>
            <div class="bg-white p-5 rounded shadow">
                <p class="text-gray-500">Alat Sehat</p>
                <p class="text-2xl font-bold text-green-600">78</p>
            </div>
            <div class="bg-white p-5 rounded shadow">
                <p class="text-gray-500">Perlu Perhatian</p>
                <p class="text-2xl font-bold text-red-600">42</p>
            </div>
        </div>
        
        <!-- canvas grafik -->
        <div class="bg-white rounded shadow p-5 mb-8">
            <h3 class="text-lg font-semibold mb-4">Grafik Health Score Alat</h3>
            <canvas id="healthChart" height="100"></canvas>
        </div>


        <!-- Table -->
        <div class="bg-white rounded shadow p-5">
            <h3 class="text-lg font-semibold mb-4">Ringkasan Analisis Alat</h3>

            <table class="w-full border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 border">Nama Alat</th>
                        <th class="p-2 border">Availability</th>
                        <th class="p-2 border">Utilisation</th>
                        <th class="p-2 border">Health Score</th>
                        <th class="p-2 border">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        <td class="p-2 border">RS-02</td>
                        <td class="p-2 border">92%</td>
                        <td class="p-2 border">45%</td>
                        <td class="p-2 border">68</td>
                        <td class="p-2 border text-yellow-600 font-semibold">Kurang Sehat</td>
                    </tr>
                    <tr class="text-center">
                        <td class="p-2 border">CC-15</td>
                        <td class="p-2 border">88%</td>
                        <td class="p-2 border">78%</td>
                        <td class="p-2 border">82</td>
                        <td class="p-2 border text-green-600 font-semibold">Sehat</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>{{ $title }}</title>

    <style>

        @page{
            margin:24px;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:11px;
            color:#374151;
            line-height:1.45;
        }

        h1,h2,h3,h4,p{
            margin:0;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        thead{
            display: table-header-group;
        }

        tfoot{
            display: table-row-group;
        }

        tr{
            page-break-inside: avoid;
        }

        .monitoring-table{
            table-layout: fixed;
        }

        .monitoring-table th{
            background:#1e3a8a;
            color:white;
            text-align:center;
            font-size:9px;
            padding:7px;
        }

        .monitoring-table td{
            font-size:9px;
            padding:6px;
            vertical-align:middle;
            word-wrap:break-word;
        }

        th{
            background:#1e3a8a;
            color:white;
            padding:8px;
            font-size:10px;
        }

        td{
            border:1px solid #d1d5db;
            padding:7px;
            font-size:10px;
            vertical-align:top;
        }

        .page-break{
            page-break-after:always;
        }

        .title{
            font-size:20px;
            font-weight:bold;
            color:#1e3a8a;
        }

        .subtitle{
            font-size:13px;
            color:#64748b;
            margin-top:6px;
        }

        .section-title{
            font-size:14px;
            font-weight:bold;
            color:#1e3a8a;
            margin-top:22px;
            margin-bottom:10px;
        }

        .meta-table td{
            border:none;
            padding:4px 0;
        }

        .card{
            border:1px solid #cbd5e1;
            padding:12px;
            border-radius:6px;
            background:#f8fafc;
        }

        .card-title{
            font-size:10px;
            color:#64748b;
        }

        .card-value{
            font-size:13px;
            font-weight:bold;
            margin-top:6px;
            color:#0f172a;

        }

        .small-card{
            width:48%;
            display:inline-block;
            border:1px solid #cbd5e1;
            padding:10px;
            box-sizing:border-box;
            vertical-align:top;
            margin-bottom:15px;
        }

        .status-green{
            color:#15803d;
            font-weight:bold;
        }

        .status-yellow{
            color:#ca8a04;
            font-weight:bold;
        }

        .status-red{
            color:#dc2626;
            font-weight:bold;
        }

        .footer{
            margin-top:25px;
            text-align:center;
            font-size:9px;
            color:#6b7280;
        }

        .text-center{
            text-align:center;
        }

        .text-right{
            text-align:right;
        }

        .text-left{
            text-align:left;
        }

        /* style status */
        .badge-green{
            color:#15803d;
            font-weight:bold;
        }

        .badge-yellow{
            color:#ca8a04;
            font-weight:bold;
        }

        .badge-red{
            color:#dc2626;
            font-weight:bold;
        }

    </style>

</head>

<body>
    <!-- COVER -->
    <div style="text-align:center;margin-top:10px;">
        <div style="font-size:14px;color:#64748b;">
            {{ $company }}
        </div>

        <div class="title" style="margin-top:8px;">
            {{ strtoupper($title) }}
        </div>

        <div class="subtitle" style="margin-top:4px;">
            Asset Analytics Monitoring System
        </div>
    </div>

    <!-- INFORMASI LAPORAN -->
    <div class="section-title" style="margin-top:25px;">
        Informasi Laporan
    </div>

    <table class="meta-table">
        <tr>
            <td width="30%">
                Tanggal Export
            </td>

            <td width="5%">
                :
            </td>

            <td>
                {{ $export_date->format('d F Y H:i') }}
            </td>
        </tr>

        <tr>
            <td>
                Periode Data
            </td>

            <td>
                :
            </td>

            <td>
                {{ $periode }}
            </td>
        </tr>

        <tr>
            <td>
                Kelompok Alat
            </td>

            <td>
                :
            </td>

            <td>
                {{ $group_alat }}
            </td>
        </tr>

    </table>

    <!-- KPI -->
    <div class="section-title">
        Ringkasan Monitoring
    </div>
    
    <table style="border:none;">
        <tr style="border:none;">
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">
                        Total Alat
                    </div>
                    
                    <div class="card-value">
                        {{ $summary['total_asset'] ?? 0 }}
                    </div>
                </div>
            </td>
            
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">
                        Total Breakdown
                    </div>
                    
                    <div class="card-value">
                        {{ $summary['total_breakdown'] ?? 0 }}
                    </div>
                </div>
            </td>
            
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">
                        Total Downtime
                    </div>
                    
                    <div class="card-value">
                        {{ number_format((float)($summary['total_downtime'] ?? 0), 2) }} Jam
                    </div>
                </div>
            </td>
        </tr>
    </table>
    
    <table style="border:none;">
        <tr style="border:none;">
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">Asset Sehat</div>
                    <div class="card-value">{{ $summary['healthy'] ?? 0 }}</div>
                </div>
            </td>
            
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">Perlu Perhatian</div>
                    <div class="card-value">{{ $summary['warning'] ?? 0 }}</div>
                </div>
        
            </td>

            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">Prioritas Maintenance</div>
                    <div class="card-value">{{ $summary['critical'] ?? 0 }}</div>
                </div>
            
            </td>
        </tr>
    </table>

    <!-- card rata-rata health score -->
    <div style="margin-top:20px;">
        <div class="card">
            <div class="card-title">Rata-rata Health Score Seluruh Alat</div>
            
            <div style="font-size:28px; font-weight:bold; margin-top:10px; color:#1e3a8a;">
                {{ number_format((float)($summary['average_health_score'] ?? 0), 2) }}
            </div>
        </div>
    </div>

    <!-- RINGKASAN ANALISIS -->
    <div class="section-title">Ringkasan Analisis Sistem</div>

    <div style="border:1px solid #cbd5e1; background:#f8fafc; padding:18px;">
        <p>Berdasarkan hasil analisis terhadap
            <b>{{ $summary['total_asset'] ?? 0 }}</b>
            alat operasional yang tersedia pada database, sistem memperoleh rata-rata
            
            <b>{{ number_format((float)($summary['average_health_score'] ?? 0), 2) }}</b> untuk Health Score.
        </p>
        
        <p>
            Sebanyak 
            <b>{{ $summary['healthy'] ?? 0 }}</b> alat berada dalam kondisi sehat,
            <b>{{ $summary['warning'] ?? 0 }}</b> memerlukan perhatian, serta
            <b>{{ $summary['critical'] ?? 0 }}</b> alat termasuk prioritas maintenance.
        </p>
        
        <p>Total histori breakdown yang dianalisis sebanyak
            <b>{{ $summary['total_breakdown'] ?? 0 }}</b> kejadian dengan akumulasi downtime
            <b>{{ number_format((float)($summary['total_downtime'] ?? 0), 2) }}</b> jam.
        </p>

    </div>

    <!-- HASIL PREDIKSI -->
    <div class="section-title">Analisis Prediksi Machine Learning</div>
    
    <div style="border:1px solid #bfdbfe; background:#eff6ff; padding:18px;">
        <p> Prediksi machine learning pada laporan ini menggunakan data historis terakhir yang tersedia di dalam database.</p>
        
    
        <p>Nilai Predicted Health Score digunakan untuk memperkirakan kondisi alat pada
            <b>{{ $prediction_period ?? '-' }}</b> sedangkan Maintenance Risk Score dihitung menggunakan persamaan:
        </p>
        
        <p style="text-align:center;">
            <b>Maintenance Risk Score = 100 − Predicted Health Score</b>
        </p>

        <p>Semakin tinggi nilai Maintenance Risk Score, semakin tinggi prioritas tindakan maintenance yang direkomendasikan sistem.</p>
    </div>


    <!-- REKOMENDASI -->
    <div class="section-title">Rekomendasi Sistem</div>

    <div style="border:1px solid #dcfce7; background:#f0fdf4; padding:18px;">

    @if(!empty($recommendations) && count($recommendations))
    
    <ol style="margin:0;padding-left:20px;">

    @foreach($recommendations as $item)
    
    <li style="margin-bottom:8px;">{{ $item }}</li>

    @endforeach

    </ol>

    @else

    Tidak terdapat rekomendasi khusus.

    @endif

    </div>

    <div class="page-break"></div>

    <!-- DATA MONITORING -->
    <div class="section-title">
        Data Monitoring Aset
    </div>

    <table class="monitoring-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Alat</th>
                <th>Group</th>
                <th>Periode</th>
                <th>Availability</th>
                <th>Utilisation</th>
                <th>MTBF</th>
                <th>MTTRp</th>
                <th>Health Score</th>
                <th>Total BD</th>
                <th>Downtime</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
        @foreach($rows as $index => $row)
        
        <tr>
            <td>{{ $index+1 }}</td>
            <td>{{ $row->nama_alat }}</td>
            <td>{{ $row->group_alat }}</td>
            <td>{{ $row->periode }}</td>
            <td>{{ number_format((float)($row->availability ?? 0), 2) }}</td>
            <td>{{ number_format((float)($row->utilisation ?? 0), 2) }}</td>
            <td>{{ number_format((float)($row->mtbf ?? 0), 2) }}</td>
            <td>{{ number_format((float)($row->mttrp ?? 0), 2) }}</td>
            <td>{{ number_format((float)($row->health_score ?? 0), 2) }}</td>
            <td>{{ $row->total_bd ?? 0 }}</td>
            <td>{{ number_format((float)($row->total_downtime ?? 0), 2) }}</td>
            <td>{{ $row->status ?? '-' }}</td>

        </tr>
        @endforeach

        </tbody>

    </table>

    <div class="page-break"></div>
    <!-- ANALISIS BREAKDOWN -->
    <div class="section-title">
        Analisis Breakdown
    </div>

    <!-- summaary card analisis breakdown -->
    <table style="border:none;">
        <tr style="border:none;">
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">Total Breakdown</div>
                    
                    <div class="card-value">{{ $breakdownSummary['total_breakdown'] ?? 0 }}</div>
                </div>
            </td>
            
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">Total Downtime</div>
                    
                    <div class="card-value">{{ number_format((float)($breakdownSummary['total_downtime'] ?? 0), 2) }} Jam</div>
                </div>
            </td>
            
            <td style="border:none;width:33%;">
                <div class="card">
                    <div class="card-title">Average Downtime</div>
                    
                    <div class="card-value">{{ number_format((float)($breakdownSummary['average_downtime'] ?? 0), 2) }} Jam</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TOP PROBLEM PARTS -->
    <div class="section-title">
        Top 5 Komponen Paling Sering Mengalami Breakdown
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">No</th>

                <th>Part Group</th>

                <th width="20%">Frekuensi</th>
            </tr>
        </thead>

        <tbody>
        @forelse(($breakdownSummary['top_problem_parts'] ?? []) as $index => $part)
            <tr>
                <td align="center">{{ $index + 1 }}</td>

                <td>{{ $part->part_group ?? '-' }}</td>

                <td align="center">{{ $part->total ?? 0 }}</td>
            </tr>

        @empty
            <tr>
                <td colspan="3" align="center">Tidak terdapat data breakdown.</td>
            </tr>

        @endforelse
        </tbody>
    </table>

    <!-- KESIMPULAN ANALISIS BREAKDOWN -->
    <div class="section-title">Kesimpulan Analisis Breakdown</div>

    <div style="border:1px solid #cbd5e1;background:#f8fafc;padding:16px;">
        @php
            $topPartsList = $breakdownSummary['top_problem_parts'] ?? collect([]);
            $topPart = is_iterable($topPartsList) && count($topPartsList) > 0 ? $topPartsList->first() : null;
        @endphp

        <p>Berdasarkan data historis maintenance yang dianalisis, sistem mencatat
            <b>{{ $breakdownSummary['total_breakdown'] ?? 0 }}</b> kejadian breakdown dengan total downtime
            <b>{{ number_format((float)($breakdownSummary['total_downtime'] ?? 0), 2) }}</b> jam.
        </p>

        <br>
        @if($topPart)

            <p>Komponen yang paling sering mengalami kerusakan adalah
                <b>{{ $topPart->part_group }}</b> sebanyak
                <b>{{ $topPart->total ?? 0 }}</b> kejadian.
            </p>

            <br>
            
            <p>Berdasarkan pola tersebut, sistem merekomendasikan agar komponen tersebut menjadi prioritas inspeksi preventif, monitoring kondisi, dan penyediaan suku cadang sebelum periode operasional berikutnya.</p>

        @else
            <p>Tidak ditemukan histori breakdown pada periode yang dipilih, sehingga belum terdapat komponen yang dapat diidentifikasi sebagai prioritas maintenance.</p>
        @endif

    </div>

    <!-- prediction -->
    <div class="page-break"></div>
    
    <div class="section-title">
        Prediction Result
    </div>
    
    <p style="font-size:10px;">
        Tabel menampilkan hasil prediksi Machine Learning untuk
        <b>Prediction Period</b>
        berdasarkan data historis terakhir yang digunakan oleh model.
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Alat</th>
                <th>Kelompok</th>
                <th>Periode Prediksi</th>
                <th>Predicted Health Score</th>
                <th>Maintenance Risk Score</th>
                <th>Status Prediksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse(($predictionRows ?? []) as $i=>$row)
            <tr>
                <td align="center">
                    {{ $i + 1 }}
                </td>

                <td>
                    {{ $row->nama_alat }}
                </td>

                <td align="center">
                    {{ $row->group_alat }}
                </td>

                <td align="center">
                    {{ $row->prediction_period ?? '-' }}
                </td>

                <td align="center">
                    {{ number_format((float)($row->predicted_health_score ?? 0), 2) }}
                </td>

                <td align="center">
                    {{ number_format((float)($row->maintenance_risk_score ?? 0), 2) }}
                </td>

                <td align="center">
                    {{ $row->prediction_status ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" align="center">Tidak terdapat data prediksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <br>

    <div style="font-size:10px;">
        <b>Keterangan:</b>
        
        <ul>
            <li>Kolom Prediction Period menunjukkan periode target yang diprediksi oleh model Machine Learning berdasarkan data historis terakhir yang digunakan sebagai input prediksi.</li>
            
            <li>Maintenance Risk Score dihitung menggunakan rumus
                <b>100 − Predicted Health Score</b>. Semakin tinggi nilainya maka semakin tinggi prioritas maintenance.
            </li>
        </ul>
    </div>

    <div class="page-break"></div>
    <!-- EXECUTIVE SUMMARY -->
    <div class="section-title">
        Executive Summary
    </div>

    <div style="border:1px solid #cbd5e1;background:#f8fafc;padding:18px;">
        <p>Laporan ini menyajikan hasil monitoring kesehatan aset operasional berdasarkan data historis availability, utilisation, MTBF, MTTRp, histori breakdown, serta hasil prediksi machine learning.</p>
        <br>
        
        <p>Seluruh indikator dianalisis untuk membantu proses pengambilan keputusan maintenance sehingga tindakan perawatan dapat dilakukan secara lebih terarah dan berbasis data.</p>
    </div>

    <!-- OVERALL CONDITION SUMMARY -->
    <div class="section-title">Ringkasan Kondisi Keseluruhan</div>

    <table>
        <thead>
            <tr>
                <th width="65%">
                    Indikator
                </th>

                <th width="35%">
                    Nilai
                </th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Total Asset</td>

                <td align="center">
                    {{ $summary['total_asset'] ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>Average Health Score</td>

                <td align="center">
                    {{ number_format((float)($summary['average_health_score'] ?? 0), 2) }}
                </td>
            </tr>

            <tr>
                <td>Healthy Asset</td>

                <td align="center">
                    {{ $summary['healthy'] ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>Warning Asset</td>

                <td align="center">
                    {{ $summary['warning'] ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>Critical Asset</td>

                <td align="center">
                    {{ $summary['critical'] ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>Total Breakdown</td>

                <td align="center">
                    {{ $summary['total_breakdown'] ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>Total Downtime</td>

                <td align="center">
                    {{ number_format((float)($summary['total_downtime'] ?? 0), 2) }} Jam
                </td>
            </tr>

            <tr>
                <td>Average Maintenance Risk Score</td>

                <td align="center">
                    {{ number_format((float)($summary['average_risk_score'] ?? 0), 2) }}
                </td>
            </tr>
        </tbody>

    </table>

    <!-- OVERALL RECOMMENDATION -->
    <div class="section-title">Rekomendasi Keseluruhan</div>

    @php
        $overallRecs = [];
        $critCount = $summary['critical'] ?? 0;
        $warnCount = $summary['warning'] ?? 0;
        $avgScore = (float) ($summary['average_health_score'] ?? 0);
        $totalBdCount = $summary['total_breakdown'] ?? 0;
        $avgRiskVal = (float) ($summary['average_risk_score'] ?? 0);

        if($critCount > 0){
            $overallRecs[] = "Segera lakukan inspeksi dan tindakan maintenance terhadap aset yang berada pada kategori prioritas tinggi.";
        }

        if($warnCount > 0){
            $overallRecs[] = "Tingkatkan frekuensi monitoring pada aset dengan kondisi kurang sehat untuk mencegah penurunan performa.";
        }

        if($avgScore > 0 && $avgScore < 80){
            $overallRecs[] = "Evaluasi kembali efektivitas program preventive maintenance karena rata-rata Health Score masih berada di bawah target.";
        }

        if($totalBdCount > 0){
            $overallRecs[] = "Analisis histori breakdown untuk mengidentifikasi komponen yang paling sering mengalami kegagalan.";
        }

        if($avgRiskVal >= 40){
            $overallRecs[] = "Maintenance Risk Score yang cukup tinggi menunjukkan perlunya penyusunan jadwal maintenance yang lebih proaktif.";
        }

        if(empty($overallRecs)){
            $overallRecs[] = "Seluruh indikator menunjukkan kondisi operasional yang baik. Pertahankan strategi maintenance yang telah diterapkan.";
        }
    @endphp

    <div style="border:1px solid #cbd5e1;background:#f8fafc;padding:18px;">
        <ol style="margin:0;padding-left:20px;">
            @foreach($overallRecs as $item)
                <li style="margin-bottom:8px;">{{ $item }}</li>
            @endforeach
        </ol>
    </div>

    <!-- PENUTUP LAPORAN -->
    <div class="section-title">Penutup</div>

    <div style="border:1px solid #cbd5e1;background:#f8fafc;padding:18px;">
        <p>Laporan ini dihasilkan secara otomatis oleh
            <b>Asset Analytics Monitoring System</b> berdasarkan data historis operasional, histori breakdown, serta hasil prediksi machine learning yang tersedia pada saat proses export dilakukan.
        </p>
        <br>

        <p>Hasil analisis pada laporan ini dapat digunakan sebagai dasar pendukung (decision support) dalam menentukan prioritas inspeksi, preventive maintenance, maupun evaluasi performa aset operasional.
        </p>
    </div>
    <br><br><br>

    <table style="border:none;width:100%;">
        <tr style="border:none;">
            <td style="border:none;width:60%;"></td>
            
            <td style="border:none;text-align:center;">Makassar,
                {{ $export_date->format('d F Y') }}
                <br><br><br><br><br>
                 ___________________________
                 <br>
                 Administrator
            </td>
        </tr>
    </table>

    <div class="footer">
        <div>Generated by Asset Analytics Monitoring System</div>

        <div style="margin-top:4px;">PT Pelabuhan Indonesia (Persero) Regional 4</div>

        <div style="margin-top:4px;">
            Export Date :
            {{ $export_date
                ->copy()
                ->timezone('Asia/Makassar')
                ->format('d F Y H:i') }}
        </div>

        <div style="margin-top:4px;">Document generated automatically by the system.</div>
    </div>

</body>

</html>


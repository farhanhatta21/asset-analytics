<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Laporan Asset Analytics</title>

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            color:#334155;
            font-size:12px;
            line-height:1.5;
        }

        h1,h2,h3,h4{
            margin:0;
        }

        .header{
            border-bottom:2px solid #1e3a8a;
            padding-bottom:15px;
            margin-bottom:25px;
        }

        .title{
            font-size:24px;
            font-weight:bold;
            color:#1e3a8a;
        }

        .subtitle{
            color:#64748b;
            margin-top:4px;
        }

        .meta{
            margin-top:15px;
            font-size:11px;
            color:#475569;
        }

        .section{
            margin-top:28px;
        }

        .section-title{
            font-size:16px;
            font-weight:bold;
            margin-bottom:12px;
            color:#0f172a;
        }

        .summary-grid{
            width:100%;
            margin-top:10px;
        }

        .summary-box{
            border:1px solid #cbd5e1;
            border-radius:10px;
            padding:12px;
            background:#f8fafc;
            vertical-align:top;
        }

        .summary-label{
            font-size:11px;
            color:#64748b;
        }

        .summary-value{
            font-size:22px;
            font-weight:bold;
            margin-top:5px;
            color:#0f172a;
        }

        .insight-box{
            background:#eff6ff;
            border:1px solid #bfdbfe;
            padding:14px;
            border-radius:10px;
            color:#1e40af;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }

        th{
            background:#1e293b;
            color:white;
            padding:10px;
            font-size:11px;
            text-align:left;
        }

        td{
            border:1px solid #e2e8f0;
            padding:9px;
            font-size:11px;
        }

        tr:nth-child(even){
            background:#f8fafc;
        }

        .status{
            padding:4px 8px;
            border-radius:6px;
            font-size:10px;
            font-weight:bold;
        }

        .healthy{
            background:#dcfce7;
            color:#166534;
        }

        .warning{
            background:#fef3c7;
            color:#92400e;
        }

        .critical{
            background:#fee2e2;
            color:#991b1b;
        }

        .footer{
            margin-top:40px;
            border-top:1px solid #cbd5e1;
            padding-top:10px;
            text-align:center;
            font-size:10px;
            color:#64748b;
        }

    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">

    <div class="title">
        DETAIL ANALISIS ASET
    </div>

    <div class="subtitle">
        {{ $nama }}
    </div>

    <div class="meta">
        <div>
            Tanggal Export:
            <b>{{ now()->format('d M Y H:i') }}</b>
        </div>

        <div>
            Nama Asset :
            <b>{{ $nama }}</b>
        </div>

        <div>
            Status Saat Ini :
            <b>{{ $latest['status'] }}</b>
        </div>
    </div>

</div>

<!-- ========================= -->
<!-- RINGKASAN KONDISI ALAT -->
<!-- ========================= -->

<div class="section">

    <div class="section-title">
        Ringkasan Kondisi Alat
    </div>

    <table style="width:100%; border-collapse:separate; border-spacing:10px 0;">

        <tr>

            <!-- Health Score -->
            <td class="summary-box" style="width:25%;">

                <div class="summary-label">
                    Health Score
                </div>

                <div class="summary-value">
                    {{ number_format((float)($latest['health_score'] ?? 0), 2) }}
                </div>

            </td>

            <!-- Priority -->
            <td class="summary-box" style="width:25%;">

                <div class="summary-label">
                    Priority Score
                </div>

                <div class="summary-value">
                    {{ number_format((float)($latest['priority'] ?? 0), 2) }}
                </div>

            </td>

            <!-- Status -->
            <td class="summary-box" style="width:25%;">

                <div class="summary-label">
                    Status
                </div>

                <div class="summary-value">

                    @if(($latest['status'] ?? '') == 'Sehat')

                        <span class="status healthy">Sehat</span>

                    @elseif(($latest['status'] ?? '') == 'Kurang Sehat')

                        <span class="status warning">Kurang Sehat</span>

                    @else

                        <span class="status critical">Tidak Sehat</span>

                    @endif

                </div>

            </td>

            <!-- Prediction -->
            <td class="summary-box" style="width:25%;">

                <div class="summary-label">
                    Predicted Health Score
                </div>

                <div class="summary-value">

                    @if($prediction && $prediction->predicted_health_score !== null)

                        {{ number_format((float)$prediction->predicted_health_score, 2) }}

                    @else

                        -

                    @endif

                </div>

            </td>

        </tr>

</table>

</div>

<!-- PARAMETER KINERJA -->

<div class="section">

    <div class="section-title">
        Parameter Kinerja
    </div>

    <table>

        <tr>
            <th>Parameter</th>
            <th>Nilai</th>
        </tr>

        <tr>
            <td>Availability</td>
            <td>{{ number_format((float)($latest['availability'] ?? 0) * 100, 2) }} %</td>
        </tr>

        <tr>
            <td>Utilisation</td>
            <td>{{ number_format((float)($latest['utilisation'] ?? 0) * 100, 2) }} %</td>
        </tr>

        <tr>
            <td>MTBF</td>
            <td>{{ number_format((float)($latest['mtbf'] ?? 0), 2) }}</td>
        </tr>

        <tr>
            <td>MTTRp</td>
            <td>{{ number_format((float)($latest['mttrp'] ?? 0), 2) }}</td>
        </tr>

    </table>

</div>

<!-- predictive maintenance insight -->
<div class="section">

    <div class="section-title">
        Predictive Maintenance Insight
    </div>

    <table>

        <tr>
            <th>Parameter</th>
            <th>Hasil</th>
        </tr>

        <tr>
            <td>Predicted Health Score</td>

            <td>
                {{ ($prediction && $prediction->predicted_health_score !== null)
                    ? number_format((float)$prediction->predicted_health_score, 2)
                    : '-' }}
            </td>

        </tr>

        <tr>

            <td>Maintenance Risk Score</td>

            <td>

                {{ ($prediction && $prediction->maintenance_risk_score !== null)
                    ? number_format((float)$prediction->maintenance_risk_score, 2)
                    : '-' }}

            </td>

        </tr>

        <tr>

            <td>Status Prediksi</td>

            <td>

                @if($prediction && $prediction->predicted_health_score !== null)

                    @if($prediction->predicted_health_score>=80)

                        LOW RISK

                    @elseif($prediction->predicted_health_score>=60)

                        MEDIUM RISK

                    @else

                        HIGH RISK

                    @endif

                @else

                    -

                @endif

            </td>

        </tr>

    </table>

</div>

<!-- riwayat performa alat -->
<div class="section">

    <div class="section-title">
        Riwayat Performa Alat
    </div>

    <table>

        <tr>

            <th>Periode</th>

            <th>Health</th>

            <th>Availability</th>

            <th>Utilisation</th>

            <th>MTBF</th>

            <th>MTTRp</th>

            <th>Status</th>

        </tr>

        @foreach(($data ?? []) as $item)

        <tr>

            <td>{{ $item->periode }}</td>

            <td>{{ number_format((float)($item->health_score ?? 0), 2) }}</td>

            <td>{{ number_format((float)($item->availability ?? 0) * 100, 2) }}%</td>

            <td>{{ number_format((float)($item->utilisation ?? 0) * 100, 2) }}%</td>

            <td>{{ number_format((float)($item->mtbf ?? 0), 2) }}</td>

            <td>{{ number_format((float)($item->mttrp ?? 0), 2) }}</td>

            <td>{{ $item->status ?? '-' }}</td>

        </tr>

        @endforeach

    </table>

</div>


<!--  -->

<!-- FOOTER -->
<div class="footer">
    Generated by Asset Analytics Monitoring System
</div>

</body>
</html>
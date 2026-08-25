@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Detail Asset: {{ $asset['nama'] }}</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Status</h5>
                <span class="badge 
                    {{ $asset['status'] === 'Sehat' ? 'bg-success' :
                       ($asset['status'] === 'Kurang Sehat' ? 'bg-warning' : 'bg-danger') }}">
                    {{ $asset['status'] }}
                </span>

                <hr>

                <h6>Health Score</h6>
                <h3>{{ $asset['health_score'] }}%</h3>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3">
                <h5>Parameter Penilaian</h5>

                <table class="table">
                    <tr>
                        <th>Availability</th>
                        <td>{{ $asset['availability'] }}%</td>
                    </tr>
                    <tr>
                        <th>Utilisation</th>
                        <td>{{ $asset['utilisation'] }}%</td>
                    </tr>
                    <tr>
                        <th>MTBF</th>
                        <td>{{ $asset['mtbf'] }}</td>
                    </tr>
                    <tr>
                        <th>MTTRp</th>
                        <td>{{ $asset['mttrp'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">
        ⬅ Kembali
    </a>
</div>
@endsection

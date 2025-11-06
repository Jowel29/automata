@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Extracted Data</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                @foreach (array_keys($extractedData[0]) as $header)
                    <th>{{ ucfirst($header) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($extractedData as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('pdf.export') }}" class="btn btn-success mt-3">Export to CSV</a>
</div>
@endsection

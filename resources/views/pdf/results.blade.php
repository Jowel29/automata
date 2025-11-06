@extends('layouts.app')

@section('content')
<div class="content-card">
    <h2 class="section-title">
        <i class="fas fa-table"></i>
        Extracted Data
    </h2>

    @if(empty($extractedData) || count($extractedData) === 0)
        <div class="modern-alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div><strong>No Data!</strong> No data was extracted from the PDFs.</div>
        </div>
    @else
        <div class="modern-table">
            <table>
                <thead>
                    <tr>
                        @foreach(array_keys($extractedData[0]) as $header)
                            <th>{{ ucfirst($header) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($extractedData as $row)
                        <tr>
                            @foreach($row as $key => $cell)
                                <td>
                                    @if($key === 'status')
                                        <span class="status-badge {{ strtolower($cell) === 'success' ? 'status-success' : 'status-error' }}">
                                            {{ $cell }}
                                        </span>
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('pdf.export') }}" class="modern-btn btn-success-modern">
                <i class="fas fa-download"></i>
                Export to CSV
            </a>
            <a href="{{ route('pdf.upload') }}" class="modern-btn btn-secondary-modern">
                <i class="fas fa-redo"></i>
                Start Over
            </a>
        </div>
    @endif
</div>
@endsection

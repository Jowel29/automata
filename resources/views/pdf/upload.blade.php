@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Upload PDF Files</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pdf.upload.post') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="pdfs" class="form-label">Select PDF Files (Max 20MB each)</label>
            <input type="file" name="pdfs[]" id="pdfs" class="form-control" multiple accept=".pdf" required>
            <small class="text-muted">You can select multiple files</small>
        </div>
        <button type="submit" class="btn btn-primary">Upload & Continue</button>
    </form>
</div>
@endsection

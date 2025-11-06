@extends('layouts.app')

@section('content')
<div class="content-card">
    <h2 class="section-title">
        <i class="fas fa-cloud-upload-alt"></i>
        Upload PDF Files
    </h2>

    @if(session('success'))
        <div class="modern-alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div><strong>Success!</strong> {{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="modern-alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div><strong>Error!</strong> {{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="modern-alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Validation Error!</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('pdf.upload.post') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="upload-zone" onclick="document.getElementById('pdfs').click()">
            <div class="upload-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <h3>Drop your PDF files here</h3>
            <p>or click to browse • Max 20MB per file</p>
        </div>

        <input type="file" name="pdfs[]" id="pdfs" class="file-input" multiple accept=".pdf" required>

        <div class="d-flex gap-3 mt-4">
            <button type="submit" class="modern-btn btn-primary-modern">
                <i class="fas fa-arrow-right"></i>
                Upload & Continue
            </button>
        </div>
    </form>
</div>

<script>
    const fileInput = document.getElementById('pdfs');
    const uploadZone = document.querySelector('.upload-zone');

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const fileList = Array.from(this.files).map(f => f.name).join(', ');
            uploadZone.querySelector('p').textContent = `Selected: ${fileList}`;
        }
    });
</script>
@endsection

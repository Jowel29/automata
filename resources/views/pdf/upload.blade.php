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

    <form action="{{ route('pdf.upload.post') }}" method="POST" enctype="multipart/form-data" id="upload-form">
        @csrf
        
        <div class="upload-zone" onclick="document.getElementById('pdfs').click()">
            <div class="upload-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <h3>Drop your PDF files here</h3>
            <p>or click to browse • Max 20MB per file • Multiple folders supported</p>
        </div>
        
        <input type="file" name="pdfs[]" id="pdfs" class="file-input" multiple accept=".pdf" webkitdirectory directory>

        <div id="selected-files" class="file-list" style="display: none;">
            <h5 style="font-weight: 700; color: var(--dark); margin-bottom: 1rem;">
                <i class="fas fa-check-circle" style="color: var(--success);"></i>
                Selected Files (<span id="file-count">0</span>)
            </h5>
            <div id="files-container"></div>
        </div>

        <div class="d-flex gap-3 mt-4" id="upload-actions" style="display: none !important;">
            <button type="submit" class="modern-btn btn-primary-modern">
                <i class="fas fa-arrow-right"></i>
                Upload & Continue
            </button>
            <button type="button" class="modern-btn btn-secondary-modern" onclick="clearAllFiles()">
                <i class="fas fa-trash"></i>
                Clear All
            </button>
        </div>
    </form>
</div>

<script>
    let selectedFiles = [];
    const fileInput = document.getElementById('pdfs');
    const filesContainer = document.getElementById('files-container');
    const selectedFilesDiv = document.getElementById('selected-files');
    const uploadActions = document.getElementById('upload-actions');
    const fileCount = document.getElementById('file-count');

    fileInput.removeAttribute('webkitdirectory');
    fileInput.removeAttribute('directory');

    fileInput.addEventListener('change', function(e) {
        const newFiles = Array.from(e.target.files);
        
        newFiles.forEach(file => {
            const exists = selectedFiles.some(f => 
                f.name === file.name && f.size === file.size
            );
            
            if (!exists) {
                selectedFiles.push(file);
            }
        });

        displayFiles();
        fileInput.value = ''; 
    });

    function displayFiles() {
        filesContainer.innerHTML = '';
        
        if (selectedFiles.length === 0) {
            selectedFilesDiv.style.display = 'none';
            uploadActions.style.display = 'none';
            return;
        }

        selectedFilesDiv.style.display = 'block';
        uploadActions.style.display = 'flex';
        fileCount.textContent = selectedFiles.length;

        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                    ${file.webkitRelativePath ? `<div class="file-path">${file.webkitRelativePath}</div>` : ''}
                </div>
                <button type="button" class="file-remove" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            filesContainer.appendChild(fileItem);
        });
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        displayFiles();
    }

    function clearAllFiles() {
        if (confirm('Are you sure you want to clear all selected files?')) {
            selectedFiles = [];
            displayFiles();
        }
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    document.getElementById('upload-form').addEventListener('submit', function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            alert('Please select at least one PDF file!');
            return;
        }

        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;
    });
</script>
@endsection
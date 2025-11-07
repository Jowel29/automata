@extends('layouts.app')

@section('content')
<div class="content-card">
    @if(session('error'))
        <div class="modern-alert alert-danger" style="margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <div><strong>Error!</strong> {{ session('error') }}</div>
        </div>
    @endif

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

        <div class="export-options">
            <h5 style="font-weight: 700; color: var(--dark); margin-bottom: 1rem;">
                <i class="fas fa-file-export"></i> Export Options
            </h5>
            
            <div id="export-options-container">
                <div class="export-option" onclick="selectExportOption('new')">
                    <input type="radio" name="export_type" value="new" id="export-new" checked>
                    <label for="export-new">
                        <div class="export-option-title">
                            <i class="fas fa-file-csv" style="color: var(--success);"></i>
                            Create New CSV File
                        </div>
                        <div class="export-option-desc">
                            Generate a new CSV file with extracted data
                        </div>
                    </label>
                </div>

                <div class="export-option" onclick="selectExportOption('existing')">
                    <input type="radio" name="export_type" value="existing" id="export-existing">
                    <label for="export-existing">
                        <div class="export-option-title">
                            <i class="fas fa-file-excel" style="color: var(--warning);"></i>
                            Append to Existing Excel/CSV
                        </div>
                        <div class="export-option-desc">
                            Add data to an existing Excel or CSV file
                        </div>
                    </label>
                </div>

                <div id="file-upload-section" style="display: none; margin-top: 1rem;">
                    <label class="input-label">Select Existing File</label>
                    <input type="file" name="existing_file" id="existing-file" 
                           accept=".csv,.xlsx,.xls" class="modern-input">
                    <small style="color: #64748b; display: block; margin-top: 0.5rem;">
                        <i class="fas fa-info-circle"></i> 
                        Supported formats: CSV, Excel (.xlsx, .xls)
                    </small>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="button" onclick="exportData()" class="modern-btn btn-success-modern">
                <i class="fas fa-download"></i>
                Export Data
            </button>
            <a href="{{ route('pdf.fields') }}" class="modern-btn btn-secondary-modern">
                <i class="fas fa-redo"></i>
                Extract Again
            </a>
            <a href="{{ route('pdf.upload') }}" class="modern-btn btn-secondary-modern">
                <i class="fas fa-upload"></i>
                Upload New PDFs
            </a>
        </div>
    @endif
</div>

<script>
    function selectExportOption(type) {
        document.getElementById('export-' + type).checked = true;
        
        const fileUploadSection = document.getElementById('file-upload-section');
        if (type === 'existing') {
            fileUploadSection.style.display = 'block';
        } else {
            fileUploadSection.style.display = 'none';
        }
    }

    function exportData() {
        const exportType = document.querySelector('input[name="export_type"]:checked').value;
        
        if (exportType === 'existing') {
            const fileInput = document.getElementById('existing-file');
            
            if (!fileInput.files.length) {
                alert('Please select an existing file to append data to!');
                return;
            }
            
            console.log('File selected:', fileInput.files[0].name);
            
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('type', 'existing');
            formData.append('existing_file', fileInput.files[0]);
            
            fetch('{{ route("pdf.export") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);
                
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Server error');
                    }).catch(err => {
                        return response.text().then(text => {
                            console.error('Response text:', text);
                            throw new Error('Server error: ' + response.status);
                        });
                    });
                }
                
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Unknown error');
                    });
                }
                
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'merged_data_' + new Date().getTime() + '.csv';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();
                
                alert('File merged and downloaded successfully!');
            })
            .catch(error => {
                console.error('Export error:', error);
                alert('Failed to export: ' + error.message);
            });
            
        } else {
            window.location.href = "{{ route('pdf.export') }}?type=new";
        }
    }
</script>
@endsection
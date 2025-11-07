@extends('layouts.app')

@section('content')
<div class="content-card">
    <h2 class="section-title">
        <i class="fas fa-tasks"></i>
        Define Fields to Extract
    </h2>

    @if(session('uploaded_pdfs'))
        <div class="file-list mb-4">
            <h5 style="font-weight: 700; color: var(--dark); margin-bottom: 1rem;">
                <i class="fas fa-check-circle" style="color: var(--success);"></i> 
                Uploaded Files ({{ count(session('uploaded_pdfs')) }})
            </h5>
            @foreach(session('uploaded_pdfs') as $pdf)
                <div class="file-item">
                    <div class="file-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="file-info">
                        <div class="file-name">{{ $pdf['original_name'] }}</div>
                        <div class="file-size">{{ number_format($pdf['size'] / 1024, 2) }} KB</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h5 style="font-weight: 700; color: var(--dark); margin-bottom: 1rem;">
        <i class="fas fa-bolt" style="color: var(--warning);"></i> Quick Templates
    </h5>
    <div class="template-grid">
        <div class="template-card" onclick="loadTemplate('personal')">
            <div class="template-icon">👤</div>
            <div class="template-title">Personal Info</div>
            <div class="template-desc">Name, Age, Gender</div>
        </div>
        <div class="template-card" onclick="loadTemplate('contact')">
            <div class="template-icon">📞</div>
            <div class="template-title">Contact Info</div>
            <div class="template-desc">Name, Phone, Email</div>
        </div>
        <div class="template-card" onclick="loadTemplate('full')">
            <div class="template-icon">📋</div>
            <div class="template-title">Full Profile</div>
            <div class="template-desc">All common fields</div>
        </div>
    </div>

    <form action="{{ route('pdf.extract') }}" method="POST">
        @csrf
        <div id="fields-container">
            <div class="field-item">
                <div class="field-header">
                    <div class="field-number">1</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="input-label">Field Name</label>
                        <input type="text" name="fields[0][name]" placeholder="e.g., Name" class="modern-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="input-label">Start Keyword</label>
                        <input type="text" name="fields[0][start_keyword]" placeholder="e.g., Name:" class="modern-input">
                    </div>
                    <div class="col-md-4">
                        <label class="input-label">End Keyword (Optional)</label>
                        <input type="text" name="fields[0][end_keyword]" placeholder="e.g., Age:" class="modern-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mt-4">
            <button type="button" id="add-field" class="modern-btn btn-secondary-modern">
                <i class="fas fa-plus"></i>
                Add Field
            </button>
            <button type="submit" class="modern-btn btn-primary-modern">
                <i class="fas fa-magic"></i>
                Extract Data
            </button>
        </div>
    </form>
</div>

<script>
    let fieldIndex = 1;

    document.getElementById('add-field').addEventListener('click', () => {
        const container = document.getElementById('fields-container');
        const div = document.createElement('div');
        div.className = 'field-item';
        div.setAttribute('data-index', fieldIndex);
        
        div.innerHTML = `
            <div class="field-header">
                <div class="field-number">${fieldIndex + 1}</div>
                <button type="button" class="remove-field" onclick="removeField(${fieldIndex})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="input-label">Field Name</label>
                    <input type="text" name="fields[${fieldIndex}][name]" placeholder="e.g., Name" class="modern-input" required>
                </div>
                <div class="col-md-4">
                    <label class="input-label">Start Keyword</label>
                    <input type="text" name="fields[${fieldIndex}][start_keyword]" placeholder="e.g., Name:" class="modern-input">
                </div>
                <div class="col-md-4">
                    <label class="input-label">End Keyword (Optional)</label>
                    <input type="text" name="fields[${fieldIndex}][end_keyword]" placeholder="e.g., Age:" class="modern-input">
                </div>
            </div>
        `;
        
        container.appendChild(div);
        fieldIndex++;
    });

    function removeField(index) {
        const field = document.querySelector(`[data-index="${index}"]`);
        if (field) field.remove();
    }

    function loadTemplate(type) {
        const container = document.getElementById('fields-container');
        container.innerHTML = '';
        fieldIndex = 0;

        const templates = {
            personal: [
                {name: 'Name', start: 'Name:', end: ''},
                {name: 'Age', start: 'Age:', end: ''},
                {name: 'Gender', start: 'Gender:', end: ''}
            ],
            contact: [
                {name: 'Name', start: 'Name:', end: ''},
                {name: 'Phone', start: 'Phone:', end: ''},
                {name: 'Email', start: 'Email:', end: ''}
            ],
            full: [
                {name: 'Name', start: 'Name:', end: ''},
                {name: 'Age', start: 'Age:', end: ''},
                {name: 'Gender', start: 'Gender:', end: ''},
                {name: 'Phone', start: 'Phone:', end: ''},
                {name: 'Email', start: 'Email:', end: ''},
                {name: 'Address', start: 'Address:', end: ''}
            ]
        };

        templates[type].forEach((field, i) => {
            const div = document.createElement('div');
            div.className = 'field-item';
            div.innerHTML = `
                <div class="field-header">
                    <div class="field-number">${i + 1}</div>
                    ${i > 0 ? `<button type="button" class="remove-field" onclick="this.closest('.field-item').remove()"><i class="fas fa-times"></i></button>` : ''}
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="input-label">Field Name</label>
                        <input type="text" name="fields[${i}][name]" value="${field.name}" class="modern-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="input-label">Start Keyword</label>
                        <input type="text" name="fields[${i}][start_keyword]" value="${field.start}" class="modern-input">
                    </div>
                    <div class="col-md-4">
                        <label class="input-label">End Keyword (Optional)</label>
                        <input type="text" name="fields[${i}][end_keyword]" value="${field.end}" class="modern-input">
                    </div>
                </div>
            `;
            container.appendChild(div);
        });

        fieldIndex = templates[type].length;
    }
</script>
@endsection
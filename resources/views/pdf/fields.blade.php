@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Define Fields to Extract</h2>


    <p>Uploaded PDFs:</p>
    <ul>
        @foreach ($uploadedPdfs as $pdf)
            <li>{{ $pdf }}</li>
        @endforeach
    </ul>


    <form action="{{ route('pdf.extract') }}" method="POST">
        @csrf

        <div id="fields-container">
            <div class="field-item mb-3">
                <input type="text" name="fields[0][name]" placeholder="Field Name (e.g., Title)" class="form-control mb-2" required>
                <input type="text" name="fields[0][start_keyword]" placeholder="Start Keyword (e.g., Title:)" class="form-control mb-2">
                <input type="text" name="fields[0][end_keyword]" placeholder="End Keyword (optional)" class="form-control">
            </div>
        </div>

        <button type="button" id="add-field" class="btn btn-secondary mt-2">+ Add Field</button>
        <button type="submit" class="btn btn-primary mt-2">Extract Data</button>
    </form>
</div>

<script>
    let fieldIndex = 1;
    document.getElementById('add-field').addEventListener('click', () => {
        const container = document.getElementById('fields-container');
        const div = document.createElement('div');
        div.classList.add('field-item', 'mb-3');
        div.innerHTML = `
            <input type="text" name="fields[${fieldIndex}][name]" placeholder="Field Name" class="form-control mb-2" required>
            <input type="text" name="fields[${fieldIndex}][start_keyword]" placeholder="Start Keyword" class="form-control mb-2">
            <input type="text" name="fields[${fieldIndex}][end_keyword]" placeholder="End Keyword (optional)" class="form-control">
        `;
        container.appendChild(div);
        fieldIndex++;
    });
</script>
@endsection

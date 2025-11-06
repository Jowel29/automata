<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload PDF Files</title>
</head>
<body>
    <h1>Upload PDF Files</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <ul style="color: red;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('pdf.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="pdfs">Select PDF files:</label><br><br>
        <input type="file" name="pdfs[]" id="pdfs" multiple accept="application/pdf" required><br><br>

        <button type="submit">Upload</button>
    </form>
</body>
</html>

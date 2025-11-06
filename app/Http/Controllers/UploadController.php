<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPDFRequest;
use Illuminate\Http\Request;
use App\Services\PDFService;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function showUploadForm()
    {
        return view('pdf.upload');
    }
    /****************************** */
    public function uploadPDFs(UploadPDFRequest $request)
    {
        $uploadedFiles = [];
        $pdfDirectory = storage_path('app/public/pdf');

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0775, true);
        }

        foreach ($request->file('pdfs') as $file) {
            $filename = $file->getClientOriginalName();
            $file->move($pdfDirectory, $filename);
            $uploadedFiles[] = $filename;
        }

        session(['uploaded_pdfs' => $uploadedFiles]);

        return redirect()->route('pdf.fields')->with('success', 'Files have been uploaded successfully!');
    }
}

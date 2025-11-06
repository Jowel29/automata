<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPDFRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function showUploadForm()
    {
        return view('pdf.upload');
    }

    public function uploadPDFs(UploadPDFRequest $request)
    {
        $uploadedFiles = [];

        try {
            foreach ($request->file('pdfs') as $file) {
                $filename = time().'_'.Str::random(10).'.pdf';

                $path = $file->storeAs('pdf', $filename, 'public');

                $uploadedFiles[] = [
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'path' => $path,
                ];
            }

            session(['uploaded_pdfs' => $uploadedFiles]);

            return redirect()->route('pdf.fields')
                ->with('success', count($uploadedFiles).' file(s) uploaded successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Upload failed: '.$e->getMessage())
                ->withInput();
        }
    }

    public function clearUploadedFiles()
    {
        if (session()->has('uploaded_pdfs')) {
            foreach (session('uploaded_pdfs') as $pdf) {
                Storage::disk('public')->delete($pdf['path']);
            }
            session()->forget('uploaded_pdfs');
        }
    }
}

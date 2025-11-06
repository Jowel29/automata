<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PDFService
{
    public function saveUploadedFiles(array $pdfs): array
    {
        $savedFiles = [];

        foreach ($pdfs as $pdf) {
            /** @var UploadedFile $pdf */
            $filename = time() . '_' . $pdf->getClientOriginalName();
            $pdf->storeAs('public/pdf', $filename);
            $savedFiles[] = $filename;
        }

        return $savedFiles;
    }
}

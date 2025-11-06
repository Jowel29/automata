<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ExtractController extends Controller
{
    public function showFieldsForm()
    {

        $uploadedPdfs = session('uploaded_pdfs', []);


        if (empty($uploadedPdfs)) {
            return redirect()->route('pdf.upload')->with('error', 'Please upload at least one PDF first.');
        }


        return view('pdf.fields', compact('uploadedPdfs'));
    }

    /************************************ */
    public function extractData(Request $request)
    {
        $fields = $request->input('fields', []);
        $uploadedPdfs = session('uploaded_pdfs', []);


        if (empty($uploadedPdfs) || empty($fields)) {
            return redirect()->back()->with('error', 'Please upload PDFs and define fields first.');
        }

        $extractedData = [];

        foreach ($uploadedPdfs as $pdfFile) {
            $pdfPath = storage_path('app/public/pdf/' . $pdfFile);

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();

            $pdfResult = ['file' => $pdfFile];

            foreach ($fields as $field) {
                $start = $field['start_keyword'] ?? null;
                $end = $field['end_keyword'] ?? null;

                $value = null;

                if ($start) {
                    $pattern = '/' . preg_quote($start, '/') . '(.*?)' . ($end ? preg_quote($end, '/') : '$') . '/su';
                    if (preg_match($pattern, $text, $matches)) {
                        $value = trim($matches[1]);
                    }
                }

                $pdfResult[$field['name']] = $value ?? 'Not found';
            }

            $extractedData[] = $pdfResult;
        }


        session(['extracted_data' => $extractedData]);

        return view('pdf.results', compact('extractedData'));
    }
}

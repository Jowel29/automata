<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class ExtractController extends Controller
{
    public function showFieldsForm()
    {
        $uploadedPdfs = session('uploaded_pdfs', []);

        if (empty($uploadedPdfs)) {
            return redirect()->route('pdf.upload')
                ->with('error', 'Please upload at least one PDF first.');
        }

        return view('pdf.fields', compact('uploadedPdfs'));
    }

    public function extractData(Request $request)
    {
        $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.start_keyword' => 'required|string',
        ]);

        $fields = $request->input('fields', []);
        $uploadedPdfs = session('uploaded_pdfs', []);

        if (empty($uploadedPdfs) || empty($fields)) {
            return redirect()->back()
                ->with('error', 'Please upload PDFs and define fields first.');
        }

        $extractedData = [];
        $parser = new Parser;

        foreach ($uploadedPdfs as $pdfInfo) {
            $filename = is_array($pdfInfo) ? $pdfInfo['filename'] : $pdfInfo;
            $originalName = is_array($pdfInfo) ? $pdfInfo['original_name'] : $pdfInfo;
            $pdfPath = Storage::disk('public')->path('pdf/'.$filename);

            try {
                if (! file_exists($pdfPath)) {
                    $extractedData[] = $this->createErrorRow($originalName, 'File not found', $fields);

                    continue;
                }

                $pdf = $parser->parseFile($pdfPath);
                $text = $pdf->getText();

                if (empty(trim($text))) {
                    $extractedData[] = $this->createErrorRow($originalName, 'Empty PDF', $fields);

                    continue;
                }

                $pdfResult = [
                    'file' => $originalName,
                    'status' => 'Success',
                ];

                foreach ($fields as $field) {
                    $pdfResult[$field['name']] = $this->extractFieldValue(
                        $text,
                        $field['start_keyword']
                    );
                }

                $extractedData[] = $pdfResult;

            } catch (\Exception $e) {
                $extractedData[] = $this->createErrorRow($originalName, 'Error: '.$e->getMessage(), $fields);
            }
        }

        session(['extracted_data' => $extractedData]);

        return redirect()->route('pdf.results');
    }

    public function showResults()
    {
        $extractedData = session('extracted_data', []);

        if (empty($extractedData)) {
            return redirect()->route('pdf.fields')
                ->with('error', 'No extracted data found. Please extract data first.');
        }

        return view('pdf.results', compact('extractedData'));
    }

    private function extractFieldValue(string $text, string $keyword): string
    {
        if (empty($text) || empty($keyword)) {
            return 'Not found';
        }

        $keyword = trim($keyword);

        $pattern = '/'.preg_quote($keyword, '/').'\s*([^\n\r]+)/ui';

        if (preg_match($pattern, $text, $matches)) {
            $value = trim($matches[1]);

            $value = preg_split('/\s+(Name:|Age:|Gender:|Phone:|Email:|Address:|ID:|Code:)/i', $value, 2)[0];
            $value = trim($value);

            return empty($value) ? 'Empty' : $value;
        }

        $pattern = '/'.preg_quote($keyword, '/').'\s*([^\n\r]+)/i';

        if (preg_match($pattern, $text, $matches)) {
            $value = trim($matches[1]);
            $value = preg_split('/\s+(Name:|Age:|Gender:|Phone:|Email:|Address:|ID:|Code:)/i', $value, 2)[0];
            $value = trim($value);

            return empty($value) ? 'Empty' : $value;
        }

        return 'Not found';
    }

    private function createErrorRow(string $filename, string $errorMsg, array $fields): array
    {
        $row = [
            'file' => $filename,
            'status' => $errorMsg,
        ];

        foreach ($fields as $field) {
            $row[$field['name']] = 'N/A';
        }

        return $row;
    }

    private function advancedExtract(string $text, string $keyword, ?string $endKeyword = null): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $line) {
            $line = trim($line);

            if (stripos($line, $keyword) !== false) {
                $value = preg_replace('/'.preg_quote($keyword, '/').'/i', '', $line);
                $value = trim($value);

                if ($endKeyword) {
                    $value = explode($endKeyword, $value)[0];
                    $value = trim($value);
                }

                return empty($value) ? 'Empty' : $value;
            }
        }

        return 'Not found';
    }
}

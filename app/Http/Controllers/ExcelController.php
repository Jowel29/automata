<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function downloadExcel(Request $request)
    {
        $data = session('extracted_data', []);

        if (empty($data)) {
            return redirect()->route('pdf.fields')->with('error', 'No data to export. Please extract data first.');
        }

        $exportType = $request->get('type', 'new');

        if ($request->isMethod('post') && $request->hasFile('existing_file')) {
            return $this->appendToExistingFile($request, $data);
        }

        if ($exportType === 'existing') {
            return redirect()->route('pdf.results')->with('error', 'Please select a file to append data to.');
        }

        return $this->createNewFile($data);
    }

    private function createNewFile($data)
    {
        $filename = 'extracted_data_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if (! empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function appendToExistingFile(Request $request, $newData)
    {
        try {
            $request->validate([
                'existing_file' => 'required|file|mimetypes:text/csv,text/plain,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:10240',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed: '.json_encode($e->errors())], 422);
        }

        $existingFile = $request->file('existing_file');
        $extension = strtolower($existingFile->getClientOriginalExtension());

        $existingData = [];

        try {
            if ($extension === 'csv') {
                $existingData = $this->readCSV($existingFile);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $existingData = $this->readExcel($existingFile);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to read existing file: '.$e->getMessage()], 400);
        }

        if (! empty($existingData) && ! empty($newData)) {
            $existingColumns = array_keys($existingData[0]);
            $newColumns = array_keys($newData[0]);

            if ($existingColumns !== $newColumns) {
                $error = 'Columns mismatch! Existing: ['.implode(', ', $existingColumns).'] | New: ['.implode(', ', $newColumns).']';

                return response()->json(['error' => $error], 400);
            }
        }

        $mergedData = array_merge($existingData, $newData);
        $mergedData = array_map('unserialize', array_unique(array_map('serialize', $mergedData)));

        $originalFilename = pathinfo($existingFile->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $originalFilename.'_merged_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($mergedData) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if (! empty($mergedData)) {
                fputcsv($file, array_keys($mergedData[0]));
            }

            foreach ($mergedData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function readCSV($file)
    {
        $data = [];
        $path = $file->getRealPath();

        if (($handle = fopen($path, 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            $headers = fgetcsv($handle);

            if ($headers === false) {
                fclose($handle);
                throw new \Exception('Empty or corrupted file');
            }

            $rowCount = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (count($headers) === count($row)) {
                    $data[] = array_combine($headers, $row);
                    $rowCount++;
                } else {
                    \Log::warning('Row skipped - column mismatch', [
                        'expected' => count($headers),
                        'got' => count($row),
                        'row' => $row,
                    ]);
                }
            }

            fclose($handle);
        } else {
            throw new \Exception('Could not open file');
        }

        return $data;
    }

    private function readExcel($file)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows)) {
                throw new \Exception('The File is Empty');
            }

            $headers = array_shift($rows);
            $data = [];

            foreach ($rows as $row) {
                if (array_filter($row)) {
                    if (count($headers) === count($row)) {
                        $data[] = array_combine($headers, $row);
                    }
                }
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('An Error occurred in reading an Excel file : '.$e->getMessage());
        }
    }
}

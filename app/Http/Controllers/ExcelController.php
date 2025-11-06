<?php

namespace App\Http\Controllers;

class ExcelController extends Controller
{
    public function downloadExcel()
    {
        $data = session('extracted_data', []);

        if (empty($data)) {
            return redirect()->back()
                ->with('error', 'No data to export.');
        }

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
}

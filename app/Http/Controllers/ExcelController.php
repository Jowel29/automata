<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ExcelController extends Controller
{
    public function downloadExcel()
    {

        $data = session('extracted_data', []);

        if (empty($data)) {
            return redirect()->back()->with('error', 'No data to export.');
        }

        $filename = 'extracted_data_' . time() . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');


            if (!empty($data)) {
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

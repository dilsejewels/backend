<?php

namespace App\Http\Controllers;

use App\Imports\CombinedProductImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductImportController extends Controller
{
    public function showForm()
    {
        return view('import.products'); // Your blade file name
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:10240' // 10MB
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $import = new CombinedProductImport();
            
            Excel::import($import, $request->file('file'));
            
            $stats = $import->getStats();
            
            $message = "Import successful! {$stats['imported']} products imported.";
            if ($stats['failed'] > 0) {
                $message .= " {$stats['failed']} rows failed.";
            }
            
            return back()->with([
                'success' => $message,
                'stats' => [
                    'imported' => $stats['imported'],
                    'failed' => $stats['failed']
                ]
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
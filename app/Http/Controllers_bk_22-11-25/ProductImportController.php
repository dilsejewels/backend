<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductMasterImport;

class ProductImportController extends Controller
{
    public function showForm()
    {
        return view('import.products');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240' // 10MB
        ]);

        try {
            Excel::import(new ProductMasterImport, $request->file('file'));
            return back()->with('success', 'सभी डेटा सफलतापूर्वक इम्पोर्ट हो गया!');
        } catch (\Exception $e) {
            return back()->with('error', 'त्रुटि: ' . $e->getMessage());
        }
    }
}
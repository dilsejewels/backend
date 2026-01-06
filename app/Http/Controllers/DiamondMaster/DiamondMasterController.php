<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Models\DiamondCulet;
use App\Models\DiamondCut;
use App\Models\DiamondFancyColor;
use App\Models\DiamondFancyColorIntensity;
use App\Models\DiamondFlourescence;
use App\Models\DiamondLab;
use App\Models\DiamondColor;
use App\Models\DiamondPolish;
use App\Models\DiamondShape;
use App\Models\DiamondSymmetry;
use Illuminate\Http\Request;
use App\Models\DiamondMaster;
use App\Models\DiamondVendor;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\DiamondClarityMaster;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

// Add these imports for Excel
use App\Exports\DiamondMasterExport;
use App\Imports\DiamondMasterImport;
use Maatwebsite\Excel\Facades\Excel;

class DiamondMasterController extends Controller
{
    public function index()
    {
        return view('admin.DiamondMaster.master.index', [
            'vendors' => DiamondVendor::pluck('vendor_name', 'vendorid'),
            'labs' => DiamondLab::pluck('dl_name', 'dl_id'),
            'shapes' => DiamondShape::pluck('name', 'id'),
            'colors' => DiamondColor::pluck('name', 'id'),
            'clarities' => DiamondClarityMaster::pluck('name', 'id'),
            'cuts' => DiamondCut::pluck('name', 'id'),
            'polish' => DiamondPolish::pluck('name', 'id'),
            'symmetry' => DiamondSymmetry::pluck('name', 'id'),
            'fluorescence' => DiamondFlourescence::pluck('name', 'id'),
            'culet' => DiamondCulet::pluck('dc_name', 'dc_id'),
            'fancyColorIntensity' => DiamondFancyColorIntensity::pluck('fci_id', 'fci_name'),
            'fancycolorOvertones' => DiamondFancyColor::pluck('fco_name', 'fco_id'),
        ]);
    }

    public function dataBackend(Request $request)
    {
        $query = DiamondMaster::with([
            'vendor',
            'shape',
            'color',
            'cut',
            'clarity',
            'certificateCompany',
            'polish',
            'symmetry',
            'fluorescence'
        ]);

        if ($request->filled('price')) {
            $price = $request->get('price');
            $query->whereBetween('price', $price);
        }

        if ($request->has('carat') && is_array($request->carat) && count($request->carat) === 2) {
            $carat = array_map('floatval', $request->carat);
            $query->whereBetween('carat_weight', $carat);
        }

        if ($request->has('cut') && is_array($request->cut) && count($request->cut) === 2) {
            $cut = array_map('intval', $request->cut);
            $query->whereBetween('cut', $cut);
        }

        if ($request->has('color') && is_array($request->color)) {
            $query->whereBetween('color', array_map('intval', $request->color));
        }

        if ($request->has('clarity') && is_array($request->clarity) && count($request->clarity) === 2) {
            $clarity = array_map('intval', $request->clarity);
            $query->whereBetween('clarity', $clarity);
        }

        if ($request->filled('shapes')) {
            $query->whereIn('shape', $request->get('shapes'));
        }

        if ($request->filled('certificate')) {
            $query->where('certificate_number', 'like', '%' . $request->get('certificate') . '%');
        }

        $perPage = $request->get('per_page', 20);
        $diamonds = $query->paginate($perPage);

        return DataTables::of($query)->make(true);
    }

    public function data(Request $request)
    {
        $query = DiamondMaster::with([
            'shape',
            'color',
            'cut',
            'clarity',
            'certificateCompany',
            'polish',
            'symmetry',
            'fluorescence'
        ]);

        if ($request->filled('active_tab')) {
            $query->where('diamond_type', (int) $request->get('active_tab'));
        }

        if ($request->filled('price')) {
            $price = $request->get('price');
            $query->whereBetween('price', $price);
        }

        if ($request->has('carat') && is_array($request->carat) && count($request->carat) === 2) {
            $carat = array_map('floatval', $request->carat);
            $query->whereBetween('carat_weight', $carat);
        }

        if ($request->has('cut') && is_array($request->cut) && count($request->cut) === 2) {
            $cut = array_map('intval', $request->cut);
            $query->whereBetween('cut', $cut);
        }

        if ($request->has('color') && is_array($request->color) && count($request->color) === 2) {
            $min = intval($request->color[0]);
            $max = intval($request->color[1]);

            $query->where('color', '>=', $min)
                ->where('color', '<', $max);
        }

        if ($request->has('clarity') && is_array($request->clarity) && count($request->clarity) === 2) {
            $minClarity = intval($request->clarity[0]);
            $maxClarity = intval($request->clarity[1]);

            $query->where('clarity', '>=', $minClarity)
                ->where('clarity', '<', $maxClarity);
        }

        if ($request->filled('shapes')) {
            $query->whereIn('shape', $request->get('shapes'));
        }

        if ($request->filled('certificate')) {
            $query->where('certificate_number', 'like', '%' . $request->get('certificate') . '%');
        }

        if (
            $request->has('polish') && is_array($request->polish) && count($request->polish) === 2 &&
            is_numeric($request->polish[0]) && is_numeric($request->polish[1])
        ) {
            $start = (int) $request->polish[0];
            $end = (int) $request->polish[1];
            $end = max($end - 1, $start);
            $query->whereBetween('polish', [$start, $end]);
        }

        if (
            $request->has('symmetry') && is_array($request->symmetry) && count($request->symmetry) === 2 &&
            is_numeric($request->symmetry[0]) && is_numeric($request->symmetry[1])
        ) {
            $start = (int) $request->symmetry[0];
            $end = (int) $request->symmetry[1];
            $end = max($end - 1, $start);
            $query->whereBetween('symmetry', [$start, $end]);
        }

        if ($request->has('fluorescence') && is_array($request->fluorescence)) {
            $fluorescence = $request->fluorescence;
            $fluorescence[1] = max($fluorescence[1] - 1, $fluorescence[0]);
            $query->whereBetween('fluorescence', $fluorescence);
        }

        if (
            $request->has('ratio') && is_array($request->ratio) && count($request->ratio) === 2 &&
            is_numeric($request->ratio[0]) && is_numeric($request->ratio[1])
        ) {
            $start = (float) $request->ratio[0];
            $end = (float) $request->ratio[1];

            $query->whereNotNull('measurement_l')
                ->whereNotNull('measurement_w')
                ->where('measurement_w', '>', 0)
                ->whereRaw('(measurement_l / measurement_w) BETWEEN ? AND ?', [$start, $end]);
        }

        if (
            $request->has('table') && is_array($request->table) && count($request->table) === 2 &&
            is_numeric($request->table[0]) && is_numeric($request->table[1])
        ) {
            $start = (int) $request->table[0];
            $end = (int) $request->table[1];
            $query->whereBetween('table_diamond', [$start, $end]);
        }

        if (
            $request->has('depth') && is_array($request->depth) && count($request->depth) === 2 &&
            is_numeric($request->depth[0]) && is_numeric($request->depth[1])
        ) {
            $start = (int) $request->depth[0];
            $end = (int) $request->depth[1];
            $query->whereBetween('depth', [$start, $end]);
        }

        if ($request->boolean('featured')) {
            $query->where('is_superdeal', 1);
        }

        if ($request->boolean('showOnlyChecked')) {
            $checked = $request->checkedDiamonds ?? [];

            if (is_array($checked) && count($checked) > 0) {
                $query->whereIn('diamondid', $checked);
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        $perPage = $request->get('per_page', 20);
        $diamonds = $query->paginate($perPage);

        return response()->json($diamonds);
    }

    public function create()
    {
        return view('admin.DiamondMaster.master.create', [
            'vendors' => DiamondVendor::pluck('vendor_name', 'vendorid'),
            'labs' => DiamondLab::pluck('dl_name', 'dl_id'),
            'shapes' => DiamondShape::pluck('name', 'id'),
            'colors' => DiamondColor::pluck('name', 'id'),
            'clarities' => DiamondClarityMaster::pluck('name', 'id'),
            'cuts' => DiamondCut::pluck('name', 'id'),
            'polish' => DiamondPolish::pluck('name', 'id'),
            'symmetry' => DiamondSymmetry::pluck('name', 'id'),
            'fluorescence' => DiamondFlourescence::pluck('name', 'id'),
            'culet' => DiamondCulet::pluck('dc_name', 'dc_id'),
            'fancyColorIntensity' => DiamondFancyColorIntensity::pluck('fci_name', 'fci_id'),
            'fancycolorOvertones' => DiamondFancyColor::pluck('fco_name', 'fco_id'),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'diamond_type' => 'required|in:1,2',
            'quantity' => 'required|integer|min:1',
            'vendor_id' => 'required|exists:vendor_master,vendorid',
            'vendor_stock_number' => 'nullable|string|max:100',
            'stock_number' => 'nullable|string|max:100',
            'shape' => 'required|exists:diamond_shape_master,id',
            'carat_weight' => 'required|numeric|min:0.01',
            'color' => 'nullable|exists:diamond_color_master,id',
            'clarity' => 'nullable|exists:diamond_clarity_master,id',
            'cut' => 'required|integer',
            'polish' => 'nullable|integer',
            'symmetry' => 'nullable|integer',
            'fluorescence' => 'nullable|integer',
            'fancy_color_intensity' => 'nullable|integer',
            'fancy_color_overtone' => 'nullable|integer',
            'price' => 'nullable|numeric|min:0',
            'msrp_price' => 'nullable|numeric|min:0',
            'price_per_carat' => 'nullable|numeric|min:0',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cert_link' => 'nullable|url',
            'video_file' => 'nullable|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:10240',
            'measurements' => 'nullable|string',
            'depth' => 'nullable|numeric|min:0',
            'vendor_rap_disc' => 'nullable|numeric|min:0',
            'table_diamond' => 'nullable|numeric|min:0',
            'certificate_company' => 'nullable|exists:diamond_lab_master,dl_id',
            'certificate_number' => 'nullable|string|max:100',
            'certificate_date' => 'nullable|date',
            'measurement_l' => 'nullable|numeric|min:0',
            'measurement_h' => 'nullable|numeric|min:0',
            'measurement_w' => 'nullable|numeric|min:0',
            'is_superdeal' => 'nullable|in:0,1',
            'availability' => 'nullable|in:0,1,2',
            'status' => 'nullable|in:0,1',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('image_file')) {
            $imageFile = $request->file('image_file');
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            $imagePath = $imageFile->storeAs('diamonds/images', $imageName, 'public');
            $validatedData['image_link'] = $imageName;
        }

        // Handle video file upload - store only filename
        if ($request->hasFile('video_file')) {
            $videoFile = $request->file('video_file');
            $videoName = time() . '_' . $videoFile->getClientOriginalName();
            $videoPath = $videoFile->storeAs('diamonds/videos', $videoName, 'public');

            $validatedData['video_link'] = $videoName;
        }

        $dimensions = [];

        if (!empty($validatedData['measurement_l'])) {
            $dimensions[] = $validatedData['measurement_l'];
        }
        if (!empty($validatedData['measurement_w'])) {
            $dimensions[] = $validatedData['measurement_w'];
        }
        if (!empty($validatedData['measurement_h'])) {
            $dimensions[] = $validatedData['measurement_h'];
        }

        if (count($dimensions) > 0) {
            $validatedData['measurements'] = implode(' x ', $dimensions);
        }

        $validatedData['date_added'] = now();
        $validatedData['added_by'] = auth()->id();
        DiamondMaster::create($validatedData);

        return response()->json(['message' => 'Diamond added successfully.']);
    }

    public function edit($id)
    {
        $diamond = DiamondMaster::findOrFail($id);
        return view('admin.DiamondMaster.master.edit', array_merge(
            ['diamond' => $diamond],
            [
                'vendors' => DiamondVendor::pluck('vendor_name', 'vendorid'),
                'labs' => DiamondLab::pluck('dl_name', 'dl_id'),
                'shapes' => DiamondShape::pluck('name', 'id'),
                'colors' => DiamondColor::pluck('name', 'id'),
                'clarities' => DiamondClarityMaster::pluck('name', 'id'),
                'cuts' => DiamondCut::pluck('name', 'id'),
                'polish' => DiamondPolish::pluck('name', 'id'),
                'symmetry' => DiamondSymmetry::pluck('name', 'id'),
                'fluorescence' => DiamondFlourescence::pluck('name', 'id'),
                'culet' => DiamondCulet::pluck('dc_name', 'dc_id'),
                'fancyColorIntensity' => DiamondFancyColorIntensity::pluck('fci_name', 'fci_id'),
                'fancycolorOvertones' => DiamondFancyColor::pluck('fco_name', 'fco_id'),
            ]
        ));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'diamond_type' => 'required|in:1,2',
            'quantity' => 'required|integer|min:1',
            'vendor_id' => 'required|exists:vendor_master,vendorid',
            'vendor_stock_number' => 'nullable|string|max:100',
            'stock_number' => 'nullable|string|max:100',
            'shape' => 'required|exists:diamond_shape_master,id',
            'carat_weight' => 'required|numeric|min:0.01',
            'color' => 'nullable|exists:diamond_color_master,id',
            'clarity' => 'nullable|exists:diamond_clarity_master,id',
            'culet' => 'nullable|exists:diamond_culet_master,dc_id',
            'cut' => 'required|integer',
            'polish' => 'nullable|integer',
            'symmetry' => 'nullable|integer',
            'fluorescence' => 'nullable|integer',
            'fancy_color_intensity' => 'nullable|integer',
            'fancy_color_overtone' => 'nullable|integer',
            'price' => 'nullable|numeric|min:0',
            'msrp_price' => 'nullable|numeric|min:0',
            'price_per_carat' => 'nullable|numeric|min:0',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cert_link' => 'nullable|url',
            'video_file' => 'nullable|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:10240',
            'measurements' => 'nullable|string',
            'depth' => 'nullable|numeric|min:0',
            'vendor_rap_disc' => 'nullable|numeric|min:0',
            'table_diamond' => 'nullable|numeric|min:0',
            'certificate_company' => 'nullable|exists:diamond_lab_master,dl_id',
            'certificate_number' => 'nullable|string|max:100',
            'certificate_date' => 'nullable|date',
            'measurement_l' => 'nullable|numeric|min:0',
            'measurement_h' => 'nullable|numeric|min:0',
            'measurement_w' => 'nullable|numeric|min:0',
            'is_superdeal' => 'nullable|in:0,1',
            'availability' => 'nullable|in:0,1,2',
            'status' => 'nullable|in:0,1',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $diamond = DiamondMaster::findOrFail($id);
        $validatedData = $validator->validated();

        // Handle image file upload - store only filename
        if ($request->hasFile('image_file')) {
            // Delete old image if exists
            if ($diamond->image_link) {
                Storage::disk('public')->delete('diamonds/images/' . $diamond->image_link);
            }

            $imageFile = $request->file('image_file');
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            $imagePath = $imageFile->storeAs('diamonds/images', $imageName, 'public');

            $validatedData['image_link'] = $imageName;
        }

        // Handle video file upload - store only filename
        if ($request->hasFile('video_file')) {
            // Delete old video if exists
            if ($diamond->video_link) {
                Storage::disk('public')->delete('diamonds/videos/' . $diamond->video_link);
            }

            $videoFile = $request->file('video_file');
            $videoName = time() . '_' . $videoFile->getClientOriginalName();
            $videoPath = $videoFile->storeAs('diamonds/videos', $videoName, 'public');

            $validatedData['video_link'] = $videoName;
        }

        $dimensions = [];

        if (!empty($validatedData['measurement_l'])) {
            $dimensions[] = $validatedData['measurement_l'];
        }
        if (!empty($validatedData['measurement_w'])) {
            $dimensions[] = $validatedData['measurement_w'];
        }
        if (!empty($validatedData['measurement_h'])) {
            $dimensions[] = $validatedData['measurement_h'];
        }

        if (count($dimensions) > 0) {
            $validatedData['measurements'] = implode(' x ', $dimensions);
        }

        $validatedData['date_updated'] = now();
        $validatedData['updated_by'] = auth()->id();
        $diamond->update($validatedData);

        return response()->json(['success' => true, 'message' => 'Record updated successfully.']);
    }

    public function destroy($id)
    {
        $diamond = DiamondMaster::findOrFail($id);

        // Delete associated files
        if ($diamond->image_link) {
            Storage::disk('public')->delete('diamonds/images/' . $diamond->image_link);
        }

        if ($diamond->video_link) {
            Storage::disk('public')->delete('diamonds/videos/' . $diamond->video_link);
        }

        $diamond->delete();

        return redirect()->route('diamond-master.index')
            ->with('success', 'Diamond deleted successfully.');
    }

    /**
     * Export diamonds to Excel
     */
    public function export()
    {
        try {
            Log::info('Starting diamonds export');

            // Check if there are diamonds to export
            $diamondCount = DiamondMaster::count();
            Log::info("Total diamonds in database: {$diamondCount}");

            if ($diamondCount === 0) {
                return redirect()->route('diamond-master.index')
                    ->with('warning', 'No diamonds found to export.');
            }

            $filename = 'diamonds_' . date('Y-m-d_H-i-s') . '.xlsx';
            Log::info("Exporting to file: {$filename}");

            return Excel::download(new DiamondMasterExport(), $filename);
        } catch (\Exception $e) {
            Log::error('Diamonds export failed: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            // Check for specific common errors
            if (strpos($e->getMessage(), 'Allowed memory size') !== false) {
                return redirect()->route('diamond-master.index')
                    ->with('error', 'Export failed: Too much data to export. Please try exporting fewer records.');
            }

            return redirect()->route('diamond-master.index')
                ->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import diamonds from Excel
     */
    /**
     * Import diamonds from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            Log::info('Starting diamonds import');

            // Debug: Read file headers
            $file = $request->file('import_file');
            $reader = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);

            if (empty($reader[0])) {
                return redirect()->route('diamond-master.index')
                    ->with('error', 'The uploaded file is empty.');
            }

            Log::info('File headers found: ' . implode(', ', array_keys($reader[0][0])));

            // Use the new simple import
            $import = new \App\Imports\DiamondMasterSimpleImport();
            Excel::import($import, $file);

            $importedCount = $import->getImportedCount();
            $updatedCount = $import->getUpdatedCount();
            $errors = $import->getErrors();

            Log::info("Import results - Imported: {$importedCount}, Updated: {$updatedCount}, Errors: " . count($errors));

            if ($importedCount == 0 && $updatedCount == 0 && empty($errors)) {
                return redirect()->route('diamond-master.index')
                    ->with('error', 'No diamonds were imported. Please check if your file has valid data.');
            }

            $message = "✅ Import completed!\n";
            $message .= "📦 New diamonds imported: " . $importedCount . "\n";
            $message .= "🔄 Existing diamonds updated: " . $updatedCount . "\n";

            if (!empty($errors)) {
                $message .= "\n⚠️ " . count($errors) . " rows had errors:\n";

                $errorDetails = [];
                foreach ($errors as $error) {
                    $errorDetails[] = [
                        'row' => $error['row'],
                        'errors' => $error['errors'],
                        'values' => $error['data'] ?? []
                    ];

                    $message .= "📝 Row: " . $error['row'] . ", ";
                    $message .= "❌ Errors: " . implode(', ', $error['errors']) . "\n";
                }

                session()->flash('import_errors', $errorDetails);

                return redirect()->route('diamond-master.index')
                    ->with('warning', $message);
            }

            return redirect()->route('diamond-master.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Diamonds import failed: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return redirect()->route('diamond-master.index')
                ->with('error', 'Error importing diamonds: ' . $e->getMessage());
        }
    }

    /**
     * Download sample Excel file for import
     */

    public function downloadSample()
    {
        // Create sample data with IDs instead of names for simplicity
        $sampleData = [
            [
                'Diamond Type' => '2',
                'Quantity' => '1',
                'Vendor Name' => 'Mira Gems',
                'Vendor Stock Number' => 'VEND-001',
                'Stock Number' => 'STOCK-001',
                'Shape' => 'Round',
                'Carat Weight' => '1.00',
                'Color' => 'D',
                'Clarity' => 'IF',
                'Cut' => 'Excellent',
                'Polish' => 'Excellent',
                'Symmetry' => 'Excellent',
                'Fluorescence' => 'None',
                'Price' => '5000.00',
                'MSRP Price' => '5500.00',
                'Price Per Carat' => '5000.00',
                'Certificate Company' => 'GIA',
                'Certificate Number' => 'GIA12345678',
                'Certificate Date' => '2024-01-15',
                'Measurements' => '6.5 x 6.5 x 4.0',
                'Measurement L' => '6.5',
                'Measurement W' => '6.5',
                'Measurement H' => '4.0',
                'Depth' => '61.5',
                'Table' => '57.0',
                'Vendor RAP Disc' => '0.0',
                'Is Superdeal' => 'No',
                'Availability' => 'Available',
                'Status' => 'Active',
            ]
        ];

        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Diamonds');

        // Add headers
        $headers = array_keys($sampleData[0]);
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
        }

        // Add sample data
        foreach ($sampleData as $row => $data) {
            $col = 0;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, $row + 2, $data[$header] ?? '');
                $col++;
            }
        }

        // Auto-size columns
        for ($col = 0; $col < count($headers); $col++) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'diamonds_import_sample_' . date('Y-m-d') . '.xlsx';
        $filePath = storage_path('app/public/' . $filename);
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    public function importErrors()
    {
        $errors = session()->get('import_errors', []);
        return response()->json([
            'count' => count($errors),
            'errors' => $errors
        ]);
    }
    public function showImport()
    {
        return view('admin.DiamondMaster.import');
    }
}

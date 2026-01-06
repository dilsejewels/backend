<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Models\DiamondSize;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiamondSizeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sizes = DiamondSize::orderBy('id', 'desc')->get();
            return response()->json($sizes);
        }
        return view('admin.DiamondMaster.Size.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'size1' => 'nullable|numeric',
            'size2' => 'nullable|numeric',
            'retailer_id' => 'nullable|integer',
            'status' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);
        if (!empty($data['size1']) && !empty($data['size2'])) {
            $data['title'] = number_format($data['size1'], 2) . '-' . number_format($data['size2'], 2);
        }
    
        $data['date_added'] = now();
        $data['added_by'] = auth()->id();

        DiamondSize::create($data);

        return redirect()->route('sizes.index')
            ->with('success', 'Record added successfully.');
    }

    public function update(Request $request, $id)
    {
        $size = DiamondSize::findOrFail($id);

        $data = $request->validate([
            'size1' => 'nullable|numeric',
            'size2' => 'nullable|numeric',
            'retailer_id' => 'nullable|integer',
            'status' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);
        if (!empty($data['size1']) && !empty($data['size2'])) {
            $data['title'] = number_format($data['size1'], 2) . '-' . number_format($data['size2'], 2);
        }
        $data['date_updated'] = now();
        $data['updated_by'] = auth()->id();

        $size->update($data);

        return redirect()->route('sizes.index')
            ->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        $size = DiamondSize::findOrFail($id);
        $size->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }
        return redirect()->route('sizes.index')
        ->with('success', 'Record deleted successfully.');
    }

    public function show(DiamondSize $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.DiamondMaster.Size.index', compact('id'));
    }
}

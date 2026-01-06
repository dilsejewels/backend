<?php

namespace App\Http\Controllers\DiamondMaster;

use Illuminate\Http\Request;
use App\Models\DiamondFancyColor;
use App\Http\Controllers\Controller;

class DiamondFancyColorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $fancyColor = DiamondFancyColor::all();
            return response()->json($fancyColor);
        }
        return view('admin.DiamondMaster.fancyColor.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fco_name' => 'required|string|max:250',
            'fco_alise' => 'nullable|string',
            'fco_short_name' => 'nullable|string|max:250',
            'fco_remark' => 'nullable|string',
            'fco_display_in_front' => 'nullable|integer',
            'fco_sort_order' => 'nullable|integer',
        ]);
        $data['date_added'] = now();
        DiamondFancyColor::create($data);
    
        return response()->json(['message' => 'Record added successfully.'], 200);
    }
    
  public function update(Request $request, $id)
{
    $fancyColor = DiamondFancyColor::findOrFail($id);

    $rules = [];

    if ($request->has('fco_name')) {
        $rules['fco_name'] = 'required|string|max:250';
    }

    if ($request->has('fco_alise')) {
        $rules['fco_alise'] = 'nullable|string';
    }

    if ($request->has('fco_short_name')) {
        $rules['fco_short_name'] = 'nullable|string|max:250';
    }

    if ($request->has('fco_remark')) {
        $rules['fco_remark'] = 'nullable|string';
    }

    if ($request->has('fco_display_in_front')) {
        $rules['fco_display_in_front'] = 'nullable|integer';
    }

    if ($request->has('fco_sort_order')) {
        $rules['fco_sort_order'] = 'nullable|integer';
    }

    $data = $request->validate($rules);

    $data['date_modify'] = now();

    $fancyColor->update($data);

    return response()->json(['message' => 'Record updated successfully.'], 200);
}

    public function destroy($id)
    {
        $fancyColor = DiamondFancyColor::findOrFail($id);
        $fancyColor->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }

        return redirect()->route('fancyColor.index')
        ->with('success', 'Record deleted successfully.');
    }
    public function show(DiamondFancyColor $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.DiamondMaster.fancyColor.index', compact('id'));
    }
}

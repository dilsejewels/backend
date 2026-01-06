<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Models\DiamondCulet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiamondCuletController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $culet = DiamondCulet::all();
            return response()->json($culet);
        }
        return view('admin.DiamondMaster.Culet.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dc_name' => 'required|string|max:250',
            'dc_short_name' => 'nullable|string|max:250',
            'dc_alise' => 'nullable|string',
            'dc_remark' => 'nullable|string',
            'dc_display_in_front' => 'nullable|integer',
            'dc_sort_order' => 'nullable|integer',
        ]);
        $data['date_added'] = now();
        DiamondCulet::create($data);

        return response()->json(['message' => 'Record added successfully.'], 200);
    }

    public function update(Request $request, $id)
    {
        $culet = DiamondCulet::findOrFail($id);

        $rules = [];

        if ($request->has('dc_name')) {
            $rules['dc_name'] = 'required|string|max:250';
        }

        if ($request->has('dc_short_name')) {
            $rules['dc_short_name'] = 'nullable|string|max:250';
        }

        if ($request->has('dc_alise')) {
            $rules['dc_alise'] = 'nullable|string';
        }

        if ($request->has('dc_remark')) {
            $rules['dc_remark'] = 'nullable|string';
        }

        if ($request->has('dc_display_in_front')) {
            $rules['dc_display_in_front'] = 'nullable|integer';
        }

        if ($request->has('dc_sort_order')) {
            $rules['dc_sort_order'] = 'nullable|integer';
        }

        $data = $request->validate($rules);

        $data['date_modify'] = now();

        $culet->update($data);

        return response()->json(['message' => 'Record updated successfully.'], 200);
    }

    public function destroy($id)
    {
        $culet = DiamondCulet::findOrFail($id);
        $culet->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }

        return redirect()->route('culet.index')
            ->with('success', 'Record deleted successfully.');
    }
    public function show(DiamondCulet $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.DiamondMaster.culet.index', compact('id'));
    }
}

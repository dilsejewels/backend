<?php

namespace App\Http\Controllers\DiamondMaster;

use Illuminate\Http\Request;
use App\Models\DiamondGirdle;
use App\Http\Controllers\Controller;

class DiamondGirdleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $girdle = DiamondGirdle::all();
            return response()->json($girdle);
        }
        return view('admin.DiamondMaster.Girdle.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dg_name' => 'required|string|max:250',
            'dg_alise' => 'nullable|string',
            'dg_short_name' => 'nullable|string|max:250',
            'dg_remark' => 'nullable|string',
            'dg_display_in_front' => 'nullable|integer',
            'dg_sort_order' => 'nullable|integer',
        ]);
        $data['date_added'] = now();
        DiamondGirdle::create($data);

        return response()->json(['message' => 'Record added successfully.'], 200);
    }

    public function update(Request $request, $id)
    {
        $girdle = DiamondGirdle::findOrFail($id);

        $rules = [];

        // Only require 'dg_name' if it's included in the request
        if ($request->has('dg_name')) {
            $rules['dg_name'] = 'required|string|max:250';
        }

        if ($request->has('dg_alise')) {
            $rules['dg_alise'] = 'nullable|string';
        }

        if ($request->has('dg_short_name')) {
            $rules['dg_short_name'] = 'nullable|string|max:250';
        }

        if ($request->has('dg_remark')) {
            $rules['dg_remark'] = 'nullable|string';
        }

        if ($request->has('dg_display_in_front')) {
            $rules['dg_display_in_front'] = 'nullable|integer';
        }

        if ($request->has('dg_sort_order')) {
            $rules['dg_sort_order'] = 'nullable|integer';
        }

        // Validate request based on the conditional rules
        $data = $request->validate($rules);

        $data['date_modify'] = now();

        $girdle->update($data);

        return response()->json(['message' => 'Record updated successfully.'], 200);
    }

    public function destroy($id)
    {
        $girdle = DiamondGirdle::findOrFail($id);
        $girdle->delete();
        // if (request()->ajax()) {
        //     return response()->json(['message' => 'Record deleted successfully.'], 200);
        // }
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }


        return redirect()->route('girdle.index')
            ->with('success', 'Record deleted successfully.');
    }
    public function show(DiamondGirdle $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.DiamondMaster.Girdle.index', compact('id'));
    }
}

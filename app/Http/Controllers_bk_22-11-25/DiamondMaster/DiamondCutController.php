<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Models\DiamondCut;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiamondCutController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cut = DiamondCut::all();
            return response()->json($cut);
        }
        return view('admin.DiamondMaster.Cut.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:250',
            'shortname' => 'nullable|string|max:250',
            'full_name' => 'nullable|string|max:250',
            'ALIAS' => 'nullable|string',
            'remark' => 'nullable|string',
            'display_in_front' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);
        $data['date_added'] = now();
        DiamondCut::create($data);

        return response()->json(['message' => 'Record added successfully.'], 200);
    }

    public function update(Request $request, $id)
    {
        $cut = DiamondCut::findOrFail($id);

        $rules = [];

        if ($request->has('name')) {
            $rules['name'] = 'required|string|max:250';
        }

        if ($request->has('shortname')) {
            $rules['shortname'] = 'nullable|string|max:250';
        }

        if ($request->has('full_name')) {
            $rules['full_name'] = 'nullable|string|max:250';
        }

        if ($request->has('ALIAS')) {
            $rules['ALIAS'] = 'nullable|string';
        }

        if ($request->has('remark')) {
            $rules['remark'] = 'nullable|string';
        }

        if ($request->has('display_in_front')) {
            $rules['display_in_front'] = 'nullable|integer';
        }

        if ($request->has('sort_order')) {
            $rules['sort_order'] = 'nullable|integer';
        }

        $data = $request->validate($rules);

        $data['date_modify'] = now();

        $cut->update($data);

        return response()->json(['message' => 'Record updated successfully.'], 200);
    }

    public function destroy($id)
    {
        $cut = DiamondCut::findOrFail($id);
        $cut->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }

        return redirect()->route('cut.index')
            ->with('success', 'Record deleted successfully.');
    }
    public function show(DiamondCut $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.DiamondMaster.Cut.index', compact('id'));
    }
}

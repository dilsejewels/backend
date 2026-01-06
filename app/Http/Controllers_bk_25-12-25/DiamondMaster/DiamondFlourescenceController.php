<?php

namespace App\Http\Controllers\DiamondMaster;

use Illuminate\Http\Request;
use App\Models\DiamondFlourescence;
use App\Http\Controllers\Controller;

class DiamondFlourescenceController extends Controller
{
    
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $flourescence = DiamondFlourescence::all();
            return response()->json($flourescence);
        }
        return view('admin.DiamondMaster.Flourescence.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:250',
            'alias' => 'nullable|string|max:250',
            'short_name' => 'nullable|string|max:150',
            'full_name' => 'nullable|string|max:250',
            'fluo_status' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);
        $data['date_added'] = now();
    
        DiamondFlourescence::create($data);
    
        return response()->json(['message' => 'Record added successfully.'], 200);
    }
    

    public function update(Request $request, $id)
{
    $flourescence = DiamondFlourescence::findOrFail($id);

    $rules = [];

    if ($request->has('name')) {
        $rules['name'] = 'required|string|max:250';
    }

    if ($request->has('alias')) {
        $rules['alias'] = 'nullable|string|max:250';
    }

    if ($request->has('short_name')) {
        $rules['short_name'] = 'nullable|string|max:150';
    }

    if ($request->has('full_name')) {
        $rules['full_name'] = 'nullable|string|max:250';
    }

    if ($request->has('fluo_status')) {
        $rules['fluo_status'] = 'nullable|integer';
    }

    if ($request->has('sort_order')) {
        $rules['sort_order'] = 'nullable|integer';
    }

    $data = $request->validate($rules);

    $data['date_modify'] = now();

    $flourescence->update($data);

    return response()->json(['message' => 'Record updated successfully.'], 200);
}


    public function destroy($id)
    {
        $flourescence = DiamondFlourescence::findOrFail($id);
        $flourescence->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }

        return redirect()->route('flourescence.index')
        ->with('success', 'Record deleted successfully.');
    }

    public function show(DiamondFlourescence $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.DiamondMaster.Flourescence.index', compact('id'));
    }
}

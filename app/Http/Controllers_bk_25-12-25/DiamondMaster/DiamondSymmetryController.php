<?php

namespace App\Http\Controllers\DiamondMaster;

use Illuminate\Http\Request;
use App\Models\DiamondSymmetry;
use App\Http\Controllers\Controller;

class DiamondSymmetryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $symmetry = DiamondSymmetry::all();
            return response()->json($symmetry);
        }
        return view('admin.DiamondMaster.Symmetry.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());
        $data['date_added'] = now();
        DiamondSymmetry::create($data);
    
        return response()->json(['message' => 'Record added successfully.'], 200);
    }
    

    public function update(Request $request, $id)
    {
        $symmetry = DiamondSymmetry::findOrFail($id);
    
        $data = $request->validate($this->validationRules());
        $data['date_modify'] = now();
        $symmetry->update($data);
    
        return response()->json(['message' => 'Record updated successfully.'], 200);
    }
    

    public function destroy($id)
    {
        $symmetry = DiamondSymmetry::findOrFail($id);
        $symmetry->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }


        return redirect()->route('symmetry.index')
        ->with('success', 'Record deleted successfully.');
    }

    public function show(DiamondSymmetry $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.DiamondMaster.Symmetry.index', compact('id'));
    }
    private function validationRules()
    {
        return [
            'name' => 'nullable|string|max:250',
            'alias' => 'nullable|string|max:250',
            'short_name' => 'nullable|string|max:150',
            'full_name' => 'nullable|string|max:250',
            'sym_ststus' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ];
    }
}

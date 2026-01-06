<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Http\Controllers\Controller;
use App\Models\DiamondWeightGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiamondWeightGroupController extends Controller
{
    public function index()
    {
        return view('admin.DiamondMaster.WeightGroup.index');
    }

    public function getData()
    {
        $weights = DiamondWeightGroup::orderBy('dwg_sort_order')->get();
        
        return response()->json([
            'data' => $weights
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dwg_name' => 'required|string|max:250',
            'dwg_from' => 'required|numeric',
            'dwg_to' => 'required|numeric',
            'dwg_status' => 'required|in:0,1',
            'dwg_sort_order' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        DiamondWeightGroup::create($request->all());

        return response()->json([
            'success' => 'Weight group added successfully'
        ]);
    }

    public function edit($id)
    {
        $weight = DiamondWeightGroup::findOrFail($id);
        return response()->json($weight);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'dwg_name' => 'required|string|max:250',
            'dwg_from' => 'required|numeric',
            'dwg_to' => 'required|numeric',
            'dwg_status' => 'required|in:0,1',
            'dwg_sort_order' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $weight = DiamondWeightGroup::findOrFail($id);
        $weight->update($request->all());

        return response()->json([
            'success' => 'Weight group updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $weight = DiamondWeightGroup::findOrFail($id);
        $weight->delete();

        return response()->json([
            'success' => 'Weight group deleted successfully'
        ]);
    }
}
<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductClarityMaster;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class ProductClarityMasterController extends Controller
{
    public function index()
    {
        return view('admin.Jewellery.ProductClarityMaster.index');
    }

    public function fetchData(Request $request)
    {
        if ($request->ajax()) {
            $data = ProductClarityMaster::select(['id', 'name', 'alias', 'remark', 'display_in_front', 'sort_order']);
            return DataTables::of($data)
                ->addColumn('display_in_front', function ($row) {
                    return $row->display_in_front ? 1 : 0;
                })
                ->make(true);
        }
        return abort(403, 'Unauthorized access');
    }

    public function store(Request $request)
    {
        $rules = [
            'name'             => 'required|string|max:255',
            'alias'            => 'nullable|string|max:255',
            'remark'           => 'nullable|string|max:255',
            'display_in_front' => 'nullable|in:0,1',
            'sort_order'       => 'nullable|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = new ProductClarityMaster();
        $record->name = $request->name;
        $record->alias = $request->alias;
        $record->remark = $request->remark;
        $record->display_in_front = $request->display_in_front ?: 0;
        $record->sort_order = $request->sort_order;
        $record->date_added = Carbon::now();
        $record->date_modify = Carbon::now();
        $record->save();

        return response()->json(['success' => 'Record created successfully.']);
    }

    public function edit($id)
    {
        $record = ProductClarityMaster::find($id);
        if (!$record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        // ✅ return the record directly (not inside data)
        return response()->json($record);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name'             => 'required|string|max:255',
            'alias'            => 'nullable|string|max:255',
            'remark'           => 'nullable|string|max:255',
            'display_in_front' => 'nullable|in:0,1',
            'sort_order'       => 'nullable|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = ProductClarityMaster::find($id);
        if (!$record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $record->name = $request->name;
        $record->alias = $request->alias;
        $record->remark = $request->remark;
        $record->display_in_front = $request->display_in_front ?: 0;
        $record->sort_order = $request->sort_order;
        $record->date_modify = Carbon::now();
        $record->save();

        return response()->json(['success' => 'Record updated successfully.']);
    }

    public function destroy($id)
    {
        $record = ProductClarityMaster::find($id);
        if (!$record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }
        $record->delete();
        return response()->json(['success' => 'Record deleted successfully.']);
    }
}

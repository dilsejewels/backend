<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductsCutMaster;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ProductCutMasterController extends Controller
{
    /**
     * Show the index page with DataTable
     */
    public function index()
    {
        return view('admin.Jewellery.ProductCutMaster.index');
    }

    /**
     * Fetch data for DataTable (AJAX)
     */
    public function fetch(Request $request)
    {
        $cuts = ProductsCutMaster::orderBy('id', 'desc');
        return DataTables::of($cuts)
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-primary editBtn" data-id="'.$row->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->id.'">Delete</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created cut (AJAX)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|max:255',
            'alias'            => 'nullable|max:255',
            'shortname'        => 'nullable|max:10',
            'remark'           => 'nullable|max:255',
            'display_in_front' => 'nullable|in:0,1',
            'sort_order'       => 'nullable|integer',
        ], [
            'name.required'             => 'Name is required.',
            'name.max'                  => 'Name may not exceed 255 characters.',
            'alias.max'                 => 'Alias may not exceed 255 characters.',
            'shortname.max'             => 'Shortname may not exceed 10 characters.',
            'remark.max'                => 'Remark may not exceed 255 characters.',
            'display_in_front.in'       => 'Display In Front must be 0 (No) or 1 (Yes).',
            'sort_order.integer'        => 'Sort Order must be an integer.',
        ]);

        ProductsCutMaster::create([
            'name'             => $request->name,
            'alias'            => $request->alias,
            'shortname'        => $request->shortname,
            'remark'           => $request->remark,
            'display_in_front' => $request->display_in_front ?? 0,
            'sort_order'       => $request->sort_order,
            'date_added'       => Carbon::now(),
        ]);

        return response()->json(['success' => 'Cut created successfully.']);
    }

    /**
     * Get a single cut for editing (AJAX)
     */
    public function edit($id)
    {
        $cut = ProductsCutMaster::findOrFail($id);
        return response()->json($cut);
    }

    /**
     * Update an existing cut (AJAX)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'             => 'required|max:255',
            'alias'            => 'nullable|max:255',
            'shortname'        => 'nullable|max:10',
            'remark'           => 'nullable|max:255',
            'display_in_front' => 'nullable|in:0,1',
            'sort_order'       => 'nullable|integer',
        ], [
            'name.required'             => 'Name is required.',
            'name.max'                  => 'Name may not exceed 255 characters.',
            'alias.max'                 => 'Alias may not exceed 255 characters.',
            'shortname.max'             => 'Shortname may not exceed 10 characters.',
            'remark.max'                => 'Remark may not exceed 255 characters.',
            'display_in_front.in'       => 'Display In Front must be 0 (No) or 1 (Yes).',
            'sort_order.integer'        => 'Sort Order must be an integer.',
        ]);

        $cut = ProductsCutMaster::findOrFail($id);
        $cut->update([
            'name'             => $request->name,
            'alias'            => $request->alias,
            'shortname'        => $request->shortname,
            'remark'           => $request->remark,
            'display_in_front' => $request->display_in_front ?? 0,
            'sort_order'       => $request->sort_order,
            'date_modify'      => Carbon::now(),
        ]);

        return response()->json(['success' => 'Cut updated successfully.']);
    }

    /**
     * Delete a cut (AJAX)
     */
    public function destroy($id)
    {
        ProductsCutMaster::findOrFail($id)->delete();
        return response()->json(['success' => 'Cut deleted successfully.']);
    }
}

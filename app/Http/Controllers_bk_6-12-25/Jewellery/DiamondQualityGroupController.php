<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiamondQualityGroup;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DiamondQualityGroupController extends Controller
{
    public function index()
    {
        return view('admin.Jewellery.QualityGroup.index');
    }

    public function fetch()
{
    $data = DiamondQualityGroup::orderBy('dqg_id', 'desc')->get();

    return response()->json([
        'data' => $data 
    ]);
}

    public function store(Request $request)
    {
        $rules = [
            'dqg_name'        => 'required|max:250',
            'dqg_alias'       => 'nullable',
            'dqg_short_name'  => 'nullable|max:250',
            'description'     => 'nullable',
            'dqg_icon'        => 'nullable',
            'dqg_sort_order'  => 'nullable|integer',
            'dqg_status'      => 'nullable|integer|in:0,1',
            'dqg_origin'      => 'nullable',
        ];
        $messages = [
            'dqg_name.required'       => 'Name is required.',
            'dqg_name.max'            => 'Name may not be greater than 250 characters.',
            'dqg_short_name.max'      => 'Short Name may not be greater than 250 characters.',
            'dqg_sort_order.integer'  => 'Sort Order must be an integer.',
            'dqg_status.in'           => 'Status must be 0 (Inactive) or 1 (Active).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $dqg = new DiamondQualityGroup();
        $dqg->dqg_name       = $request->dqg_name;
        $dqg->dqg_alias      = $request->dqg_alias;
        $dqg->dqg_short_name = $request->dqg_short_name;
        $dqg->description    = $request->description;
        $dqg->dqg_icon       = $request->dqg_icon;
        $dqg->dqg_sort_order = $request->dqg_sort_order;
        $dqg->dqg_status     = $request->dqg_status;
        $dqg->dqg_origin     = $request->dqg_origin;
        $dqg->added_by       = auth()->id() ?? null;
        $dqg->date_added     = Carbon::now();
        $dqg->save();

        return response()->json([
            'success' => 'Diamond Quality Group added successfully.'
        ]);
    }

public function edit($id)
{
    $dqg = DiamondQualityGroup::find($id);
    if (!$dqg) {
        return response()->json([
            'error' => 'Diamond Quality Group not found.'
        ], 404);
    }
    return response()->json(['data' => $dqg]);
}
    public function update(Request $request, $id)
    {
        $rules = [
            'dqg_name'        => 'required|max:250',
            'dqg_alias'       => 'nullable',
            'dqg_short_name'  => 'nullable|max:250',
            'description'     => 'nullable',
            'dqg_icon'        => 'nullable',
            'dqg_sort_order'  => 'nullable|integer',
            'dqg_status'      => 'nullable|integer|in:0,1',
            'dqg_origin'      => 'nullable',
        ];
        $messages = [
            'dqg_name.required'       => 'Name is required.',
            'dqg_name.max'            => 'Name may not be greater than 250 characters.',
            'dqg_short_name.max'      => 'Short Name may not be greater than 250 characters.',
            'dqg_sort_order.integer'  => 'Sort Order must be an integer.',
            'dqg_status.in'           => 'Status must be 0 (Inactive) or 1 (Active).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $dqg = DiamondQualityGroup::find($id);
        if (!$dqg) {
            return response()->json([
                'error' => 'Diamond Quality Group not found.'
            ], 404);
        }

        $dqg->dqg_name       = $request->dqg_name;
        $dqg->dqg_alias      = $request->dqg_alias;
        $dqg->dqg_short_name = $request->dqg_short_name;
        $dqg->description    = $request->description;
        $dqg->dqg_icon       = $request->dqg_icon;
        $dqg->dqg_sort_order = $request->dqg_sort_order;
        $dqg->dqg_status     = $request->dqg_status;
        $dqg->dqg_origin     = $request->dqg_origin;
        $dqg->updated_by     = auth()->id() ?? null;
        $dqg->date_modified  = Carbon::now();
        $dqg->save();

        return response()->json([
            'success' => 'Diamond Quality Group updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $dqg = DiamondQualityGroup::find($id);
        if (!$dqg) {
            return response()->json([
                'error' => 'Diamond Quality Group not found.'
            ], 404);
        }
        $dqg->delete();

        return response()->json([
            'success' => 'Diamond Quality Group deleted successfully.'
        ]);
    }
}

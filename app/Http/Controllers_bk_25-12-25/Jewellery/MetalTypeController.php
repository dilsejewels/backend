<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MetalType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MetalTypeController extends Controller
{
    /**
     * Show the Metal Type management page.
     */
    public function index()
    {
        return view('admin.Jewellery.MetalType.index');
    }

    /**
     * Fetch all MetalType records as JSON for DataTable.
     */
    public function fetch()
{
    $metals = MetalType::select('dmt_id', 'dmt_name', 'dmt_tooltip', 'dmt_status', 'sort_order', 'color_code', 'metal_icon')->get();
    return response()->json(['data' => $metals]);
}


    /**
     * Store a newly created MetalType (AJAX).
     */
    public function store(Request $request)
    {
        $request->validate([
            'dmt_name'     => 'required|string|max:250',
            'dmt_tooltip'  => 'nullable|string|max:2',
            'dmt_status'   => 'required|in:0,1',
            'sort_order'   => 'nullable|integer',
            'color_code'   => 'nullable|string|max:50',
            'metal_icon'   => 'nullable|string|max:255',
        ], [
            'dmt_name.required'   => 'Name is required.',
            'dmt_name.max'        => 'Name may not exceed 250 characters.',
            'dmt_tooltip.max'     => 'Tooltip may not exceed 2 characters.',
            'dmt_status.in'       => 'Status must be 0 (Inactive) or 1 (Active).',
            'sort_order.integer'  => 'Sort Order must be an integer.',
            'color_code.max'      => 'Color Code may not exceed 50 characters.',
            'metal_icon.max'      => 'Icon may not exceed 255 characters.',
        ]);

        $metal = new MetalType($request->only([
            'dmt_name', 'dmt_tooltip', 'dmt_status', 'sort_order', 'color_code', 'metal_icon'
        ]));
        $metal->added_by    = Auth::id();
        $metal->date_added  = Carbon::now();
        $metal->save();

        return response()->json(['success' => 'Metal type added successfully.']);
    }

    /**
     * Get a single MetalType record (AJAX).
     */
    public function show($id)
    {
        return response()->json(
            MetalType::findOrFail($id)
        );
    }


    public function update(Request $request, $id)
    {
        $metal = MetalType::findOrFail($id);

        if ($request->has('dmt_name')) {
            $request->validate([
                'dmt_name'     => 'required|string|max:250',
                'dmt_tooltip'  => 'nullable|string|max:2',
                'dmt_status'   => 'required|in:0,1',
                'sort_order'   => 'nullable|integer',
                'color_code'   => 'nullable|string|max:50',
                'metal_icon'   => 'nullable|string|max:255',
            ], [
                'dmt_name.required'   => 'Name is required.',
                'dmt_name.max'        => 'Name may not exceed 250 characters.',
                'dmt_tooltip.max'     => 'Tooltip may not exceed 2 characters.',
                'dmt_status.in'       => 'Status must be 0 (Inactive) or 1 (Active).',
                'sort_order.integer'  => 'Sort Order must be an integer.',
                'color_code.max'      => 'Color Code may not exceed 50 characters.',
                'metal_icon.max'      => 'Icon may not exceed 255 characters.',
            ]);


            $metal->dmt_name      = $request->dmt_name;
            $metal->dmt_tooltip   = $request->dmt_tooltip;
            $metal->dmt_status    = $request->dmt_status;
            $metal->sort_order    = $request->sort_order;
            $metal->color_code    = $request->color_code;
            $metal->metal_icon    = $request->metal_icon;
            $metal->updated_by    = Auth::id();
            $metal->date_modified = Carbon::now();
            $metal->save();

            return response()->json(['success' => 'Metal type updated successfully.']);
        }


        $updated = false;

        if ($request->has('dmt_status')) {
            $metal->dmt_status = $request->dmt_status;
            $updated = true;
        }
        if ($request->has('sort_order')) {
            $metal->sort_order = $request->sort_order;
            $updated = true;
        }

        if ($updated) {
            $metal->updated_by    = Auth::id();
            $metal->date_modified = Carbon::now();
            $metal->save();
            return response()->json(['success' => 'Metal type updated successfully.']);
        }

        return response()->json(['errors' => ['general' => ['Nothing to update']]], 422);
    }

    /**
     * Delete a MetalType record (AJAX).
     */
    public function destroy($id)
    {
        MetalType::destroy($id);
        return response()->json(['success' => 'Metal type deleted successfully.']);
    }
}

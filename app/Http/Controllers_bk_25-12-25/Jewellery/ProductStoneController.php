<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\ProductStoneType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class ProductStoneController extends Controller
{
    /**
     * Display view or return JSON for DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProductStoneType::orderBy('pst_id', 'DESC');
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('pst_status', function ($row) {
                    return $row->pst_status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('pst_display_in_front', function ($row) {
                    return $row->pst_display_in_front
                        ? '<span class="badge bg-info">Yes</span>'
                        : '<span class="badge bg-warning text-dark">No</span>';
                })
                ->addColumn('pst_image', function ($row) {
                    if ($row->pst_image && Storage::disk('public')->exists($row->pst_image)) {
                        $url = asset('storage/' . $row->pst_image);
                        return '<img src="' . $url . '" width="60" height="60" class="rounded">';
                    }
                    return '—';
                })
                ->addColumn('action', function ($row) {
                    $editBtn   = '<button data-id="' . $row->pst_id . '" class="btn btn-sm btn-primary editBtn">Edit</button>';
                    $deleteBtn = '<button data-id="' . $row->pst_id . '" class="btn btn-sm btn-danger deleteBtn">Delete</button>';
                    return $editBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['pst_status', 'pst_display_in_front', 'pst_image', 'action'])
                ->make(true);
        }

        return view('admin.jewellery.ProductStone.index');
    }

    /**
     * Store a newly created ProductStoneType in storage (with image upload).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pst_name'             => 'required|string|max:255',
            'pst_alias'            => 'nullable|string|max:250',
            'pst_description'      => 'nullable|string',
            'pst_image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pst_status'           => 'nullable|in:0,1',
            'pst_sort_order'       => 'nullable|integer',
            'pst_display_in_front' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If image was uploaded, store it under public/products_stone
        $imagePath = null;
        if ($request->hasFile('pst_image')) {
            $image      = $request->file('pst_image');
            $fileName   = time() . '_' . $image->getClientOriginalName();
            $imagePath  = $image->storeAs('products_stone', $fileName, 'public');
        }

        ProductStoneType::create([
            'pst_category_id'      => $request->pst_category_id,            // optional (if you have category relationship)
            'pst_name'             => $request->pst_name,
            'pst_alias'            => $request->pst_alias,
            'pst_description'      => $request->pst_description,
            'pst_image'            => $imagePath,
            'pst_status'           => $request->pst_status ?? 0,
            'pst_sort_order'       => $request->pst_sort_order ?? 0,
            'pst_display_in_front' => $request->pst_display_in_front ?? 0,
            'added_by'             => auth()->id(),
            'date_added'           => Carbon::now(),
        ]);

        return response()->json(['message' => 'Product Stone Type added successfully.']);
    }

    /**
     * Show the specified resource (return JSON for editing).
     */
    public function edit($id)
    {
        $stone = ProductStoneType::findOrFail($id);
        return response()->json($stone);
    }

    /**
     * Update the specified resource in storage (delete old image if replaced).
     */
    public function update(Request $request, $id)
    {
        $stone = ProductStoneType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'pst_name'             => 'required|string|max:255',
            'pst_alias'            => 'nullable|string|max:250',
            'pst_description'      => 'nullable|string',
            'pst_image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pst_status'           => 'nullable|in:0,1',
            'pst_sort_order'       => 'nullable|integer',
            'pst_display_in_front' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If a new image is uploaded, delete the old one first
        if ($request->hasFile('pst_image')) {
            if ($stone->pst_image && Storage::disk('public')->exists($stone->pst_image)) {
                Storage::disk('public')->delete($stone->pst_image);
            }
            $image      = $request->file('pst_image');
            $fileName   = time() . '_' . $image->getClientOriginalName();
            $path       = $image->storeAs('products_stone', $fileName, 'public');
        } else {
            $path = $stone->pst_image;
        }

        $stone->update([
            'pst_category_id'      => $request->pst_category_id,
            'pst_name'             => $request->pst_name,
            'pst_alias'            => $request->pst_alias,
            'pst_description'      => $request->pst_description,
            'pst_image'            => $path,
            'pst_status'           => $request->pst_status ?? 0,
            'pst_sort_order'       => $request->pst_sort_order ?? 0,
            'pst_display_in_front' => $request->pst_display_in_front ?? 0,
            'updated_by'           => auth()->id(),
            'date_modified'        => Carbon::now(),
        ]);

        return response()->json(['message' => 'Product Stone Type updated successfully.']);
    }

    /**
     * Remove the specified resource from storage (also delete the image file).
     */
    public function destroy($id)
    {
        $stone = ProductStoneType::findOrFail($id);

        // Delete the image file if it exists
        if ($stone->pst_image && Storage::disk('public')->exists($stone->pst_image)) {
            Storage::disk('public')->delete($stone->pst_image);
        }

        $stone->delete();
        return response()->json(['message' => 'Deleted successfully.']);
    }
}

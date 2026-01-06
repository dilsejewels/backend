<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductStyleGroup;
use App\Models\ProductCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ProductStyleGroupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProductStyleGroup::with('collection');
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('psg_names', function($row) {
                    return e($row->psg_names);
                })
                ->addColumn('psg_image', function($row) {
                    $images = json_decode($row->psg_image, true) ?: [];
                    return count($images)
                        ? '<img src="'.url('storage/'.$images[0]).'" class="dt-thumbnail">'
                        : '<div class="text-muted">No image</div>';
                })
                ->addColumn('collection_id', function($row) {
                    return $row->collection->name ?? '-';
                })

                ->editColumn('psg_status', function($row) {
                    $checked = $row->psg_status == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                              <input class="form-check-input toggle-status" type="checkbox" role="switch" data-id="'.$row->psg_id.'" '.$checked.'>
                            </div>';
                })
                ->editColumn('psg_display_in_front', function($row) {
                    $checked = $row->psg_display_in_front == 1 ? 'checked' : '';
                    return '<div class="form-check form-switch">
                              <input class="form-check-input toggle-display" type="checkbox" role="switch" data-id="'.$row->psg_id.'" '.$checked.'>
                            </div>';
                })
                ->editColumn('psg_sort_order', function($row) {
                    return '<input type="number" class="form-control sort-order-input" data-id="'.$row->psg_id.'" value="'.$row->psg_sort_order.'" style="width:80px;">';
                })
                ->addColumn('action', function($row) {
                    return '<div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary edit-btn" data-id="'.$row->psg_id.'"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="'.$row->psg_id.'"><i class="fas fa-trash"></i></button>
                            </div>';
                })
                ->rawColumns(['action', 'psg_image', 'collection', 'psg_status', 'psg_display_in_front', 'psg_sort_order'])
                ->make(true);
        }

        $collections = ProductCollection::all();
        return view('admin.Jewellery.ProductStyleGroup.index', compact('collections'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'collection_id' => 'nullable|exists:product_collections,id',
            'name' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
            'psg_status' => 'required|in:0,1',
            'psg_display_in_front' => 'required|in:0,1',
            'psg_sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $imagePath = $request->file('image')->store('style-groups', 'public');

        ProductStyleGroup::create([
            'collection_id' => $request->collection_id,
            'psg_names' => $request->name,
            'psg_image' => json_encode([$imagePath]),
            'psg_status' => $request->psg_status,
            'psg_display_in_front' => $request->psg_display_in_front,
            'psg_sort_order' => $request->psg_sort_order,
            'psg_alias' => $request->psg_alias,
            'added_by' => auth()->id(),
            'date_added' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Style group created successfully!']);
    }

    public function edit($id)
    {
        $group = ProductStyleGroup::findOrFail($id);
        $collections = ProductCollection::all();

        $images = json_decode($group->psg_image, true) ?: [];

        return response()->json([
            'group' => $group,
            'collections' => $collections,
            'name' => $group->psg_names,
            'image' => $images[0] ?? ''
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'collection_id' => 'nullable|exists:product_collections,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'psg_status' => 'required|in:0,1',
            'psg_display_in_front' => 'required|in:0,1',
            'psg_sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $group = ProductStyleGroup::findOrFail($id);
        $images = json_decode($group->psg_image, true) ?: [];

        if ($request->hasFile('image')) {
            if (count($images)) {
                Storage::disk('public')->delete($images[0]);
            }
            $imagePath = $request->file('image')->store('style-groups', 'public');
            $images = [$imagePath];
        } elseif ($request->remove_image == '1' && count($images)) {
            Storage::disk('public')->delete($images[0]);
            $images = [];
        }

        $group->update([
            'collection_id' => $request->collection_id,
            'psg_names' => $request->name,
            'psg_image' => json_encode($images),
            'psg_status' => $request->psg_status,
            'psg_display_in_front' => $request->psg_display_in_front,
            'psg_sort_order' => $request->psg_sort_order,
            'psg_alias' => $request->psg_alias,
            'updated_by' => auth()->id(),
            'date_modified' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Style group updated successfully!']);
    }

    public function destroy($id)
    {
        $group = ProductStyleGroup::findOrFail($id);
        $images = json_decode($group->psg_image, true) ?: [];
        foreach ($images as $image) {
            Storage::disk('public')->delete($image);
        }
        $group->delete();
        return response()->json(['success' => true, 'message' => 'Style group deleted successfully!']);
    }

    public function toggleStatus(Request $request)
    {
        $group = ProductStyleGroup::findOrFail($request->id);
        $group->psg_status = $request->status;
        $group->save();
        return response()->json(['success' => true]);
    }

    public function toggleDisplay(Request $request)
    {
        $group = ProductStyleGroup::findOrFail($request->id);
        $group->psg_display_in_front = $request->display;
        $group->save();
        return response()->json(['success' => true]);
    }

    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products_style_group,psg_id',
            'sort_order' => 'nullable|integer'
        ]);

        $group = ProductStyleGroup::findOrFail($request->id);
        $group->psg_sort_order = $request->sort_order;
        $group->save();
        return response()->json(['success' => true]);
    }
}

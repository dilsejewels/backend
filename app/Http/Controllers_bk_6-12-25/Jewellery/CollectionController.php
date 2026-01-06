<?php
namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\ProductCollection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $collections = ProductCollection::query()
                ->with([
                    'addedBy:id,name',
                    'updatedBy:id,name'
                ]);

            return DataTables::eloquent($collections)
                ->addColumn('product_types', function ($row) {
                    if (!$row->product_type) return '-';
                    
                    $typeNames = [
                        0 => 'Jewelry',
                        1 => 'Engagement', 
                        2 => 'Wedding',
                        3 => 'Gifts',
                        4 => 'Sale'
                    ];
                    
                    $types = json_decode($row->product_type);
                    $names = array_map(function($type) use ($typeNames) {
                        return $typeNames[$type] ?? 'Unknown';
                    }, $types);
                    
                    return implode(', ', $names);
                })
                ->addColumn('categories_info', function ($row) {
                    $info = [];
                    
                    if ($row->parent_category_ids) {
                        $parentIds = json_decode($row->parent_category_ids);
                        $parents = Category::whereIn('category_id', $parentIds)->pluck('category_name')->toArray();
                        $info = array_merge($info, $parents);
                    }
                    
                    if ($row->product_category_ids) {
                        $childIds = json_decode($row->product_category_ids);
                        $children = Category::whereIn('category_id', $childIds)->pluck('category_name')->toArray();
                        $info = array_merge($info, $children);
                    }
                    
                    return $info ? implode(', ', $info) : '-';
                })
                ->addColumn('status_toggle', function($row) {
                    return '<div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input status-toggle" 
                            data-id="'.$row->id.'" '.($row->status == 1 ? 'checked' : '').'>
                    </div>';
                })
                ->addColumn('display_toggle', function($row) {
                    return '<div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input display-toggle" 
                            data-id="'.$row->id.'" '.($row->display_in_menu == 1 ? 'checked' : '').'>
                    </div>';
                })
                ->addColumn('action', function($row) {
                    return '<div class="btn-group">
                        <button class="btn btn-sm btn-info editCollectionBtn" data-id="'.$row->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger deleteCollectionBtn" data-id="'.$row->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>';
                })
                ->rawColumns(['status_toggle', 'display_toggle', 'action'])
                ->toJson();
        }

        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->get();

        $productTypes = [
            0 => 'Jewelry',
            1 => 'Engagement',
            2 => 'Wedding', 
            3 => 'Gifts',
            4 => 'Sale'
        ];

        return view('admin.Jewellery.ProductCollection.index', compact('categories', 'productTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'product_type' => 'required|array|min:1',
            'product_type.*' => 'in:0,1,2,3,4',
            'category_ids' => 'nullable|array',
            'collection_image' => 'required|mimes:jpeg,png,jpg,gif,webp,svg|max:102400',
            'banner_image' => 'nullable|mimes:jpeg,png,jpg,gif,webp,svg|max:102400',
            'banner_video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/avi|max:512000',
            'alias' => 'nullable|max:255',
            'sort_order' => 'nullable|integer',
            'heading' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Process categories - separate parent and child categories
        $parentCategoryIds = [];
        $productCategoryIds = [];
        
        if ($request->category_ids) {
            foreach ($request->category_ids as $categoryId) {
                if (strpos($categoryId, 'parent_') === 0) {
                    $parentCategoryIds[] = substr($categoryId, 7);
                } else {
                    $productCategoryIds[] = $categoryId;
                }
            }
        }

        // Handle file uploads
        $imagePath = $request->file('collection_image')->store('collections', 'public');
        $bannerPath = $request->hasFile('banner_image') 
            ? $request->file('banner_image')->store('collections/banners', 'public')
            : null;
        
        $bannerVideoPath = $request->hasFile('banner_video') 
            ? $request->file('banner_video')->store('collections/videos', 'public')
            : null;

        $collection = new ProductCollection();
        $collection->name = $request->name;
        $collection->heading = $request->heading;
        $collection->description = $request->description;
        $collection->product_type = json_encode($request->product_type);
        $collection->product_category_ids = !empty($productCategoryIds) ? json_encode($productCategoryIds) : null;
        $collection->parent_category_ids = !empty($parentCategoryIds) ? json_encode($parentCategoryIds) : null;
        $collection->collection_image = $imagePath;
        $collection->banner_image = $bannerPath;
        $collection->banner_video = $bannerVideoPath;
        $collection->status = 1;
        $collection->sort_order = $request->sort_order ?? 0;
        $collection->alias = $request->alias;
        $collection->display_in_menu = $request->has('display_in_menu') ? 1 : 0;
        $collection->date_added = now();
        $collection->date_modified = now();
        $collection->added_by = auth()->id();
        $collection->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Collection created successfully!'
        ]);
    }

    public function edit($id)
    {
        $collection = ProductCollection::find($id);
        
        if (!$collection) {
            return response()->json([
                'status' => 'error',
                'message' => 'Collection not found'
            ], 404);
        }
        
        // Prepare data for form
        $collection->product_type = $collection->product_type ? json_decode($collection->product_type) : [];
        
        // Combine all category IDs for form
        $categoryIds = [];
        if ($collection->parent_category_ids) {
            $parentIds = json_decode($collection->parent_category_ids);
            foreach ($parentIds as $parentId) {
                $categoryIds[] = 'parent_' . $parentId;
            }
        }
        
        if ($collection->product_category_ids) {
            $childIds = json_decode($collection->product_category_ids);
            $categoryIds = array_merge($categoryIds, $childIds);
        }
        
        $collection->category_ids = $categoryIds;
        
        return response()->json($collection);
    }

    public function update(Request $request, $id)
    {
        $collection = ProductCollection::find($id);
        
        if (!$collection) {
            return response()->json([
                'status' => 'error',
                'message' => 'Collection not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'product_type' => 'required|array|min:1',
            'product_type.*' => 'in:0,1,2,3,4',
            'category_ids' => 'nullable|array',
            'collection_image' => 'sometimes|mimes:jpeg,png,jpg,gif,webp,svg|max:102400',
            'banner_image' => 'nullable|mimes:jpeg,png,jpg,gif,webp,svg|max:102400',
            'banner_video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/avi|max:512000',
            'alias' => 'nullable|max:255',
            'sort_order' => 'nullable|integer',
            'heading' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Process categories - separate parent and child categories
        $parentCategoryIds = [];
        $productCategoryIds = [];
        
        if ($request->category_ids) {
            foreach ($request->category_ids as $categoryId) {
                if (strpos($categoryId, 'parent_') === 0) {
                    $parentCategoryIds[] = substr($categoryId, 7);
                } else {
                    $productCategoryIds[] = $categoryId;
                }
            }
        }

        // Handle file removal
        if ($request->remove_image == '1') {
            Storage::disk('public')->delete($collection->collection_image);
            $collection->collection_image = null;
        }
        
        if ($request->remove_banner == '1') {
            Storage::disk('public')->delete($collection->banner_image);
            $collection->banner_image = null;
        }
        
        if ($request->remove_banner_video == '1') {
            Storage::disk('public')->delete($collection->banner_video);
            $collection->banner_video = null;
        }

        // Handle new files
        if ($request->hasFile('collection_image')) {
            if ($collection->collection_image && Storage::disk('public')->exists($collection->collection_image)) {
                Storage::disk('public')->delete($collection->collection_image);
            }
            $collection->collection_image = $request->file('collection_image')->store('collections', 'public');
        }

        if ($request->hasFile('banner_image')) {
            if ($collection->banner_image && Storage::disk('public')->exists($collection->banner_image)) {
                Storage::disk('public')->delete($collection->banner_image);
            }
            $collection->banner_image = $request->file('banner_image')->store('collections/banners', 'public');
        }

        if ($request->hasFile('banner_video')) {
            if ($collection->banner_video && Storage::disk('public')->exists($collection->banner_video)) {
                Storage::disk('public')->delete($collection->banner_video);
            }
            $collection->banner_video = $request->file('banner_video')->store('collections/videos', 'public');
        }

        // Update fields
        $collection->name = $request->name;
        $collection->heading = $request->heading;
        $collection->description = $request->description;
        $collection->product_type = json_encode($request->product_type);
        $collection->product_category_ids = !empty($productCategoryIds) ? json_encode($productCategoryIds) : null;
        $collection->parent_category_ids = !empty($parentCategoryIds) ? json_encode($parentCategoryIds) : null;
        $collection->sort_order = $request->sort_order ?? 0;
        $collection->alias = $request->alias;
        $collection->display_in_menu = $request->has('display_in_menu') ? 1 : 0;
        $collection->date_modified = now();
        $collection->updated_by = auth()->id();
        $collection->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Collection updated successfully!'
        ]);
    }

    public function destroy($id)
{
    $collection = ProductCollection::find($id);
    
    if (!$collection) {
        return response()->json([
            'status' => 'error',
            'message' => 'Collection not found'
        ], 404);
    }

    // Delete files only if they exist and are not null
    $filesToDelete = [];

    if ($collection->collection_image) {
        $filesToDelete[] = $collection->collection_image;
    }

    if ($collection->banner_image) {
        $filesToDelete[] = $collection->banner_image;
    }

    if ($collection->banner_video) {
        $filesToDelete[] = $collection->banner_video;
    }

    // Delete only non-null files that exist in storage
    foreach ($filesToDelete as $file) {
        if ($file && Storage::disk('public')->exists($file)) {
            Storage::disk('public')->delete($file);
        }
    }
    
    $collection->delete();
    
    return response()->json([
        'status' => 'success',
        'message' => 'Collection deleted successfully!'
    ]);
}
    
    public function updateStatus(Request $request, $id)
    {
        $collection = ProductCollection::find($id);
        $collection->update(['status' => $request->status]);
        return response()->json(['message' => 'Status updated successfully.']);
    }
    
    public function updateDisplay(Request $request, $id)
    {
        $collection = ProductCollection::find($id);
        $collection->update(['display_in_menu' => $request->display]);
        return response()->json(['message' => 'Display setting updated successfully.']);
    }
}
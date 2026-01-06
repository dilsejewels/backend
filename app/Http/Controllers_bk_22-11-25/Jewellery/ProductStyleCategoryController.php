<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductStyleCategory;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ProductStyleCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = ProductStyleCategory::with(['category.parent'])
                ->orderBy('psc_id', 'DESC')
                ->get();

            return DataTables::of($categories)
                ->addColumn('image_url', fn($row) => $row->image_url)
                ->addColumn('category_name', function($row) {
                    return $row->category->category_name ?? 'N/A';
                })
                ->addColumn('status_toggle', function($row) {
                    $status = $row->psc_status == 1;
                    return '<div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input status-toggle" 
                            data-id="'.$row->psc_id.'" '.($status ? 'checked' : '').'>
                    </div>';
                })
                ->addColumn('display_toggle', function($row) {
                    $display = $row->psc_display_in_front == 1;
                    return '<div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input display-toggle" 
                            data-id="'.$row->psc_id.'" '.($display ? 'checked' : '').'>
                    </div>';
                })
                ->addColumn('engagement_toggle', function($row) { // नया कॉलम
                    $engagement = $row->engagement_menu == 1;
                    return '<div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input engagement-toggle" 
                            data-id="'.$row->psc_id.'" '.($engagement ? 'checked' : '').'>
                    </div>';
                })
                ->rawColumns(['image_url', 'status_toggle', 'display_toggle', 'engagement_toggle'])
                ->make(true);
        }
        
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->get();

        return view('admin.Jewellery.ProductStyleCategory.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'psc_name' => 'required|string|max:250',
            'psc_alias' => 'required|string|max:250'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isParent = strpos($request->category_id, 'parent_') === 0;
        $categoryId = $isParent ? substr($request->category_id, 7) : $request->category_id;

        $imageName = null;
        if ($request->hasFile('psc_image')) {
            $file = $request->file('psc_image');
            $imageName = time() . '_' . $file->getClientOriginalName();
            $uploadPath = public_path('uploads/product_style_category');
            if (!File::exists($uploadPath)) File::makeDirectory($uploadPath, 0775, true);
            $file->move($uploadPath, $imageName);
        }

        // Handle banner image upload
        $bannerImageName = null;
        if ($request->hasFile('banner_image')) {
            $bannerFile = $request->file('banner_image');
            $bannerImageName = time() . '_banner_' . $bannerFile->getClientOriginalName();
            $bannerUploadPath = public_path('uploads/product_style_category/banner');
            if (!File::exists($bannerUploadPath)) File::makeDirectory($bannerUploadPath, 0775, true);
            $bannerFile->move($bannerUploadPath, $bannerImageName);
        }

        ProductStyleCategory::create([
            'parent_category_id' => $isParent ? $categoryId : null,
            'psc_category_id' => $categoryId,
            'psc_name' => $request->psc_name,
            'psc_image' => $imageName,
            'banner_image' => $bannerImageName,
            'psc_status' => $request->psc_status ?? 0,
            'psc_sort_order' => $request->psc_sort_order ?? 0,
            'psc_alias' => $request->psc_alias,
            'engagement_menu' => $request->engagement_menu ?? 0, // नया फ़ील्ड
            'psc_display_in_front' => $request->psc_display_in_front ?? 0,
            'date_added' => Carbon::now(),
            'added_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Product Style Category added successfully.']);
    }

    public function edit($id)
    {
        $data = ProductStyleCategory::with('category.parent')->findOrFail($id);
        $data->image_url = $data->image_url;
        $data->banner_image_url = $data->banner_image_url;
        
        // Set category_id value
        if ($data->parent_category_id) {
            $data->category_id = $data->psc_category_id;
        } else {
            $data->category_id = 'parent_' . $data->psc_category_id;
        }

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'psc_name' => 'required|string|max:250',
            'psc_alias' => 'required|string|max:250'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isParent = strpos($request->category_id, 'parent_') === 0;
        $categoryId = $isParent ? substr($request->category_id, 7) : $request->category_id;

        $category = ProductStyleCategory::findOrFail($id);
        $imageName = $category->psc_image;
        $bannerImageName = $category->banner_image;

        if ($request->hasFile('psc_image')) {
            $oldPath = public_path('uploads/product_style_category/' . $imageName);
            if (File::exists($oldPath)) File::delete($oldPath);

            $file = $request->file('psc_image');
            $imageName = time() . '_' . $file->getClientOriginalName();
            $uploadPath = public_path('uploads/product_style_category');
            if (!File::exists($uploadPath)) File::makeDirectory($uploadPath, 0775, true);
            $file->move($uploadPath, $imageName);
        }

        // Handle banner image update
        if ($request->hasFile('banner_image')) {
            // Delete old banner
            if ($bannerImageName) {
                $oldBannerPath = public_path('uploads/product_style_category/banner/' . $bannerImageName);
                if (File::exists($oldBannerPath)) File::delete($oldBannerPath);
            }

            $bannerFile = $request->file('banner_image');
            $bannerImageName = time() . '_banner_' . $bannerFile->getClientOriginalName();
            $bannerUploadPath = public_path('uploads/product_style_category/banner');
            if (!File::exists($bannerUploadPath)) File::makeDirectory($bannerUploadPath, 0775, true);
            $bannerFile->move($bannerUploadPath, $bannerImageName);
        }

        $category->update([
            'parent_category_id' => $isParent ? $categoryId : null,
            'psc_category_id' => $categoryId,
            'psc_name' => $request->psc_name,
            'psc_image' => $imageName,
            'banner_image' => $bannerImageName,
            'psc_status' => $request->psc_status ?? 0,
            'psc_sort_order' => $request->psc_sort_order ?? 0,
            'psc_alias' => $request->psc_alias,
            'engagement_menu' => $request->engagement_menu ?? 0, // नया फ़ील्ड
            'psc_display_in_front' => $request->psc_display_in_front ?? 0,
            'date_modified' => Carbon::now(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Product Style Category updated successfully.']);
    }

    public function destroy($id)
    {
        $category = ProductStyleCategory::findOrFail($id);
        
        // Delete main image
        if ($category->psc_image && File::exists(public_path('uploads/product_style_category/' . $category->psc_image))) {
            File::delete(public_path('uploads/product_style_category/' . $category->psc_image));
        }
        
        // Delete banner image
        if ($category->banner_image && File::exists(public_path('uploads/product_style_category/banner/' . $category->banner_image))) {
            File::delete(public_path('uploads/product_style_category/banner/' . $category->banner_image));
        }
        
        $category->delete();
        return response()->json(['message' => 'Product Style Category deleted successfully.']);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $category = ProductStyleCategory::findOrFail($id);
        $category->update(['psc_status' => $request->status]);
        return response()->json(['message' => 'Status updated successfully.']);
    }
    
    public function updateDisplay(Request $request, $id)
    {
        $category = ProductStyleCategory::findOrFail($id);
        $category->update(['psc_display_in_front' => $request->display]);
        return response()->json(['message' => 'Display setting updated successfully.']);
    }
    
    // नई मेथड: एंगेजमेंट मेन्यू स्टेटस अपडेट करने के लिए
    public function updateEngagement(Request $request, $id)
    {
        $category = ProductStyleCategory::findOrFail($id);
        $category->update(['engagement_menu' => $request->engagement]);
        return response()->json(['message' => 'Engagement menu setting updated successfully.']);
    }
}
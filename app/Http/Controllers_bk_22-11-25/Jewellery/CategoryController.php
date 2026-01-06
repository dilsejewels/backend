<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $categories = Category::with('parent')->orderBy('category_id', 'desc')->get();
            return response()->json($categories);
        }

        $allCategories = Category::all();
        $parentCategories = Category::where('parent_id', null)->get();
        return view('admin.Jewellery.Category.index', compact('allCategories','parentCategories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:categories,category_id',
            'category_name' => 'required|string|max:255',
            'category_alias' => 'required|string|max:255',
            'category_description' => 'nullable|string',
            'is_display_front' => 'required|boolean',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'category_header_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'category_status' => 'required|boolean',
            'seo_url' => 'nullable|string|max:150|unique:categories,seo_url',
            'category_meta_title' => 'nullable|string|max:255',
            'category_meta_description' => 'nullable|string|max:500',
            'category_meta_keyword' => 'nullable|string|max:255',
            'category_h1_tag' => 'nullable|string|max:250',
            'sort_order' => 'required|integer',
            'deleted' => 'required|boolean',
            'product_type' => 'nullable|array',
            'product_type.*' => 'string|in:0,1,2,3,4',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['category_date_added'] = now();
        $data['added_by'] = Auth::id();
        $data['parent_id'] = $request->filled('parent_id') ? $request->parent_id : null;
        
        // Let the model mutator handle product_type conversion
        // No need to manually encode here
        
        if ($request->hasFile('category_image')) {
            $data['category_image'] = $request->file('category_image')->store('categories', 'public');
        }

        if ($request->hasFile('category_header_banner')) {
            $data['category_header_banner'] = $request->file('category_header_banner')->store('banners', 'public');
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category added successfully.',
            'category' => $category,
            'allCategories' => Category::all()
        ]);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        if ($request->has('field') && $request->has('value')) {
            $field = $request->field;
            $value = $request->value;
            
            $validationRules = [];
            if (in_array($field, ['category_status', 'is_display_front'])) {
                $validationRules['value'] = 'required|boolean';
            } elseif ($field === 'sort_order') {
                $validationRules['value'] = 'required|integer';
            }
            
            $validator = Validator::make(['value' => $value], $validationRules);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            $data = [
                $field => $value,
                'category_date_modified' => now(),
                'updated_by' => auth()->id()
            ];
            
            $category->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Category field updated successfully.',
                'category' => $category
            ]);
        }

        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:categories,category_id',
            'category_name' => 'required|string|max:255',
            'category_alias' => 'required|string|max:255',
            'category_description' => 'nullable|string',
            'is_display_front' => 'required|boolean',
            'category_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'category_header_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'category_status' => 'required|boolean',
            'seo_url' => 'nullable|string|max:150|unique:categories,seo_url,'.$id.',category_id',
            'category_meta_title' => 'nullable|string|max:255',
            'category_meta_description' => 'nullable|string|max:500',
            'category_meta_keyword' => 'nullable|string|max:255',
            'category_h1_tag' => 'nullable|string|max:250',
            'sort_order' => 'required|integer',
            'deleted' => 'required|boolean',
            'product_type' => 'nullable|array',
            'product_type.*' => 'string|in:0,1,2,3,4',
        ]);
        
        if ($request->parent_id == $id) {
            return response()->json([
                'errors' => ['parent_id' => ['Category cannot be its own parent']]
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $data = $request->only([
            'parent_id',
            'category_name',
            'category_alias',
            'category_description',
            'is_display_front',
            'category_status',
            'seo_url',
            'category_meta_title',
            'category_meta_description',
            'category_meta_keyword',
            'category_h1_tag',
            'sort_order',
            'deleted',
            'product_type',
        ]);

        // Let model mutator handle product_type conversion

        if ($request->hasFile('category_image')) {
            if ($category->category_image) {
                Storage::disk('public')->delete($category->category_image);
            }
            $data['category_image'] = $request->file('category_image')->store('categories', 'public');
        }

        if ($request->hasFile('category_header_banner')) {
            if ($category->category_header_banner) {
                Storage::disk('public')->delete($category->category_header_banner);
            }
            $data['category_header_banner'] = $request->file('category_header_banner')->store('banners', 'public');
        }

        $data['category_date_modified'] = now();
        $data['updated_by'] = auth()->id();

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'category' => $category,
            'allCategories' => Category::all()
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        if ($category->category_image) {
            Storage::disk('public')->delete($category->category_image);
        }
        if ($category->category_header_banner) {
            Storage::disk('public')->delete($category->category_header_banner);
        }
        
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
            'allCategories' => Category::all()
        ]);
    }

    public function getChildCategories(Request $request)
    {
        $parentId = $request->input('parent_id');

        $childCategories = \App\Models\Category::where('parent_id', $parentId)
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        return response()->json($childCategories);
    }
}
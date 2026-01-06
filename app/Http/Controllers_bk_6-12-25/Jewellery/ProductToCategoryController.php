<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\ProductToCategory;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductToCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $mappings = ProductToCategory::with(['product:products_id,products_name', 'category:category_id,category_name'])
                ->get()
                ->map(function($item) {
                    return [
                        'products_id'    => $item->products_id,
                        'categories_id'  => $item->categories_id,
                        'product_name'   => optional($item->product)->products_name ?? '',
                        'category_name'  => optional($item->category)->category_name ?? '',
                    ];
                });
            return response()->json(['data' => $mappings]); // ✅ FIXED
        }

        return view('admin.jewellery.ProductToCategory.index');
    }

    public function store(Request $request)
    {
        $rules = [
            'products_id'   => 'required|exists:products,products_id',
            'categories_id' => 'required|exists:categories,category_id',
        ];

        $messages = [
            'products_id.required'   => 'Product is required.',
            'categories_id.required' => 'Category is required.',
            'products_id.exists'     => 'Invalid product.',
            'categories_id.exists'   => 'Invalid category.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = ProductToCategory::where('products_id', $request->products_id)
                                   ->where('categories_id', $request->categories_id)
                                   ->exists();
        if ($exists) {
            return response()->json([
                'errors' => ['duplicate' => ['This mapping already exists.']]
            ], 422);
        }

        ProductToCategory::create([
            'products_id'   => $request->products_id,
            'categories_id' => $request->categories_id,
        ]);

        return response()->json(['message' => 'Mapping created successfully.']);
    }

    public function show($products_id, $categories_id)
    {
        $mapping = ProductToCategory::where('products_id', $products_id)
            ->where('categories_id', $categories_id)
            ->firstOrFail();

        return response()->json([
            'products_id'   => $mapping->products_id,
            'categories_id' => $mapping->categories_id,
        ]);
    }

    public function update(Request $request, $products_id, $categories_id)
    {
        $rules = [
            'products_id'   => 'required|exists:products,products_id',
            'categories_id' => 'required|exists:categories,category_id',
        ];

        $messages = [
            'products_id.required'   => 'Product is required.',
            'categories_id.required' => 'Category is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ProductToCategory::where('products_id', $products_id)
                         ->where('categories_id', $categories_id)
                         ->delete();

        ProductToCategory::create([
            'products_id'   => $request->products_id,
            'categories_id' => $request->categories_id,
        ]);

        return response()->json(['message' => 'Mapping updated successfully.']);
    }

    public function destroy($products_id, $categories_id)
    {
        ProductToCategory::where('products_id', $products_id)
                         ->where('categories_id', $categories_id)
                         ->delete();

        return response()->json(['message' => 'Mapping deleted successfully.']);
    }
}

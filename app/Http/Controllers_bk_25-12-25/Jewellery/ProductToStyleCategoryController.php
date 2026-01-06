<?php
namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStyleCategory;
use App\Models\ProductToStyleCategory;
use Illuminate\Support\Facades\Validator;

class ProductToStyleCategoryController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $data = ProductToStyleCategory::with(['product', 'styleCategory'])->get()->map(function($item) {
                return [
                    'sptsc_id'                => $item->sptsc_id,
                    'sptsc_products_id'       => $item->sptsc_products_id,
                    'sptsc_style_category_id' => $item->sptsc_style_category_id,
                    'product_name'            => optional($item->product)->products_name,
                    'style_category_name'     => optional($item->styleCategory)->psc_name,
                ];
            });

            return response()->json([
                'data' => $data // ✅ This is the required structure
            ]);
        }

        $products = Product::select('products_id', 'products_name')->get();
        $styleCategories = ProductStyleCategory::select('psc_id', 'psc_name')->get();

        return view('admin.jewellery.ProductToStyleCategory.index', compact('products', 'styleCategories'));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sptsc_products_id'       => 'required|exists:products,products_id',
            'sptsc_style_category_id' => 'required|exists:products_style_category,psc_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = ProductToStyleCategory::where('sptsc_products_id', $request->sptsc_products_id)
            ->where('sptsc_style_category_id', $request->sptsc_style_category_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['duplicate' => ['Mapping already exists.']]
            ], 422);
        }

        ProductToStyleCategory::create($request->only('sptsc_products_id', 'sptsc_style_category_id'));

        return response()->json(['message' => 'Mapping created successfully.']);
    }

    public function edit($id)
    {
        $mapping = ProductToStyleCategory::findOrFail($id);
        return response()->json($mapping);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sptsc_products_id'       => 'required|exists:products,products_id',
            'sptsc_style_category_id' => 'required|exists:products_style_category,psc_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = ProductToStyleCategory::where('sptsc_products_id', $request->sptsc_products_id)
            ->where('sptsc_style_category_id', $request->sptsc_style_category_id)
            ->where('sptsc_id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['duplicate' => ['Mapping already exists.']]
            ], 422);
        }

        $mapping = ProductToStyleCategory::findOrFail($id);
        $mapping->update($request->only('sptsc_products_id', 'sptsc_style_category_id'));

        return response()->json(['message' => 'Mapping updated successfully.']);
    }

    public function destroy($id)
    {
        $mapping = ProductToStyleCategory::findOrFail($id);
        $mapping->delete();
        return response()->json(['message' => 'Mapping deleted successfully.']);
    }
}

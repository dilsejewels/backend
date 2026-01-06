<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStoneType;
use App\Models\ProductToStoneType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductToStoneTypeController extends Controller
{
    public function index()
    {
        $products = Product::select('products_id', 'products_name')->get();
        $stoneTypes = ProductStoneType::select('pst_id', 'pst_name')->get();

        return view('admin.jewellery.ProductToStoneType.index', compact('products', 'stoneTypes'));
    }

    public function fetch()
    {
        $data = ProductToStoneType::with(['product', 'stoneType'])->get()->map(function ($item) {
            return [
                'sptst_products_id'   => $item->sptst_products_id,
                'sptst_stone_type_id' => $item->sptst_stone_type_id,
                'product_name'        => optional($item->product)->products_name,
                'stone_type_name'     => optional($item->stoneType)->pst_name,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sptst_products_id'     => 'required|exists:products,products_id',
            'sptst_stone_type_id'   => 'required|exists:products_stone_type,pst_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = ProductToStoneType::where('sptst_products_id', $request->sptst_products_id)
            ->where('sptst_stone_type_id', $request->sptst_stone_type_id)
            ->exists();

        if ($exists) {
            return response()->json(['errors' => ['duplicate' => ['This combination already exists.']]], 422);
        }

        ProductToStoneType::create($request->only('sptst_products_id', 'sptst_stone_type_id'));

        return response()->json(['message' => 'Mapping created successfully.']);
    }

    public function show($productId, $stoneId)
    {
        $mapping = ProductToStoneType::where('sptst_products_id', $productId)
            ->where('sptst_stone_type_id', $stoneId)
            ->firstOrFail();

        return response()->json($mapping);
    }

    public function update(Request $request, $productId, $stoneId)
    {
        $validator = Validator::make($request->all(), [
            'sptst_products_id'     => 'required|exists:products,products_id',
            'sptst_stone_type_id'   => 'required|exists:products_stone_type,pst_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mapping = ProductToStoneType::where('sptst_products_id', $productId)
            ->where('sptst_stone_type_id', $stoneId)
            ->firstOrFail();

        $exists = ProductToStoneType::where('sptst_products_id', $request->sptst_products_id)
            ->where('sptst_stone_type_id', $request->sptst_stone_type_id)
            ->where('sptst_id', '!=', $mapping->sptst_id)
            ->exists();

        if ($exists) {
            return response()->json(['errors' => ['duplicate' => ['This combination already exists.']]], 422);
        }

        $mapping->update($request->only('sptst_products_id', 'sptst_stone_type_id'));

        return response()->json(['message' => 'Mapping updated successfully.']);
    }

    public function destroy(Request $request)
    {
        $mapping = ProductToStoneType::where('sptst_products_id', $request->sptst_products_id)
            ->where('sptst_stone_type_id', $request->sptst_stone_type_id)
            ->firstOrFail();

        $mapping->delete();

        return response()->json(['message' => 'Mapping deleted successfully.']);
    }
}
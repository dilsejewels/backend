<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\ProductToShape;
use App\Models\Product;
use App\Models\DiamondShape;
use Illuminate\Http\Request;

class ProductToShapeController extends Controller
{
    public function index()
    {
        $products = Product::select('products_id', 'products_name')->get();
        $shapes = DiamondShape::select('id', 'name')->get();

        return view('admin.jewellery.ProductToShape.index', compact('products', 'shapes'));
    }

    public function fetch()
    {
        $data = ProductToShape::with(['product', 'shape'])->get();

        return response()->json(
            $data->map(function ($item) {
                return [
                    'pts_id'       => $item->pts_id,
                    'product_name' => $item->product->products_name ?? 'N/A',
                    'shape_name'   => $item->shape->name ?? 'N/A',
                    'products_id'  => $item->products_id,
                    'shape_id'     => $item->shape_id,
                ];
            })
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'products_id' => 'required|exists:products,products_id',
            'shape_id'    => 'required|exists:diamond_shape_master,id',
        ]);

        $exists = ProductToShape::where('products_id', $request->products_id)
            ->where('shape_id', $request->shape_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['shape_id' => ['This product-shape combination already exists.']],
            ], 422);
        }

        ProductToShape::create($request->only(['products_id', 'shape_id']));

        return response()->json(['message' => 'Mapping created successfully.']);
    }

    public function show($id)
    {
        $mapping = ProductToShape::findOrFail($id);
        return response()->json($mapping);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'products_id' => 'required|exists:products,products_id',
            'shape_id'    => 'required|exists:diamond_shape_master,id',
        ]);

        $exists = ProductToShape::where('products_id', $request->products_id)
            ->where('shape_id', $request->shape_id)
            ->where('pts_id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['shape_id' => ['This product-shape combination already exists.']],
            ], 422);
        }

        $mapping = ProductToShape::findOrFail($id);
        $mapping->update($request->only(['products_id', 'shape_id']));

        return response()->json(['message' => 'Mapping updated successfully.']);
    }

    public function destroy($id)
    {
        $mapping = ProductToShape::findOrFail($id);
        $mapping->delete();

        return response()->json(['message' => 'Mapping deleted successfully.']);
    }
}

<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductToOption;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Support\Facades\Validator;

class ProductToOptionController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $data = ProductToOption::with(['product', 'option', 'value'])->get()->map(function ($item) {
                return [
                    'id' => $item->products_to_option_id,  // Important: here key is 'id'
                    'product' => optional($item->product)->products_name,
                    'option' => optional($item->option)->options_name,
                    'value' => optional($item->value)->value_name,
                    'options_price' => $item->options_price,
                ];
            });
            return response()->json(['data' => $data]);
        }

        return view('admin.jewellery.ProductToOption.index', [
            'products' => Product::all(),
            'options' => ProductOption::all(),
            'values' => ProductOptionValue::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products_id' => 'required|exists:products,products_id',
            'options_id' => 'required|exists:products_options,options_id',
            'value_id' => 'required|exists:products_options_values,value_id',
        ], [
            'products_id.required' => 'Product is required',
            'options_id.required' => 'Option is required',
            'value_id.required' => 'Value is required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ProductToOption::create($request->all());
        return response()->json(['message' => 'Mapping created successfully']);
    }

    public function show($id)
    {
        $data = ProductToOption::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'products_id' => 'required|exists:products,products_id',
            'options_id' => 'required|exists:products_options,options_id',
            'value_id' => 'required|exists:products_options_values,value_id',
        ], [
            'products_id.required' => 'Product is required',
            'options_id.required' => 'Option is required',
            'value_id.required' => 'Value is required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = ProductToOption::findOrFail($id);
        $data->update($request->all());
        return response()->json(['message' => 'Mapping updated successfully']);
    }

    public function destroy(Request $request)
    {
        $data = ProductToOption::findOrFail($request->id);
        $data->delete();
        return response()->json(['message' => 'Mapping deleted']);
    }
}

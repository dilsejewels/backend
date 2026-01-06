<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\ProductAssignOption;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Facades\DB;

class ProductAssignOptionController extends Controller
{
    public function index()
    {
        $products = Product::select('products_id', 'products_name')->get();
        $options = ProductOption::select('options_id', 'options_name')->get();

        return view('admin.jewellery.ProductAssignOption.index', compact('products', 'options'));
    }

   public function fetch()
    {
        $data = DB::table('products_assign_options')
            ->select('products_id', 'options_id')
            ->get();

        return response()->json([
            'data' => $data // DataTables expects this key
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'products_id' => 'required|integer',
            'options_id'  => 'required|integer',
        ], [
            'products_id.required' => 'Product is required.',
            'options_id.required'  => 'Option is required.',
        ]);

        // Check if combination already exists
        $exists = ProductAssignOption::where('products_id', $request->products_id)
            ->where('options_id', $request->options_id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'This combination already exists.'], 422);
        }

        // Insert new record
        ProductAssignOption::create([
            'products_id' => $request->products_id,
            'options_id'  => $request->options_id,
        ]);

        return response()->json(['success' => 'Product option assigned successfully.']);
    }


    public function show($productId, $optionId)
    {
        $data = ProductAssignOption::where('products_id', $productId)
            ->where('options_id', $optionId)
            ->firstOrFail();

        return response()->json($data);
    }

    public function update(Request $request, $productId, $optionId)
    {
        $request->validate([
            'products_id' => 'required|integer',
            'options_id'  => 'required|integer',
        ]);

        // Check if new combination already exists
        $exists = ProductAssignOption::where('products_id', $request->products_id)
            ->where('options_id', $request->options_id)
            ->where('products_id', '!=', $productId)
            ->where('options_id', '!=', $optionId)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'This combination already exists.'], 422);
        }

        // Find existing record
        $assign = ProductAssignOption::where('products_id', $productId)
            ->where('options_id', $optionId)
            ->firstOrFail();

        // Update directly (no need to delete/create)
        $assign->products_id = $request->products_id;
        $assign->options_id = $request->options_id;
        $assign->save();

        return response()->json(['success' => 'Product option updated successfully.']);
    }

    public function destroy(Request $request)
    {
        $deleted = ProductAssignOption::where('products_id', $request->products_id)
            ->where('options_id', $request->options_id)
            ->delete();

        if ($deleted) {
            return response()->json(['success' => 'Deleted successfully.']);
        }

        return response()->json(['error' => 'Record not found.'], 404);
    }
}
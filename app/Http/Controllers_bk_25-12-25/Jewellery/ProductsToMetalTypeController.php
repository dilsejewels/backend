<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductsToMetalType;
use App\Models\Product;
use App\Models\MetalType;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class ProductsToMetalTypeController extends Controller
{
    /**
     * Show the index page with DataTable
     */
    public function index()
    {
        // Fetch products (id and products_name) for the select dropdown
        $products = Product::select('products_id as id', 'products_name')->get();

        // Fetch metal types (dmt_id and dmt_name) for the select dropdown
        $metalTypes = MetalType::select('dmt_id as id', 'dmt_name')->get();

        return view('admin.Jewellery.products_to_metal_type.index', compact('products', 'metalTypes'));
    }

    /**
     * Fetch data for DataTable (AJAX)
     */
    public function fetch(Request $request)
    {
        $query = ProductsToMetalType::with(['product', 'metalType'])->orderBy('sptmt_id', 'desc');

        return DataTables::of($query)
            ->addColumn('product_name', function ($row) {
                return $row->product ? $row->product->products_name : 'N/A';
            })
            ->addColumn('metal_type_name', function ($row) {
                return $row->metalType ? $row->metalType->dmt_name : 'N/A';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-info editBtn" data-id="'.$row->sptmt_id.'">Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->sptmt_id.'">Delete</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created record (AJAX)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sptmt_products_id'   => 'required|integer|exists:products,products_id',
            'sptmt_metal_type_id' => 'required|integer|exists:metal_type,dmt_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ProductsToMetalType::create($request->only([
            'sptmt_products_id',
            'sptmt_metal_type_id',
        ]));

        return response()->json(['success' => 'Record added successfully']);
    }

    /**
     * Get a single record for editing (AJAX)
     */
    public function show($id)
    {
        return ProductsToMetalType::findOrFail($id);
    }

    /**
     * Update an existing record (AJAX)
     */
    public function update(Request $request, $id)
    {
        $record = ProductsToMetalType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sptmt_products_id'   => 'required|integer|exists:products,products_id',
            'sptmt_metal_type_id' => 'required|integer|exists:metal_type,dmt_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record->update($request->only([
            'sptmt_products_id',
            'sptmt_metal_type_id',
        ]));

        return response()->json(['success' => 'Record updated successfully']);
    }

    /**
     * Delete a record (AJAX)
     */
    public function destroy($id)
    {
        ProductsToMetalType::destroy($id);
        return response()->json(['success' => 'Record deleted successfully']);
    }
}

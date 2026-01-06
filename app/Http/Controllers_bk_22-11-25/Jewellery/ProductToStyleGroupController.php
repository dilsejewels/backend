<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductToStyleGroup;
use App\Models\Product;
use App\Models\ProductStyleCategory;
use Illuminate\Support\Facades\DB;

class ProductToStyleGroupController extends Controller
{
    public function index()
    {
        $products = Product::select('products_id', 'products_name')->get();
        $styleCategories = ProductStyleCategory::select('psc_id', 'psc_name')->get();

        return view('admin.jewellery.ProductToStyleGroup.index', compact('products', 'styleCategories'));
    }

    public function fetch()
    {
        $data = DB::table('products_to_style_group as ps')
            ->leftJoin('products as p', 'ps.sptsg_products_id', '=', 'p.products_id')
            ->leftJoin('products_style_category as sc', 'ps.sptsg_style_category_id', '=', 'sc.psc_id')
            ->select('ps.sptsg_id', 'p.products_name', 'sc.psc_name')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sptsg_products_id' => 'required|integer',
            'sptsg_style_category_id' => 'required|integer',
        ], [
            'sptsg_products_id.required' => 'कृपया प्रोडक्ट चुनें (Please select a product).',
            'sptsg_style_category_id.required' => 'कृपया शैली श्रेणी चुनें (Please select a style category).',
        ]);

        ProductToStyleGroup::create($request->only('sptsg_products_id', 'sptsg_style_category_id'));

        return response()->json(['success' => 'Product style group created successfully.']);
    }

    public function show($id)
    {
        $data = ProductToStyleGroup::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sptsg_products_id' => 'required|integer',
            'sptsg_style_category_id' => 'required|integer',
        ], [
            'sptsg_products_id.required' => 'कृपया प्रोडक्ट चुनें (Please select a product).',
            'sptsg_style_category_id.required' => 'कृपया शैली श्रेणी चुनें (Please select a style category).',
        ]);

        ProductToStyleGroup::where('sptsg_id', $id)->update([
            'sptsg_products_id' => $request->sptsg_products_id,
            'sptsg_style_category_id' => $request->sptsg_style_category_id,
        ]);

        return response()->json(['success' => 'Updated successfully.']);
    }

    public function destroy($id)
    {
        ProductToStyleGroup::where('sptsg_id', $id)->delete();
        return response()->json(['success' => 'Deleted successfully.']);
    }
}

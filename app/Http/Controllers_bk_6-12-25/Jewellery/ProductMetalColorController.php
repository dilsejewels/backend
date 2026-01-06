<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\ProductMetalColor;
use Illuminate\Http\Request;
use Carbon\Carbon;
class ProductMetalColorController extends Controller
{
    public function index()
    {
        return view('admin.products_metal_color.index');
    }

    public function fetch()
    {
        return response()->json([
            'data' => ProductMetalColor::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dmc_name' => 'required|string|max:250',
            'dmc_status' => 'required|boolean'
        ]);

        $data['added_by'] = 1; // Auth::id() if login system
        $data['date_added'] = Carbon::now();

        $metalColor = ProductMetalColor::create($data);

        return response()->json(['success' => true, 'data' => $metalColor]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'dmc_name' => 'required|string|max:250',
            'dmc_status' => 'required|boolean'
        ]);

        $data['updated_by'] = 1;
        $data['date_modified'] = Carbon::now();

        $metalColor = ProductMetalColor::findOrFail($id);
        $metalColor->update($data);

        return response()->json(['success' => true, 'data' => $metalColor]);
    }

    public function destroy($id)
    {
        ProductMetalColor::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $metalColor = ProductMetalColor::findOrFail($id);
        return response()->json($metalColor);
    }
}


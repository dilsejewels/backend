<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopTaxClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopTaxClassesController extends Controller
{
    public function index()
    {
        return view('admin.Jewellery.ShopTaxClasses.index');
    }

    public function getData()
    {
        $taxClasses = ShopTaxClass::orderBy('tax_class_id', 'desc')->get();
        return response()->json(['data' => $taxClasses]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tax_class_title' => 'required|string|max:100',
            'tax_class_description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['date_added'] = now();
        $data['added_by'] = Auth::id();

        $taxClass = ShopTaxClass::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tax class added successfully!',
            'taxClass' => $taxClass
        ]);
    }

    public function show($id)
    {
        $taxClass = ShopTaxClass::findOrFail($id);
        return response()->json($taxClass);
    }

    public function update(Request $request, $id)
    {
        $taxClass = ShopTaxClass::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tax_class_title' => 'required|string|max:100',
            'tax_class_description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['date_modified'] = now();
        $data['updated_by'] = Auth::id();

        $taxClass->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tax class updated successfully!',
            'taxClass' => $taxClass
        ]);
    }

    public function destroy($id)
    {
        $taxClass = ShopTaxClass::findOrFail($id);
        $taxClass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax class deleted successfully!'
        ]);
    }
}
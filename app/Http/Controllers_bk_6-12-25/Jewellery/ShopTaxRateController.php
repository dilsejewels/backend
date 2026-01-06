<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopTaxRate;
use App\Models\ShopZone;
use App\Models\ShopTaxClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopTaxRateController extends Controller
{
    public function index()
    {
        $zones = ShopZone::all();
        $taxClasses = ShopTaxClass::all();
        return view('admin.Jewellery.ShopTaxRate.index', compact('zones', 'taxClasses'));
    }

    public function getData()
    {
        $taxRates = ShopTaxRate::with(['taxZone', 'taxClass'])
                    ->orderBy('tax_rates_id', 'desc')
                    ->get();
                    
        return response()->json(['data' => $taxRates]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tax_zone_id' => 'required|exists:shop_zones,zone_id',
            'tax_class_id' => 'required|exists:shop_tax_classes,tax_class_id',
            'tax_priority' => 'required|integer|min:1',
            'tax_rate' => 'required|numeric|min:0',
            'tax_description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['date_added'] = now();
        $data['added_by'] = Auth::id();
        $data['tax_retailer_id'] = 0; // Default value

        $taxRate = ShopTaxRate::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tax rate added successfully!',
            'taxRate' => $taxRate
        ]);
    }

    public function show($id)
    {
        $taxRate = ShopTaxRate::with(['taxZone', 'taxClass'])->findOrFail($id);
        return response()->json($taxRate);
    }

    public function update(Request $request, $id)
    {
        $taxRate = ShopTaxRate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tax_zone_id' => 'required|exists:shop_zones,zone_id',
            'tax_class_id' => 'required|exists:shop_tax_classes,tax_class_id',
            'tax_priority' => 'required|integer|min:1',
            'tax_rate' => 'required|numeric|min:0',
            'tax_description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['date_modified'] = now();
        $data['updated_by'] = Auth::id();

        $taxRate->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tax rate updated successfully!',
            'taxRate' => $taxRate
        ]);
    }

    public function destroy($id)
    {
        $taxRate = ShopTaxRate::findOrFail($id);
        $taxRate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax rate deleted successfully!'
        ]);
    }
}
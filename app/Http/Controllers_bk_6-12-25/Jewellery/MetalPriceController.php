<?php
// app/Http/Controllers/Jewellery/MetalPriceController.php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\MetalPrice;
use App\Models\ProductVariation;
use App\Models\MetalType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetalPriceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $metalPrices = MetalPrice::select('*');
            
            return datatables()->of($metalPrices)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    $buttons = '<button class="btn btn-sm btn-info editMetalPriceBtn" data-id="'.$row->id.'">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger deleteMetalPriceBtn" data-id="'.$row->id.'">
                                <i class="fas fa-trash"></i> Delete
                            </button>';
                    
                    $matchingMetalTypes = MetalType::where('dmt_name', $row->metal_type)->count();
                    if ($matchingMetalTypes > 0) {
                        $buttons .= '<button class="btn btn-sm btn-warning updateProductPricesBtn mt-1" data-id="'.$row->id.'" 
                                    data-metal-type="'.$row->metal_type.'" 
                                    data-metal-quality="'.$row->metal_quality.'">
                                <i class="fas fa-sync-alt"></i> Update Products
                            </button>';
                    }
                    
                    return $buttons;
                })
                ->editColumn('date', function($row) {
                    return \Carbon\Carbon::parse($row->date)->format('d-m-Y');
                })
                ->editColumn('metal_quality', function($row) {
                    return $row->metal_quality ?? '-';
                })
                ->editColumn('price_per_gram', function($row) {
                    return '$' . number_format($row->price_per_gram, 2);
                })
                ->editColumn('price_per_10gram', function($row) {
                    return '$' . number_format($row->price_per_10gram, 2);
                })
                ->editColumn('created_at', function($row) {
                    return $row->created_at->format('d-m-Y H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.Jewellery.MetalPrice.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'metal_type' => 'required|string',
            'metal_quality' => 'nullable|string',
            'price_per_gram' => 'required|numeric|min:0',
            'price_per_10gram' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $existingPrice = MetalPrice::where('date', $request->date)
                ->where('metal_type', $request->metal_type)
                ->where('metal_quality', $request->metal_quality)
                ->first();

            if ($existingPrice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Price already exists for this date, metal type and quality!'
                ], 422);
            }

            $metalPrice = MetalPrice::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Metal price added successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add metal price! Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $metalPrice = MetalPrice::findOrFail($id);
        return response()->json($metalPrice);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'metal_type' => 'required|string',
            'metal_quality' => 'nullable|string',
            'price_per_gram' => 'required|numeric|min:0',
            'price_per_10gram' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $metalPrice = MetalPrice::findOrFail($id);

            $existingPrice = MetalPrice::where('date', $request->date)
                ->where('metal_type', $request->metal_type)
                ->where('metal_quality', $request->metal_quality)
                ->where('id', '!=', $id)
                ->first();

            if ($existingPrice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Price already exists for this date, metal type and quality!'
                ], 422);
            }

            $metalPrice->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Metal price updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update metal price! Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $metalPrice = MetalPrice::findOrFail($id);
            $metalPrice->delete();

            return response()->json([
                'status' => true,
                'message' => 'Metal price deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete metal price! Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Product variations ki prices update karein - RATIO MAINTAIN KARTE HUE
     */
    public function updateProductPrices(Request $request)
    {
        try {
            Log::info('=== PRODUCT PRICE UPDATE STARTED ===');
            Log::info('Request Data:', $request->all());

            $metalPriceId = $request->input('metal_price_id');
            $metalType = $request->input('metal_type');
            $metalQuality = $request->input('metal_quality');
            
            if ($metalPriceId) {
                $metalPrice = MetalPrice::findOrFail($metalPriceId);
                $updatedCount = $this->updateVariationsWithMetalPrice($metalPrice);
                
                return response()->json([
                    'status' => true,
                    'message' => "Updated {$updatedCount} product variations with latest {$metalPrice->metal_type} price!"
                ]);
            }
            
            if ($metalType) {
                $latestMetalPrice = MetalPrice::where('metal_type', $metalType)
                    ->when($metalQuality, function($query) use ($metalQuality) {
                        return $query->where('metal_quality', $metalQuality);
                    })
                    ->latest('date')
                    ->first();
                    
                Log::info('Latest Metal Price:', $latestMetalPrice ? $latestMetalPrice->toArray() : 'NOT FOUND');

                if (!$latestMetalPrice) {
                    return response()->json([
                        'status' => false,
                        'message' => "No metal price found for {$metalType}" . ($metalQuality ? " ({$metalQuality})" : "")
                    ], 404);
                }
                
                $updatedCount = $this->updateVariationsWithMetalPrice($latestMetalPrice);
                
                return response()->json([
                    'status' => true,
                    'message' => "Updated {$updatedCount} product variations with latest {$latestMetalPrice->metal_type} price!"
                ]);
            }
            
            $result = $this->updateAllVariationsWithLatestPrices();
            
            return response()->json([
                'status' => true,
                'message' => "Updated {$result['updated']} out of {$result['total']} product variations with latest metal prices!"
            ]);

        } catch (\Exception $e) {
            Log::error('Update product prices error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => false,
                'message' => 'Failed to update product prices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Specific metal price se variations update karein - RATIO MAINTAIN KARTE HUE
     */
    private function updateVariationsWithMetalPrice(MetalPrice $metalPrice)
    {
        $matchingMetalTypes = MetalType::where('dmt_name', $metalPrice->metal_type)
            ->pluck('dmt_id');

        Log::info("Matching metal types for {$metalPrice->metal_type}: " . json_encode($matchingMetalTypes->toArray()));

        if ($matchingMetalTypes->isEmpty()) {
            Log::warning("No matching metal types found for: " . $metalPrice->metal_type);
            return 0;
        }

        $variations = ProductVariation::whereIn('metal_color_id', $matchingMetalTypes)
            ->where('weight', '>', 0)
            ->get();

        Log::info("Found {$variations->count()} variations to update");

        $updatedCount = 0;

        foreach ($variations as $variation) {
            try {
                // NAYA METHOD USE KAREN - RATIO MAINTAIN KARTE HUE
                $success = $variation->updatePriceFromMetal($metalPrice);
                if ($success) {
                    $updatedCount++;
                    
                    Log::info("Updated variation {$variation->id}:", [
                        'weight' => $variation->weight,
                        'metal_price_per_gram' => $metalPrice->price_per_gram,
                        'old_price' => $variation->getOriginal('price'),
                        'new_price' => $variation->price,
                        'old_regular_price' => $variation->getOriginal('regular_price'),
                        'new_regular_price' => $variation->regular_price,
                        'ratio_maintained' => true
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to update variation {$variation->id}: " . $e->getMessage());
            }
        }

        return $updatedCount;
    }

    /**
     * Sabhi variations ko latest metal prices se update karein - RATIO MAINTAIN KARTE HUE
     */
    private function updateAllVariationsWithLatestPrices()
    {
        $latestMetalPrices = MetalPrice::whereIn('id', function($query) {
            $query->select(DB::raw('MAX(id)'))
                  ->from('metal_prices')
                  ->groupBy('metal_type', 'metal_quality');
        })->get();

        $totalVariations = ProductVariation::where('weight', '>', 0)->count();
        $updatedCount = 0;

        foreach ($latestMetalPrices as $metalPrice) {
            $updated = $this->updateVariationsWithMetalPrice($metalPrice);
            $updatedCount += $updated;
            Log::info("Updated {$updated} variations for {$metalPrice->metal_type}");
        }

        return [
            'updated' => $updatedCount,
            'total' => $totalVariations
        ];
    }

    /**
     * Metal prices ke saath product price update ka form dikhayein
     */
    public function showPriceUpdateForm()
    {
        $latestMetalPrices = MetalPrice::whereIn('id', function($query) {
            $query->select(DB::raw('MAX(id)'))
                  ->from('metal_prices')
                  ->groupBy('metal_type', 'metal_quality');
        })->get();

        foreach ($latestMetalPrices as $metalPrice) {
            $matchingCount = MetalType::where('dmt_name', $metalPrice->metal_type)
                ->join('product_variations', 'metal_type.dmt_id', '=', 'product_variations.metal_color_id')
                ->where('product_variations.weight', '>', 0)
                ->count();
                
            $metalPrice->matching_products_count = $matchingCount;
            
            Log::info("Metal Type: {$metalPrice->metal_type}, Matching Products: {$matchingCount}");
        }

        return view('admin.Jewellery.MetalPrice.price-update-form', compact('latestMetalPrices'));
    }
}
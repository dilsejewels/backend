<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariation;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function getBestSellingRings(Request $request, $categoryType)
    {
        try {
            // Map category types to search patterns
            $categoryPatterns = [
                'anniversary' => '%Anniversary%',
                'eternity' => '%Eternity%',
                'stackable' => '%Stackable%'
            ];

            if (!array_key_exists($categoryType, $categoryPatterns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid category type. Available types: anniversary, eternity, stackable'
                ], 400);
            }

            $category = Category::where('category_name', 'like', $categoryPatterns[$categoryType])->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => ucfirst($categoryType) . ' category not found'
                ], 404);
            }

            $bestSellingVariations = ProductVariation::with([
                'product' => function ($query) {
                    $query->select('products_id', 'products_name', 'categories_id');
                },
                'metalColor',
                'shape'
            ])
                ->where('is_best_selling', 1)
                ->whereHas('product', function ($query) use ($category) {
                    $query->where('categories_id', $category->category_id)
                        ->where('products_status', 1);
                })
                ->orderBy('created_at', 'desc')
                ->take(12)
                ->get();

            // Format response
            $formattedVariations = $bestSellingVariations->map(function ($variation) {
                return [
                    'id' => $variation->id,
                    'product_id' => $variation->product->products_id,
                    'product_name' => $variation->product->products_name,
                    'price' => $variation->price,
                    'regular_price' => $variation->regular_price,
                    'weight' => $variation->weight,
                    'metal_color' => $variation->metalColor ? $variation->metalColor->dmt_name : 'N/A',
                    'shape' => $variation->shape ? $variation->shape->name : 'N/A',
                    'images' => $variation->images,
                    'is_best_selling' => $variation->is_best_selling,
                    'sku' => $variation->sku,
                    'video_url' => $variation->video ? asset('storage/variation_videos/' . $variation->video) : null
                ];
            });

            return response()->json([
                'success' => true,
                'category' => $category->category_name,
                'category_type' => $categoryType,
                'data' => $formattedVariations,
                'count' => $bestSellingVariations->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Best selling rings API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch best selling ' . $categoryType . ' rings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Models\DiamondColor;
use App\Models\DiamondShape;
use Illuminate\Http\Request;
use App\Models\DiamondMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductStyleCategory;


class ShapeController extends Controller
{
    public function filterDiamondsByShape($shape_id): JsonResponse
    {
        // Validate if shape_id exists in DiamondMaster and if it's valid
        if (!is_numeric($shape_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid shape ID.',
            ], 400);
        }

        // Fetch diamonds from DiamondMaster that match the selected shape
        $diamonds = DiamondMaster::where('shape', $shape_id)
            ->with(['vendor', 'shape', 'color', 'clarity'])
            ->get();

        // Check if diamonds are found
        if ($diamonds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No diamonds found for this shape.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $diamonds,
        ], 200);
    }

    public function filterByShape(Request $request): JsonResponse
    {
        $request->validate([
            'shape_id' => 'required|integer|exists:diamond_shape,id'
        ]);

        $diamonds = DiamondMaster::with(['vendor', 'shape', 'color', 'clarity'])
            ->where('shape', $request->shape_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $diamonds
        ]);
    }

    public function getFrontShapes(Request $request): JsonResponse
    {
        try {
            $shapesFromDb = DiamondShape::where('display_in_front', 1)->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch diamond shapes: ' . $e->getMessage());
            $shapesFromDb = collect();
        }

        return response()->json([
            'success' => true,
            'data' => $shapesFromDb
        ]);
    }

    public function getFrontShapestest(Request $request): JsonResponse
    {
        try {
            // Fetch all active DB shapes with their aliases
            $shapesFromDb = DiamondShape::where('display_in_front', 1)->get();

            // Fetch shapes from the external API
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://www.onepricelab.com/api/search',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ]);
            $response = curl_exec($curl);
            curl_close($curl);

            $apiData = json_decode($response, true);
            $shapesFromApi = collect($apiData['data'])->pluck('shape')->unique();

            $resolvedShapes = [];

            foreach ($shapesFromApi as $apiShape) {
                $apiShapeUpper = strtoupper(trim($apiShape));
                foreach ($shapesFromDb as $dbShape) {
                    $dbNameUpper = strtoupper(trim($dbShape->name));

                    if ($apiShapeUpper === $dbNameUpper) {
                        $resolvedShapes[] = $dbShape->name;
                        break;
                    }

                    $aliases = array_map('trim', explode(',', $dbShape->ALIAS ?? ''));
                    foreach ($aliases as $alias) {
                        if (strtoupper($alias) === $apiShapeUpper) {
                            $resolvedShapes[] = $dbShape->name;
                            break 2;
                        }
                    }
                }
            }

            // Merge shapes from DB and Api, remove duplicates
            $finalShapes = $shapesFromDb->pluck('name')
                ->merge($resolvedShapes)
                ->unique()
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Diamond shapes fetched successfully.',
                'data' => $finalShapes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching diamond shapes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function styleShapeData(){
        $styles = ProductStyleCategory::select('psc_id', 'psc_name', 'psc_image')->where('engagement_menu', 1)->get();
        $shapes = DiamondShape::select('id', 'name', 'image')->get();

        return response()->json([
            'styles' => $styles,
            'shapes' => $shapes
        ]);
    }

}

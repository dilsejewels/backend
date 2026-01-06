<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\DiamondMaster;
use App\Http\Controllers\Controller;

class ColorController extends Controller
{
    public function filterDiamondsByColor($color_id): JsonResponse
    {

        if (!is_numeric($color_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid color ID.',
            ], 400);
        }

        $diamonds = DiamondMaster::where('color', $color_id)
            ->with(['vendor', 'shape', 'color', 'clarity'])
            ->get();

   
        if ($diamonds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No diamonds found for this color.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $diamonds,
        ], 200);
    }
}

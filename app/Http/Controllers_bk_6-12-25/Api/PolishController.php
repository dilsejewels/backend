<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\DiamondMaster;

class PolishController extends Controller
{
    public function filterDiamondsByPolish($polish_id): JsonResponse
    {
        if (!is_numeric($polish_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid polish ID.',
            ], 400);
        }

        $diamonds = DiamondMaster::where('polish', $polish_id)
            ->with(['vendor', 'shape', 'color', 'clarity','polish'])
            ->get();

   
        if ($diamonds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No diamonds found for this polish.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $diamonds,
        ], 200);
    
    }
}

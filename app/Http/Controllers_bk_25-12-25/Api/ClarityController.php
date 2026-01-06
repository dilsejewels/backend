<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiamondMaster;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClarityController extends Controller
{
    public function filterDiamondsByClarity($clarity_id): JsonResponse
    {
        // Validate if clarity_id exists in DiamondMaster and if it's valid
        if (!is_numeric($clarity_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid clarity ID.',
            ], 400);
        }

        $diamonds = DiamondMaster::where('clarity', $clarity_id)
            ->with(['vendor', 'shape', 'color', 'cut'])
            ->get();

        if ($diamonds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No diamonds found for this clarity.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $diamonds,
        ], 200);
    }
}

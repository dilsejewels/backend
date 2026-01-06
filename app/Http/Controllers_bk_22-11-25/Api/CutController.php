<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiamondMaster;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class CutController extends Controller
{
    public function filterDiamondsByCut($cut_id): JsonResponse
    {
        if (!is_numeric($cut_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid cut ID.',
            ], 400);
        }

        $diamonds = DiamondMaster::where('cut', $cut_id)
            ->with(['vendor', 'shape', 'color', 'clarity'])
            ->get();


        if ($diamonds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No diamonds found for this cut.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $diamonds,
        ], 200);
    }
}

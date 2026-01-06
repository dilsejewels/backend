<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LogoutController extends Controller
{
    /**
     * Revoke current access token or all tokens.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        // $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Handle user registration
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'title'    => 'nullable|string|max:10',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create user with default user_type = 'user'
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'user_type' => 'user', // hidden field default
        ]);

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data'    => $user,
        ], 201);
    }
}

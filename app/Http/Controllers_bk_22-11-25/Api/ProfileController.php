<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    // 🔹 1. Get current profile
    public function getProfile(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully',
            'data' => $user,
        ]);
    }

    // 🔹 2. Update profile (with optional image)
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Optional image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🔄 Update fields if provided
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('phone')) $user->phone = $request->phone;

        // 📷 Handle image upload
        if ($request->hasFile('image')) {
            if ($user->image && Storage::exists($user->image)) {
                Storage::delete($user->image); // Delete old image
            }

            $path = $request->file('image')->store('public/profile_images');
            $user->image = $path;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }
}

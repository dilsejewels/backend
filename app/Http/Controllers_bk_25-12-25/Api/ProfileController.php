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
        
        // Add full image URL to response
        if ($user->image) {
            $user->image_url = Storage::disk('public')->url($user->image);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile fetched successfully',
            'data' => $user,
        ]);
    }

    // 🔹 2. Update profile (SIMPLIFIED AND FIXED VERSION)
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'title' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'anniversary_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // 🔄 Update basic fields
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('title')) {
                $user->title = $request->title;
            }
            if ($request->has('dob')) {
                $user->dob = $request->dob;
            }
            if ($request->has('anniversary_date')) {
                $user->anniversary_date = $request->anniversary_date;
            }

            // 📷 Handle image upload - SIMPLIFIED APPROACH
            if ($request->hasFile('image')) {
                \Log::info('Image upload started for user: ' . $user->id);
                
                // Delete old image if exists
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                    \Log::info('Old image deleted: ' . $user->image);
                }
                
                // Store new image
                $image = $request->file('image');
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                
                // Store in public disk under profile_images directory
                $path = $image->storeAs('profile_images', $filename, 'public');
                
                \Log::info('Image stored at path: ' . $path);
                \Log::info('Full storage path: ' . storage_path('app/public/' . $path));
                
                // Update user image path
                $user->image = $path;
            }

            // Save user changes
            $user->save();
            
            // Add image URL to response
            if ($user->image) {
                $user->image_url = Storage::disk('public')->url($user->image);
                \Log::info('Image URL generated: ' . $user->image_url);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }
}
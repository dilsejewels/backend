<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (auth()->user()->user_type === 'admin') {

                // If request expects JSON (API)
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Login successful',
                        'user' => auth()->user()
                    ]);
                }

                // If it's a web request (browser)
                return redirect()->route('admin.dashboard');
            }

            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Not an admin user.'
                ], 401);
            }

            return back()->withErrors(['email' => 'Unauthorized']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
    public function profile()
    {
        return view('admin.auth.profile');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:800', // max 800KB
        ]);

        $user = auth()->user();

        // Delete old image
        if ($user->image && Storage::exists('public/profile/' . $user->image)) {
            Storage::delete('public/profile/' . $user->image);
        }

        $imageName = time() . '.' . $request->image->extension();
        $request->image->storeAs('public/profile', $imageName);

        $user->image = $imageName;
        $user->save();

        return response()->json(['success' => true]);
    }
    public function deleteImage()
    {
        $user = auth()->user();

        if ($user->image) {
            // Delete the image file from storage
            $imagePath = public_path('storage/profile/' . $user->image);

            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the file
            }

            // Update the user's image in the database to null
            $user->image = null;
            $user->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'No image found']);
    }

    public function changePassword()
    {
        return view('admin.auth.changePassword');
    }

    

}


<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'birth_date' => 'nullable|date',
            'anniversary_date' => 'nullable|date',
        
        ]);
 
        $user = User::create([
            'title'     => $request->title,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'dob' => $request->birth_date,
            'anniversary_date' => $request->anniversary_date,
            'user_type' => 'user',
        ]);
        $user->sendEmailVerificationNotification();
 
        $token = $user->createToken('auth_token')->plainTextToken;
 
        return response()->json([
            'success'      => true,
            'message'      => 'Registration successful.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user_type'    => $user->user_type,
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            Auth::logout(); // log the user out immediately
            return response()->json(['message' => 'You need to verify your email before logging in.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'new_password_confirmation' => 'required'
        ]);

        $errors = [];

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
        } else {
            // Only check this if current_password is provided and valid per validator
            if (!Hash::check($request->current_password, $request->user()->password)) {
                $errors['current_password'][] = 'Current password is incorrect.';
            }

            // Check new password confirmation
            if ($request->new_password !== $request->new_password_confirmation) {
                $errors['new_password'][] = 'The new password confirmation does not match.';
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        // Update password
        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password Changed successfully.'
        ]);

    }
    
    //Forget Password
    public function forgetPasswordView()
    {
        return view('admin.auth.forgetPassword');
    }
    
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // For API/React
        if ($request->expectsJson()) {
            
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => __($status),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 422);
        }

        // For Laravel Blade
        return back()->with('status', trans($status));
    }
    
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successful.']);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');

        if ($request->expectsJson()) {
            // React / API
            return response()->json([
                'token' => $token,
                'email' => $email,
            ]);
        }

        // Blade / Web
        return view('admin.auth.forgetPasswordReset', [
            'token' => $token,
            'email' => $email,
        ]);
    }


    public function forgetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => trans($status)]);
        }

        return response()->json(['message' => trans($status)], 500);
    }

    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();

    //     return response()->json([
    //         'message' => 'Logged out successfully'
    //     ]);
    // }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        // Auth::guard('web')->logout();

        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                ]
            );

            // Create token (if using Laravel Passport or Sanctum)
            $token = $user->createToken('google-login')->accessToken;

            // 🔁 Redirect to your frontend app with the token
            return redirect("https://dilsejewels.com/google/callback?token={$token}");
        } catch (\Exception $e) {
            return redirect("https://dilsejewels.com/signin?error=google_login_failed");
        }
    }
} 
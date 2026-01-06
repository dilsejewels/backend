<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->away(config('app.frontend_url') . '/signin?verified=1');
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return redirect()->away(config('app.frontend_url') . '/signin?verified=1');
    }
}

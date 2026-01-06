<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewShareController extends Controller
{
    public function share(Request $request, $reviewId)
    {
        $review = Review::findOrFail($reviewId);

        $validated = $request->validate([
            'platform' => 'nullable|string|max:50',
            'user_name' => 'nullable|string|max:100'
        ]);

        $userName = $validated['user_name'] ?? null;
        $platform = $validated['platform'] ?? null;

        $guestIdentifier = $this->getGuestIdentifier($request);
        $userId = Auth::id();

        $existingShare = ReviewShare::where('review_id', $reviewId)
            ->where(function ($query) use ($userId, $guestIdentifier) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('guest_identifier', $guestIdentifier);
                }
            })
            ->first();

        if ($existingShare) {
            return response()->json([
                'success' => false,
                'message' => 'You have already shared this review'
            ], 422);
        }

        ReviewShare::create([
            'review_id' => $reviewId,
            'user_id' => $userId,
            'guest_identifier' => $guestIdentifier,
            'user_name' => $userName,
            'platform' => $platform
        ]);

        $review->increment('shares_count');

        return response()->json([
            'success' => true,
            'message' => 'Review shared successfully',
            'shares_count' => $review->fresh()->shares_count
        ]);
    }

    public function getShareStats($reviewId)
    {
        $review = Review::findOrFail($reviewId);

        return response()->json([
            'success' => true,
            'data' => [
                'shares_count' => $review->shares_count
            ]
        ]);
    }

    private function getGuestIdentifier($request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        return md5($ip . $userAgent . $request->header('User-Agent', ''));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewLikeController extends Controller
{
    public function toggleLike(Request $request, $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        $userId = Auth::id();
        $isLike = filter_var($request->is_like, FILTER_VALIDATE_BOOLEAN);

        $existingLike = ReviewLike::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            if ($existingLike->is_like == $isLike) {
                // Remove like/dislike if same action
                $existingLike->delete();
                
                // Update counts
                if ($isLike) {
                    $review->decrement('likes_count');
                } else {
                    $review->decrement('dislikes_count');
                }

                return response()->json([
                    'success' => true,
                    'message' => $isLike ? 'Like removed' : 'Dislike removed',
                    'action' => 'removed'
                ]);
            } else {
                // Change from like to dislike or vice versa
                $existingLike->update(['is_like' => $isLike]);
                
                // Update counts
                if ($isLike) {
                    $review->increment('likes_count');
                    $review->decrement('dislikes_count');
                } else {
                    $review->decrement('likes_count');
                    $review->increment('dislikes_count');
                }

                return response()->json([
                    'success' => true,
                    'message' => $isLike ? 'Changed to like' : 'Changed to dislike',
                    'action' => 'changed'
                ]);
            }
        } else {
            // Create new like/dislike
            ReviewLike::create([
                'review_id' => $reviewId,
                'user_id' => $userId,
                'is_like' => $isLike
            ]);

            // Update counts
            if ($isLike) {
                $review->increment('likes_count');
            } else {
                $review->increment('dislikes_count');
            }

            return response()->json([
                'success' => true,
                'message' => $isLike ? 'Liked successfully' : 'Disliked successfully',
                'action' => 'added'
            ]);
        }
    }

    public function getUserReaction($reviewId)
    {
        $reaction = ReviewLike::where('review_id', $reviewId)
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'success' => true,
            'data' => $reaction ? [
                'is_like' => $reaction->is_like
            ] : null
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Get all reviews (optionally by product)
     */
     public function index(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer'
            ]);

            $productId = $request->input('product_id');
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $query = Review::with(['replies'])
                ->withCount(['likes as likes_count', 'dislikes as dislikes_count', 'replies as replies_count'])
                ->where('product_id', $productId)
                ->whereNull('parent_id');

            // Sorting
            $sort = $request->get('sort', 'latest');
            switch ($sort) {
                case 'highest_rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'lowest_rating':
                    $query->orderBy('rating', 'asc');
                    break;
                case 'most_liked':
                    $query->orderBy('likes_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $reviews = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviews'
            ], 500);
        }
    }

    /**
     * Store a new review or reply
     */
    public function store(Request $request)
    {
        Log::info('Review store request:', $request->all());

        try {
            $validated = $request->validate([
                'product_id' => 'required|integer',
                'parent_id' => 'nullable|exists:reviews,id',
                'rating' => 'nullable|integer|between:1,5',
                'comment' => 'required|string|min:1|max:1000',
                'user_name' => 'nullable|string|max:100',
                'user_id' => 'nullable|integer'
            ]);

            // Replies cannot have ratings
            if ($request->parent_id && $request->rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Replies cannot have ratings.'
                ], 422);
            }

            // Reviews (not replies) must have rating
            if (!$request->parent_id && !$request->rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating is required for reviews.'
                ], 422);
            }

            // Determine user info
            $userId = $validated['user_id'] ?? null;
            $userName = $validated['user_name'] ?? 'Guest User';

            $review = Review::create([
                'user_id' => $userId,
                'user_name' => $userName,
                'product_id' => $validated['product_id'],
                'parent_id' => $validated['parent_id'] ?? null,
                'rating' => $validated['rating'] ?? null,
                'comment' => $validated['comment'],
                'likes_count' => 0,
                'dislikes_count' => 0,
                'replies_count' => 0,
                'shares_count' => 0
            ]);

            // Increase replies count if needed
            if ($request->parent_id) {
                Review::where('id', $request->parent_id)->increment('replies_count');
            }

            // Load relationships
            $review->load(['replies']);
            $review->loadCount(['likes as likes_count', 'dislikes as dislikes_count']);

            return response()->json([
                'success' => true,
                'message' => $request->parent_id
                    ? 'Reply posted successfully'
                    : 'Review submitted successfully',
                'data' => $review
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Review creation error:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $review = Review::with(['replies'])
            ->withCount(['likes as likes_count', 'dislikes as dislikes_count'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'rating' => 'nullable|integer|between:1,5',
            'comment' => 'required|string|min:1|max:1000',
            'user_name' => 'nullable|string|max:100',
        ]);

        if ($review->parent_id && $request->has('rating')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update rating for replies.'
            ], 422);
        }

        $review->update([
            'rating' => $validated['rating'] ?? $review->rating,
            'comment' => $validated['comment'],
            'user_name' => $validated['user_name'] ?? $review->user_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'data' => $review
        ]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        if ($review->parent_id) {
            Review::where('id', $review->parent_id)->decrement('replies_count');
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.'
        ]);
    }

    public function getReplies($reviewId)
    {
        if (!is_numeric($reviewId)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid review ID.'
            ], 422);
        }

        $replies = Review::withCount(['likes as likes_count', 'dislikes as dislikes_count'])
            ->where('parent_id', $reviewId)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $replies
        ]);
    }

    public function getUserReviews(Request $request)
    {
        $name = $request->get('user_name');

        if (!$name) {
            return response()->json([
                'success' => false,
                'message' => 'user_name is required.'
            ], 422);
        }

        $reviews = Review::with('product')
            ->withCount(['likes as likes_count', 'dislikes as dislikes_count'])
            ->where('user_name', $name)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Get review statistics for a product
     */
     public function getReviewStats(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer'
            ]);

            $productId = $request->input('product_id');

            $reviews = Review::where('product_id', $productId)
                ->whereNull('parent_id')
                ->get();

            $totalReviews = $reviews->count();

            if ($totalReviews === 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'average_rating' => 0,
                        'total_reviews' => 0,
                        'rating_distribution' => [
                            '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0
                        ]
                    ]
                ]);
            }

            $totalRating = $reviews->sum('rating');
            $averageRating = round($totalRating / $totalReviews, 1);

            $ratingDistribution = [
                '5' => $reviews->where('rating', 5)->count(),
                '4' => $reviews->where('rating', 4)->count(),
                '3' => $reviews->where('rating', 3)->count(),
                '2' => $reviews->where('rating', 2)->count(),
                '1' => $reviews->where('rating', 1)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'average_rating' => $averageRating,
                    'total_reviews' => $totalReviews,
                    'rating_distribution' => $ratingDistribution
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching review stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch review statistics'
            ], 500);
        }
    }
    /**
     * Alternative method if you want to include additional stats
     */
    public function getReviewStatsWithDetails(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,products_id'
            ]);

            $productId = $request->input('product_id');

            $reviews = Review::where('product_id', $productId)
                ->whereNull('parent_id')
                ->where('status', 'approved')
                ->get();

            $totalReviews = $reviews->count();

            if ($totalReviews === 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'average_rating' => 0,
                        'total_reviews' => 0,
                        'rating_distribution' => [
                            '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0
                        ],
                        'total_likes' => 0,
                        'total_dislikes' => 0,
                        'total_shares' => 0,
                        'total_replies' => 0
                    ]
                ]);
            }

            $totalRating = $reviews->sum('rating');
            $averageRating = round($totalRating / $totalReviews, 1);

            $ratingDistribution = [
                '5' => $reviews->where('rating', 5)->count(),
                '4' => $reviews->where('rating', 4)->count(),
                '3' => $reviews->where('rating', 3)->count(),
                '2' => $reviews->where('rating', 2)->count(),
                '1' => $reviews->where('rating', 1)->count(),
            ];

            // Additional statistics
            $totalLikes = $reviews->sum('likes_count');
            $totalDislikes = $reviews->sum('dislikes_count');
            $totalShares = $reviews->sum('shares_count');
            
            // Count total replies for all reviews
            $totalReplies = Review::where('product_id', $productId)
                ->whereNotNull('parent_id')
                ->where('status', 'approved')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'average_rating' => $averageRating,
                    'total_reviews' => $totalReviews,
                    'rating_distribution' => $ratingDistribution,
                    'total_likes' => $totalLikes,
                    'total_dislikes' => $totalDislikes,
                    'total_shares' => $totalShares,
                    'total_replies' => $totalReplies
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching detailed review stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch review statistics'
            ], 500);
        }
    }

    /**
     * Get review stats for multiple products at once
     */
    public function getBulkReviewStats(Request $request)
    {
        try {
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'integer|exists:products,products_id'
            ]);

            $productIds = $request->input('product_ids');
            $stats = [];

            foreach ($productIds as $productId) {
                $reviews = Review::where('product_id', $productId)
                    ->whereNull('parent_id')
                    ->where('status', 'approved')
                    ->get();

                $totalReviews = $reviews->count();

                if ($totalReviews > 0) {
                    $totalRating = $reviews->sum('rating');
                    $averageRating = round($totalRating / $totalReviews, 1);
                } else {
                    $averageRating = 0;
                }

                $stats[$productId] = [
                    'average_rating' => $averageRating,
                    'total_reviews' => $totalReviews,
                    'rating_distribution' => [
                        '5' => $reviews->where('rating', 5)->count(),
                        '4' => $reviews->where('rating', 4)->count(),
                        '3' => $reviews->where('rating', 3)->count(),
                        '2' => $reviews->where('rating', 2)->count(),
                        '1' => $reviews->where('rating', 1)->count(),
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching bulk review stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bulk review statistics'
            ], 500);
        }
    }
}
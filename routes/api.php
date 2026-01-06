<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CutController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ShapeController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\PolishController;
use App\Http\Controllers\Api\ClarityController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CartAddressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VerifyEmailController;
use App\Http\Controllers\DiamondMaster\DiamondMasterController;
use App\Http\Controllers\Api\PayPalController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ReviewLikeController;
use App\Http\Controllers\Api\ReviewShareController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\PaymentController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// --- Payment Routes ---

// Razorpay
Route::post('/razorpay/create', [PaymentController::class, 'createRazorpayOrder']);
// Route::post('/razorpay/verify', [PaymentController::class, 'verifyRazorpayPayment']);
Route::middleware('auth:sanctum')->post('/razorpay/verify', [PaymentController::class, 'verifyRazorpayPayment']);

// PayPal
Route::post('/paypal/create', [PaymentController::class, 'createPaypalOrder']);
Route::middleware('auth:sanctum')->post('/paypal/capture', [PaymentController::class, 'capturePaypalOrder']);

// CashFree
Route::post('/cashfree/create', [PaymentController::class, 'createCashfreeOrder']);
Route::middleware('auth:sanctum')->post('/cashfree/verify', [PaymentController::class, 'verifyCashfreePayment']);
// COD Route
Route::middleware('auth:sanctum')->post('/cod/create', [PaymentController::class, 'createCodOrder']);

Route::post('/book-appointment', [BookingController::class, 'store']);
Route::post('/enquiries', [EnquiryController::class, 'store']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/best-selling-rings/{categoryType}', [HomeController::class, 'getBestSellingRings']);
Route::get('/get-all-products', [ProductController::class, 'jewelryData']);
Route::get('/get-all-engagementData/{slug?}', [ProductController::class, 'engagementData']);
Route::get('/get-all-wedding-data/{slug?}', [ProductController::class, 'weddingData']);
Route::get('/get-all-gift-data/{slug?}', [ProductController::class, 'giftData']);
Route::get('/get-all-sale-data/{slug?}', [ProductController::class, 'saleData']);
Route::get('/get-all-collection-data/{slug?}', [ProductController::class, 'collectionData']);
Route::get('/jewelry', [CategoryController::class, 'jewelryData']);

Route::get('/categories', [CategoryController::class, 'typeData']);
Route::get('/engagement', [ShapeController::class, 'engagementData']);
Route::get('/product-details/{productId}', [ProductController::class, 'showProductById']);
Route::get('/get-all-styleShapeData', [ShapeController::class, 'styleShapeData']);
Route::get('/product-by-id/{id}', [ProductController::class, 'showById']);
Route::get('/engagement-buildproduct/{productId}', [ProductController::class, 'showBuildProductById']);
Route::get('/jewelry-product/{id}', [ProductController::class, 'showRegularProductById']);

Route::get('/get-all-diamonds', [DiamondMasterController::class, 'data']);
Route::post('/contact', [ContactController::class, 'submit']);
//shape
Route::get('/diamond-shapes', [ShapeController::class, 'getFrontShapes']);
Route::get('diamonds/by-shape/{shape_id}', [ShapeController::class, 'filterDiamondsByShape']);
//color
Route::get('/diamond-colors', [ShapeController::class, 'getFrontColors']);
Route::get('diamonds/by-color/{color_id}', [ColorController::class, 'filterDiamondsByColor']);
//Cut
Route::get('diamonds/by-cut/{cut_id}', [CutController::class, 'filterDiamondsByCut']);
//clarity
Route::get('diamonds/by-clarity/{clarity_id}', [ClarityController::class, 'filterDiamondsByClarity']);

Route::get('diamonds/by-polish/{polish_id}', [PolishController::class, 'filterDiamondsByPolish']);

// Route::post('/register', [RegisterController::class, 'register']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->name('verification.verify');


// Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Route::middleware('auth:sanctum')->get('/logout', [LogoutController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::post('/password/email', [AuthController::class, 'sendResetLink']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset']);

Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'forgetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/reset-password-auth', [AuthController::class, 'resetPassword']);
    
    // Address routes
    Route::get('/address', [AddressController::class, 'getAddress']);
    Route::get('/addresses', [AddressController::class, 'getUserAddresses']);
    Route::post('/address', [AddressController::class, 'store']);
    Route::delete('/address/{id}', [AddressController::class, 'destroy']);

    // Order routes with cancellation
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/get-orders', [OrderController::class, 'index']);
    Route::post('/cancel-order/{id}', [OrderController::class, 'cancel']);
    Route::get('/order-cancellation-info/{id}', [OrderController::class, 'getCancellationInfo']);
    
    // Admin order management routes (optional - for testing)
    Route::post('/mark-delivered/{id}', [OrderController::class, 'markDelivered']);
    Route::post('/mark-shipped/{id}', [OrderController::class, 'markShipped']);
});
Route::post('/store-order', [OrderController::class, 'store']);
Route::post('/apply-discount', [CouponController::class, 'applyCoupon']);
Route::post('apply-coupon', [CouponController::class, 'applyCoupon']);

Route::post('/appointments', [AppointmentController::class, 'storeAppointment']);

Route::get('/blogs/{slug}', [BlogController::class, 'show']);
Route::get('/blogs', [BlogController::class, 'getBlogs']);

Route::get('/reviews/stats', [ReviewController::class, 'getReviewStats']);
Route::get('/reviews/stats/detailed', [ReviewController::class, 'getReviewStatsWithDetails']);
Route::get('/reviews/stats/bulk', [ReviewController::class, 'getBulkReviewStats']);

Route::apiResource('reviews', ReviewController::class);
Route::get('/user/reviews', [ReviewController::class, 'getUserReviews']);
Route::get('/reviews/{reviewId}/replies', [ReviewController::class, 'getReplies']);
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
Route::post('/reviews/{reviewId}/like', [ReviewLikeController::class, 'toggleLike']);
Route::get('/reviews/{reviewId}/reaction', [ReviewLikeController::class, 'getUserReaction']);
Route::post('/reviews/{reviewId}/share', [ReviewShareController::class, 'share']);
Route::get('/reviews/{reviewId}/share-stats', [ReviewShareController::class, 'getShareStats']);

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function applyCoupon(Request $request)
{
    $request->validate([
        'code' => 'required|string',
        'cart_total' => 'required|numeric|min:0'
    ]);

    $coupon = Coupon::where('code', $request->code)
        ->where('is_active', 1)
        ->whereDate('valid_from', '<=', now())
        ->whereDate('valid_until', '>=', now())
        ->first();

    if (!$coupon) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired coupon.'
        ], 400);
    }

    // Cart minimum check
    if ($coupon->min_cart_value && $request->cart_total < $coupon->min_cart_value) {
        return response()->json([
            'success' => false,
            'message' => "Cart total must be at least ₹{$coupon->min_cart_value}."
        ], 400);
    }

    if ($coupon->usage_limit <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Coupon usage limit exceeded.'
        ], 400);
    }

    $discount = 0;
    if ($coupon->type === 'fixed') {
        $discount = $coupon->value;
    } elseif ($coupon->type === 'percent') {
        $discount = ($request->cart_total * $coupon->value) / 100;

        // Max discount check
        if ($coupon->max_discount && $discount > $coupon->max_discount) {
            $discount = $coupon->max_discount;
        }
    }

    $finalAmount = max(0, $request->cart_total - $discount);

    return response()->json([
        'success' => true,
        'message' => 'Coupon applied successfully.',
        'data' => [
            'coupon_code' => $coupon->code,
            'discount' => $discount,
            'final_amount' => $finalAmount
        ]
    ]);
}
}

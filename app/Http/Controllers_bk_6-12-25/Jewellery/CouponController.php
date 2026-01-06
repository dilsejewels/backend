<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();
        return view('admin.Jewellery.coupons.index', compact('coupons'));
    }

    public function store(Request $request) 
    {
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_cart_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'usage_limit' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        Coupon::create($request->all());

        return response()->json(['success' => 'Coupon created successfully.']);
    }

    public function edit(Coupon $coupon)
    {
        return response()->json(['coupon' => $coupon]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_cart_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'usage_limit' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $coupon->update($request->all());

        return response()->json(['success' => 'Coupon updated successfully.']);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['success' => 'Coupon deleted successfully.']);
    }

    public function updateStatus(Request $request, Coupon $coupon)
    {
        $coupon->is_active = $request->status;
        $coupon->save();

        return response()->json(['message' => 'Coupon status updated successfully.']);
    }
}
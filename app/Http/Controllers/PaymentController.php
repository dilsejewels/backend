<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Order; // Import your Order Model
use Exception;

class PaymentController extends Controller
{
    // ==========================================
    // 1. RAZORPAY: Create Order (Step 1)
    // ==========================================
    public function createRazorpayOrder(Request $request)
    {
        // 1. Initialize Razorpay
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        // 2. Create the order on Razorpay Server
        // Amount must be in "Paise" (100 Paise = 1 Rupee)
        $orderData = [
            'receipt'         => 'rcpt_' . time(),
            'amount'          => $request->total_amount * 100, 
            'currency'        => 'INR',
            'payment_capture' => 1 
        ];

        try {
            $razorpayOrder = $api->order->create($orderData);
            
            // 3. Send the Order ID and Key back to React
            return response()->json([
                'order_id' => $razorpayOrder['id'],
                'amount' => $orderData['amount'],
                'key' => config('services.razorpay.key')
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 2. RAZORPAY: Verify & Save to DB (Step 2)
    // ==========================================

    public function verifyRazorpayPayment(Request $request)
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            // -------------------------------------------
            // 1. Verify Razorpay Signature (Security Check)
            // -------------------------------------------
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'  => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature
            ]);

            // -------------------------------------------
            // 2. Extract Full Payload
            // -------------------------------------------
            $payload = $request->items ?? [];

            $itemsId = [
                'diamond' => [],
                'gift'    => [],
                'build'   => [],
                'combo'   => [],
            ];

            foreach ($payload as $item) {

                $type = $item['productType'] ?? null;

                switch ($type) {

                    // ---------------------- DIAMOND ----------------------
                    case 'diamond':
                        if (!empty($item['diamondid'])) {
                            $itemsId['diamond'][] = $item['diamondid'];
                        }
                        break;

                    // ---------------------- JEWELLERY / GIFT ----------------------
                    case 'gift':
                        if (!empty($item['product_id'])) {
                            $itemsId['gift'][] = $item['product_id'];
                        }
                        break;

                    // ---------------------- BUILD A RING ----------------------
                    case 'build':
                        if (!empty($item['product_id'])) {
                            $itemsId['build'][] = [
                                'product_id' => $item['product_id'],
                                'size' => $item['size'] ?? null,
                            ];
                        }
                        break;

                    // ---------------------- COMBO ----------------------
                    case 'combo':
                        $itemsId['combo'][] = [
                            'diamond_id' => $item['diamond']['diamondid'] ?? null,
                            'product_id' => $item['ring']['id'] ?? null,
                            'size'       => $item['size'] ?? null,
                        ];
                        break;
                }
            }

            // -------------------------------------------
            // 4. Decide product type
            // -------------------------------------------
            $nonEmptyTypes = collect($itemsId)
                ->filter(fn($id) => !empty($id))
                ->keys();

            if ($nonEmptyTypes->isEmpty()) {
                $productType = 'empty';
            } elseif ($nonEmptyTypes->count() === 1) {
                $productType = $nonEmptyTypes->first();         // diamond, gift, build, combo
            } else {
                $productType = 'multiple';
            }

            $billing = $request->billing_address;
            $shipping = $request->shipping_address;

            // Determine name
            $userName = trim(
                ($billing['first_name'] ?? $shipping['first_name'] ?? '') . ' ' .
                ($billing['last_name'] ?? $shipping['last_name'] ?? '')
            );

            $contactNumber = $shipping['phone'] 
                ?? $billing['phone'] 
                ?? null;
            // -------------------------------------------
            // 5. Save Order in DB
            // -------------------------------------------
            $order = Order::create([
                'order_id'         => 'ORD-' . Str::uuid(),
                'user_id'          => Auth::id(), 
                'user_name'        => $userName, 
                'item_details'     => json_encode($payload, JSON_UNESCAPED_SLASHES),  
                'items_id'         => json_encode($itemsId, JSON_UNESCAPED_SLASHES),

                'product_type'     => $productType,
                'contact_number'   => $contactNumber,
                'address'          => json_encode($request->shipping_address, JSON_UNESCAPED_SLASHES),
                'billing_address'  => json_encode($request->billing_address, JSON_UNESCAPED_SLASHES),
                'total_price'      => $request->total_amount,
                'payment_mode'     => 'razorpay',
                'payment_method'   => 'razorpay',
                'payment_status'   => 'paid',
                'coupon_code' => $request->coupon_code,
                'coupon_discount' => $request->discount,
                'transaction_id'   => $request->razorpay_payment_id,
                'razorpay_payment_id'   => $request->razorpay_payment_id,
                'razorpay_order_id' => $request->razorpay_order_id,
            ]);

            // -------------------------------------------
            // 6. Return Success Response
            // -------------------------------------------
            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified & order saved',
                'order_id' => $order->order_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ==========================================
    // 3. PAYPAL: Create Order (Step 1)
    // ==========================================
    public function createPaypalOrder(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                // Where to send the user after they click "Pay" on PayPal
                // This URL leads to your React "Success" page
                "return_url" => env('FRONTEND_URL', 'http://localhost:5173') . "/payment/success", 
                "cancel_url" => env('FRONTEND_URL', 'http://localhost:5173') . "/checkout",
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->total_amount
                    ]
                ]
            ]
        ]);

        // Find the "approve" link to send to React
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json([
                        'approval_url' => $link['href'], 
                        'order_id' => $response['id']
                    ]);
                }
            }
        }

        return response()->json(['error' => 'Something went wrong with PayPal'], 500);
    }

    // ==========================================
    // 4. PAYPAL: Capture & Save to DB (Step 2)
    // ==========================================

    public function capturePaypalOrder(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // 1. Capture the payment (Take the money)
        $response = $provider->capturePaymentOrder($request->token);

        // 2. Check if successful
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {

            // 3. Prepare item IDs like Razorpay (optional, but useful)
            $payload = $request->items ?? [];

            $itemsId = [
                'diamond' => [],
                'gift'    => [],
                'build'   => [],
                'combo'   => [],
            ];

            foreach ($payload as $item) {
                $type = $item['productType'] ?? null;
                switch ($type) {
                    case 'diamond':
                        if (!empty($item['diamondid'])) {
                            $itemsId['diamond'][] = $item['diamondid'];
                        }
                        break;
                    case 'gift':
                        if (!empty($item['product_id'])) {
                            $itemsId['gift'][] = $item['product_id'];
                        }
                        break;
                    case 'build':
                        if (!empty($item['product_id'])) {
                            $itemsId['build'][] = [
                                'product_id' => $item['product_id'],
                                'size'       => $item['size'] ?? null,
                            ];
                        }
                        break;
                    case 'combo':
                        $itemsId['combo'][] = [
                            'diamond_id' => $item['diamond']['diamondid'] ?? null,
                            'product_id' => $item['ring']['id'] ?? null,
                            'size'       => $item['size'] ?? null,
                        ];
                        break;
                }
            }

            // Determine product type
            $nonEmptyTypes = collect($itemsId)
                ->filter(fn($id) => !empty($id))
                ->keys();

            if ($nonEmptyTypes->isEmpty()) {
                $productType = 'empty';
            } elseif ($nonEmptyTypes->count() === 1) {
                $productType = $nonEmptyTypes->first(); // diamond, gift, build, combo
            } else {
                $productType = 'multiple';
            }

            // Determine user name
            $billing = $request->billing_address ?? [];
            $shipping = $request->shipping_address ?? [];

            $userName = trim(
                ($billing['first_name'] ?? $shipping['first_name'] ?? '') . ' ' .
                ($billing['last_name'] ?? $shipping['last_name'] ?? '')
            );

            $contactNumber = $shipping['phone'] ?? $billing['phone'] ?? null;

            // 4. Save Order in DB with JSON encoding
            $order = Order::create([
                'order_id'         => 'ORD-' . Str::uuid(),
                'user_id'          => $request->user()->id ?? null,
                'user_name'        => $userName,
                'item_details'     => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'items_id'         => json_encode($itemsId, JSON_UNESCAPED_SLASHES),
                'product_type'     => $productType,
                'contact_number'   => $contactNumber,
                'address'          => json_encode($shipping, JSON_UNESCAPED_SLASHES),
                'billing_address'  => json_encode($billing, JSON_UNESCAPED_SLASHES),
                'total_price'      => $request->total_amount,
                'payment_mode'     => 'paypal',
                'payment_method'   => 'paypal',
                'payment_status'   => 'paid',
                'coupon_code' => $request->coupon_code,
                'coupon_discount' => $request->discount,
                'transaction_id'   => $response['id'],
                'paypal_order_id' => $request->token,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified & order saved',
                'order_id' => $order->order_id
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Payment failed'], 400);
    }

    // ==========================================
    // 5. CASHFREE: Create Order (Step 1)
    // ==========================================

    public function createCashfreeOrder(Request $request)
    {
        $client = new Client();
        $baseUrl = env('CASHFREE_MODE') === 'sandbox' 
            ? 'https://sandbox.cashfree.com/pg' 
            : 'https://api.cashfree.com/pg';

        $orderId = 'order_' . time(); 
        $returnUrl = env('FRONTEND_URL', 'http://localhost:5173') . "/payment/success?cf_order_id={order_id}";

        // FIX: Force amount to float to prevent "Invalid Amount" error
        $amount = (float) $request->total_amount;

        try {
            $response = $client->request('POST', "$baseUrl/orders", [
                'headers' => [
                    'x-client-id' => env('CASHFREE_APP_ID'),
                    'x-client-secret' => env('CASHFREE_SECRET_KEY'),
                    'x-api-version' => env('CASHFREE_API_VERSION', '2023-08-01'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'order_id' => $orderId,
                    'order_amount' => $amount, // Use the float amount
                    'order_currency' => 'INR',
                    'customer_details' => [
                        'customer_id' => (string) ($request->user()->id ?? 'guest_' . time()),
                        'customer_email' => $request->user()->email ?? 'guest@example.com',
                        'customer_phone' => '9999999999',
                    ],
                    'order_meta' => [
                        'return_url' => $returnUrl
                    ]
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            return response()->json([
                'payment_session_id' => $body['payment_session_id'],
                'order_id' => $orderId
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 6. CASHFREE: Verify & Save (Step 2)
    // ==========================================

    public function verifyCashfreePayment(Request $request)
    {
        $client = new Client();
        $baseUrl = env('CASHFREE_MODE') === 'sandbox' 
            ? 'https://sandbox.cashfree.com/pg' 
            : 'https://api.cashfree.com/pg';

        $orderId = $request->order_id;

        try {
            // 1. Verify Status
            $response = $client->request('GET', "$baseUrl/orders/$orderId", [
                'headers' => [
                    'x-client-id' => env('CASHFREE_APP_ID'),
                    'x-client-secret' => env('CASHFREE_SECRET_KEY'),
                    'x-api-version' => env('CASHFREE_API_VERSION', '2023-08-01'),
                    'Accept' => 'application/json',
                ]
            ]);

            $orderData = json_decode($response->getBody(), true);

            if ($orderData['order_status'] === 'PAID') {
                
                // 2. LOGIC TO EXTRACT ITEM IDs (Fixed for Key Mismatches)
                $payload = $request->items ?? [];

                $itemsId = [
                    'diamond' => [],
                    'gift'    => [],
                    'build'   => [],
                    'combo'   => [],
                ];

                foreach ($payload as $item) {
                    $type = $item['productType'] ?? null;
                    switch ($type) {
                        case 'diamond':
                            // FIX: Check 'diamondid' OR 'id' OR 'product_id'
                            $dId = $item['diamondid'] ?? $item['id'] ?? $item['product_id'] ?? null;
                            if ($dId) {
                                $itemsId['diamond'][] = $dId;
                            }
                            break;
                        case 'gift':
                            // FIX: Check 'product_id' OR 'id'
                            $gId = $item['product_id'] ?? $item['id'] ?? null;
                            if ($gId) {
                                $itemsId['gift'][] = $gId;
                            }
                            break;
                        case 'build':
                            if (!empty($item['product_id'])) {
                                $itemsId['build'][] = [
                                    'product_id' => $item['product_id'],
                                    'size'       => $item['size'] ?? null,
                                ];
                            }
                            break;
                        case 'combo':
                            $itemsId['combo'][] = [
                                'diamond_id' => $item['diamond']['diamondid'] ?? $item['diamond']['id'] ?? null,
                                'product_id' => $item['ring']['id'] ?? null,
                                'size'       => $item['size'] ?? null,
                            ];
                            break;
                    }
                }

                // 3. Determine Product Type
                $nonEmptyTypes = collect($itemsId)->filter(fn($id) => !empty($id))->keys();
                if ($nonEmptyTypes->isEmpty()) {
                    $productType = 'empty';
                } elseif ($nonEmptyTypes->count() === 1) {
                    $productType = $nonEmptyTypes->first();
                } else {
                    $productType = 'multiple';
                }

                // 4. User Info
                $billing = $request->billing_address ?? [];
                $shipping = $request->shipping_address ?? [];
                $userName = trim(($billing['first_name'] ?? $shipping['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? $shipping['last_name'] ?? ''));
                $contactNumber = $shipping['phone'] ?? $billing['phone'] ?? null;

                // 5. SAVE TO DB
                $order = Order::create([
                    'order_id'         => 'ORD-' . Str::uuid(),
                    'user_id'          => $request->user()->id ?? null,
                    'user_name'        => $userName,
                    
                    // Save the calculated arrays
                    'item_details'     => json_encode($payload, JSON_UNESCAPED_SLASHES),
                    'items_id'         => json_encode($itemsId, JSON_UNESCAPED_SLASHES),
                    'product_type'     => $productType,
                    
                    'contact_number'   => $contactNumber,
                    'address'          => json_encode($shipping, JSON_UNESCAPED_SLASHES),
                    'billing_address'  => json_encode($billing, JSON_UNESCAPED_SLASHES),
                    'total_price'      => $request->total_amount,
                    'payment_mode'     => 'cashfree',
                    'payment_method'   => 'cashfree',
                    'payment_status'   => 'paid',
                    'coupon_code'      => $request->coupon_code,
                    'coupon_discount'  => $request->discount,
                    'transaction_id'   => $orderId,
                ]);

                // 6. Clear Cart
                /* if ($request->user()) {
                    \App\Models\Cart::where('user_id', $request->user()->id)->delete();
                } */

                return response()->json(['status' => 'success', 'message' => 'Order Placed']);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Payment status: ' . $orderData['order_status']]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 7. COD: Create Order
    // ==========================================
    public function createCodOrder(Request $request)
    {
        try {
            // 1. PREPARE DATA (Same logic as PayPal/Cashfree)
            $payload = $request->items ?? [];

            // Calculate Items ID
            $itemsId = [ 'diamond' => [], 'gift' => [], 'build' => [], 'combo' => [] ];
            
            foreach ($payload as $item) {
                $type = $item['productType'] ?? null;
                switch ($type) {
                    case 'diamond':
                        $dId = $item['diamondid'] ?? $item['id'] ?? $item['product_id'] ?? null;
                        if ($dId) $itemsId['diamond'][] = $dId;
                        break;
                    case 'gift':
                        $gId = $item['product_id'] ?? $item['id'] ?? null;
                        if ($gId) $itemsId['gift'][] = $gId;
                        break;
                    case 'build':
                        if (!empty($item['product_id'])) {
                            $itemsId['build'][] = [
                                'product_id' => $item['product_id'],
                                'size'       => $item['size'] ?? null,
                            ];
                        }
                        break;
                    case 'combo':
                        $itemsId['combo'][] = [
                            'diamond_id' => $item['diamond']['diamondid'] ?? $item['diamond']['id'] ?? null,
                            'product_id' => $item['ring']['id'] ?? null,
                            'size'       => $item['size'] ?? null,
                        ];
                        break;
                }
            }

            // Determine Product Type
            $nonEmptyTypes = collect($itemsId)->filter(fn($id) => !empty($id))->keys();
            if ($nonEmptyTypes->isEmpty()) {
                $productType = 'empty';
            } elseif ($nonEmptyTypes->count() === 1) {
                $productType = $nonEmptyTypes->first();
            } else {
                $productType = 'multiple';
            }

            // User Details
            $billing = $request->billing_address ?? [];
            $shipping = $request->shipping_address ?? [];
            $userName = trim(($billing['first_name'] ?? $shipping['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? $shipping['last_name'] ?? ''));
            $contactNumber = $shipping['phone'] ?? $billing['phone'] ?? null;

            // 2. SAVE TO DATABASE
            $order = Order::create([
                'order_id'         => 'ORD-' . Str::uuid(),
                'user_id'          => $request->user()->id ?? null,
                'user_name'        => $userName,
                
                'item_details'     => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'items_id'         => json_encode($itemsId, JSON_UNESCAPED_SLASHES),
                'product_type'     => $productType,
                
                'contact_number'   => $contactNumber,
                'address'          => json_encode($shipping, JSON_UNESCAPED_SLASHES),
                'billing_address'  => json_encode($billing, JSON_UNESCAPED_SLASHES),
                'total_price'      => $request->total_amount,
                
                // COD SPECIFIC FIELDS
                'payment_mode'     => 'cod',
                'payment_method'   => 'cod',
                'payment_status'   => 'pending', // Pending because money isn't received yet
                
                'coupon_code'      => $request->coupon_code,
                'coupon_discount'  => $request->discount, // Frontend sends 'discount' or 'discount_amount'
                'transaction_id'   => 'COD-' . strtoupper(Str::random(10)), // Generate a fake Transaction ID
            ]);

            // 3. Clear Cart
            if ($request->user()) {
                \App\Models\Cart::where('user_id', $request->user()->id)->delete();
            }

            return response()->json(['status' => 'success', 'message' => 'Order Placed Successfully']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
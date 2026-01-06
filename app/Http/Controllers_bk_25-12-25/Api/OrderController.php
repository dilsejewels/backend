<?php
namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $orders = Order::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        // Custom validator with quantity validation
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required|exists:users,id',
            'user_name'      => 'required|string',
            'contact_number' => 'required|string',
            'item_details'   => 'required|json',
            'total_price'    => 'required|numeric',
            'address'        => 'required|json',
            'billing_address' => 'nullable|json',
            'order_status'   => 'required|string',
            'payment_mode'   => 'required|string',
            'payment_status' => 'required|string',
            'transaction_id' => 'nullable|string',
            'razorpay_payment_id' => 'nullable|string',
            'razorpay_order_id' => 'nullable|string',
            'paypal_order_id' => 'nullable|string',
            'payer_email'    => 'nullable|email',
            'is_gift'        => 'nullable|boolean',
            'notes'          => 'nullable|string',
            'coupon_discount' => 'nullable|numeric',
            'coupon_code'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $validated['order_id'] = 'ORD-' . Str::uuid();

        // Parse the payload
        $itemDetails = json_decode($validated['item_details'], true);
        $payload = $itemDetails['items'] ?? [];

        // Initialize arrays for items_id and quantities
        $itemsId = [
            'diamond' => [],
            'jewelry' => [],
            'gift'    => [],
            'build'   => [],
            'combo'   => [],
        ];

        $quantities = [
            'diamond' => 0,
            'jewelry' => 0,
            'gift'    => 0,
            'build'   => 0,
            'combo'   => 0,
            'total'   => 0
        ];

        // Process each item in the payload
        foreach ($payload as $item) {
            $quantity = $item['quantity'] ?? $item['itemQuantity'] ?? 1;

            switch ($item['productType'] ?? null) {
                case 'diamond':
                    if (!empty($item['diamondid'])) {
                        $itemsId['diamond'][] = [
                            'id' => $item['diamondid'],
                            'quantity' => $quantity,
                            'price' => $item['price'] ?? 0,
                            'carat' => $item['carat'] ?? null,
                            'shape' => $item['shape'] ?? null
                        ];
                        $quantities['diamond'] += $quantity;
                    }
                    break;

                case 'jewelry':
                    if (!empty($item['id'])) {
                        $itemsId['jewelry'][] = [
                            'id' => $item['id'],
                            'quantity' => $quantity,
                            'price' => $item['price'] ?? 0,
                            'title' => $item['title'] ?? '',
                            'type' => $item['type'] ?? ''
                        ];
                        $quantities['jewelry'] += $quantity;
                    }
                    break;

                case 'gift':
                    if (!empty($item['id'])) {
                        $itemsId['gift'][] = [
                            'id' => $item['id'],
                            'quantity' => $quantity,
                            'price' => $item['price'] ?? 0,
                            'title' => $item['name'] ?? '',
                            'type' => $item['productType'] ?? ''
                        ];
                        $quantities['gift'] += $quantity;
                    }
                    break;

                case 'build':
                    if (!empty($item['id'])) {
                        $itemsId['build'][] = [
                            'id'   => $item['id'],
                            'size' => $item['size'] ?? null,
                            'quantity' => $quantity,
                            'price' => $item['price'] ?? 0,
                            'specifications' => $item['specifications'] ?? []
                        ];
                        $quantities['build'] += $quantity;
                    }
                    break;

                case 'combo':
                    $itemsId['combo'][] = [
                        'diamond_id' => $item['diamond']['diamondid'] ?? null,
                        'product_id' => $item['ring']['id'] ?? null,
                        'size'       => $item['size'] ?? null,
                        'quantity'   => $quantity,
                        'price'      => $item['price'] ?? 0,
                        'diamond_details' => $item['diamond'] ?? [],
                        'ring_details' => $item['ring'] ?? []
                    ];
                    $quantities['combo'] += $quantity;
                    break;
            }

            $quantities['total'] += $quantity;
        }

        // Calculate total quantities
        $validated['total_quantity'] = $quantities['total'];
        $validated['quantities'] = $quantities;

        // Decide product type for DB
        $nonEmptyTypes = collect($itemsId)->filter(fn($ids) => !empty($ids))->keys();

        if ($nonEmptyTypes->isEmpty()) {
            $productType = 'empty';
        } elseif ($nonEmptyTypes->count() === 1) {
            $productType = $nonEmptyTypes->first();
        } else {
            $productType = 'multiple';
        }

        // Add both product type and items_id into validated array
        $validated['items_id'] = $itemsId;
        $validated['product_type'] = $productType;

        // If billing address is not provided, use shipping address
        if (empty($validated['billing_address']) && !empty($validated['address'])) {
            $validated['billing_address'] = $validated['address'];
        }

        // Save order
        try {
            $order = Order::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'order' => $order,
                'quantities' => $quantities
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('user_id', $userId)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Calculate current quantities from items_id
        $currentQuantities = [
            'diamond' => 0,
            'jewelry' => 0,
            'gift'    => 0,
            'build' => 0,
            'combo' => 0,
            'total' => 0
        ];

        if ($order->items_id && is_array($order->items_id)) {
            foreach ($order->items_id as $type => $items) {
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $itemQuantity = $item['quantity'] ?? 1;
                        $currentQuantities[$type] += $itemQuantity;
                        $currentQuantities['total'] += $itemQuantity;
                    }
                }
            }
        }

        // If items_id se total 0 aaya hai, to old method use karen
        if ($currentQuantities['total'] === 0) {
            try {
                $itemDetails = json_decode($order->item_details, true);
                $items = $itemDetails['items'] ?? [];

                foreach ($items as $item) {
                    $currentQuantities['total'] += $item['itemQuantity'] ?? 1;

                    // Type-wise quantity bhi calculate karen
                    $productType = $item['productType'] ?? 'jewelry';
                    if (isset($currentQuantities[$productType])) {
                        $currentQuantities[$productType] += $item['itemQuantity'] ?? 1;
                    }
                }
            } catch (\Exception $e) {
                // Fallback: agar parse nahi ho paya to 1 consider karen
                $currentQuantities['total'] = 1;
                $currentQuantities['jewelry'] = 1;
            }
        }

        $order->current_quantities = $currentQuantities;

        return response()->json($order);
    }

    // Cancel Order Method
    public function cancel(Request $request, $id)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('user_id', $userId)->where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        // Validate cancellation reason
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|min:10|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return response()->json([
                'status' => 'error',
                'message' => $order->cancellation_message
            ], 400);
        }

        try {
            // Cancel the order
            $success = $order->cancel($request->cancellation_reason);

            if ($success) {
                // Reload the order with fresh data
                $order->refresh();

                $response = [
                    'status' => 'success',
                    'message' => 'Order cancelled successfully',
                    'order' => $order
                ];

                // Add refund information for online payments
                if ($order->payment_mode !== 'cod') {
                    $response['refund_processed'] = true;
                    $response['message'] .= '. Refund has been initiated for your payment.';
                }

                return response()->json($response);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to cancel order'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error("Order cancellation failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error cancelling order: ' . $e->getMessage()
            ], 500);
        }
    }

    // Mark order as delivered (for admin use)
    public function markDelivered($id)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('user_id', $userId)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            $order->markAsDelivered();

            return response()->json([
                'status' => 'success',
                'message' => 'Order marked as delivered',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error marking order as delivered: ' . $e->getMessage()
            ], 500);
        }
    }

    // Mark order as shipped (for admin use)
    public function markShipped($id)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('user_id', $userId)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            $success = $order->markAsShipped();

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Order marked as shipped',
                    'order' => $order
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot mark order as shipped. Current status: ' . $order->order_status
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error marking order as shipped: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get cancellation eligibility
    public function getCancellationInfo($id)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::where('user_id', $userId)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'can_be_cancelled' => $order->canBeCancelled(),
                'cancellation_message' => $order->cancellation_message,
                'order_status' => $order->order_status,
                'payment_mode' => $order->payment_mode,
                'payment_status' => $order->payment_status
            ]
        ]);
    }

    // New method to get quantity summary for all orders
    public function getQuantitySummary(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $orders = Order::where('user_id', $userId)->get();

        $summary = [
            'total_orders' => $orders->count(),
            'total_items' => 0,
            'by_type' => [
                'diamond' => 0,
                'jewelry' => 0,
                'gift' => 0,
                'build' => 0,
                'combo' => 0
            ],
            'by_status' => []
        ];

        foreach ($orders as $order) {
            $summary['total_items'] += $order->total_quantity ?? 0;

            // Count by product type
            if ($order->quantities && is_array($order->quantities)) {
                foreach ($order->quantities as $type => $qty) {
                    if (in_array($type, ['diamond', 'jewelry', 'gift', 'build', 'combo'])) {
                        $summary['by_type'][$type] += $qty;
                    }
                }
            }

            // Count by order status
            $status = $order->order_status ?? 'unknown';
            if (!isset($summary['by_status'][$status])) {
                $summary['by_status'][$status] = 0;
            }
            $summary['by_status'][$status]++;
        }

        return response()->json($summary);
    }
}
<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DiamondMaster;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\DiamondShape;
use App\Models\DiamondColor;
use App\Models\DiamondClarityMaster;
use App\Models\DiamondCut;
use App\Models\Address;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.DiamondMaster.Orders.index');
    }

    public function searchProducts(Request $request)
    {
        $search = $request->input('search');

        $products = Product::with(['variations', 'metalType'])
            ->where(function ($query) use ($search) {
                $query->where('products_name', 'like', "%{$search}%")
                    ->orWhere('products_sku', 'like', "%{$search}%")
                    ->orWhere('master_sku', 'like', "%{$search}%");
            })
            ->where('products_status', 1)
            ->limit(20)
            ->get();

        $results = [];
        foreach ($products as $product) {
            foreach ($product->variations as $variation) {
                $results[] = [
                    'id' => $variation->id,
                    'product_id' => $product->products_id,
                    'name' => $product->products_name,
                    'sku' => $variation->sku ?: $product->products_sku,
                    'price' => $variation->price ?: $product->products_price,
                    'regular_price' => $variation->regular_price ?: $product->products_price,
                    'carat' => $variation->carat,
                    'weight' => $variation->weight,
                    'metal_color' => $variation->metalColor ? $variation->metalColor->dmt_name : null,
                    'shape' => $variation->shape ? $variation->shape->shape_name : null,
                    'stock' => $variation->stock,
                    'type' => 'jewelry',
                    'images' => $variation->images
                ];
            }

            if ($product->variations->isEmpty()) {
                $results[] = [
                    'id' => $product->products_id,
                    'product_id' => $product->products_id,
                    'name' => $product->products_name,
                    'sku' => $product->products_sku,
                    'price' => $product->products_price,
                    'regular_price' => $product->products_price,
                    'carat' => null,
                    'weight' => $product->products_weight,
                    'metal_color' => $product->metalType ? $product->metalType->dmt_name : null,
                    'shape' => null,
                    'stock' => $product->products_quantity,
                    'type' => 'jewelry',
                    'images' => []
                ];
            }
        }

        return response()->json($results);
    }

    public function searchDiamonds(Request $request)
    {
        try {
            $search = $request->input('search');

            if (empty($search)) {
                return response()->json([]);
            }

            $diamonds = DiamondMaster::where(function ($query) use ($search) {
                $query->where('stock_number', 'like', "%{$search}%")
                    ->orWhere('certificate_number', 'like', "%{$search}%")
                    ->orWhere('vendor_stock_number', 'like', "%{$search}%")
                    ->orWhere('diamondid', 'like', "%{$search}%");
            })
                ->where('status', 1)
                ->limit(20)
                ->get();

            $results = [];
            foreach ($diamonds as $diamond) {
                $shapeName = DiamondShape::where('id', $diamond->shape)->value('name') ?? 'N/A';
                $colorName = DiamondColor::where('id', $diamond->color)->value('name') ?? 'N/A';
                $clarityName = DiamondClarityMaster::where('id', $diamond->clarity)->value('name') ?? 'N/A';
                $cutName = DiamondCut::where('id', $diamond->cut)->value('name') ?? 'N/A';

                $results[] = [
                    'id' => $diamond->diamondid,
                    'name' => ($diamond->diamond_type == 1) ? 'Natural Diamond' : 'CVD Diamond',
                    'certificate_number' => $diamond->certificate_number ?? 'N/A',
                    'shape' => $shapeName,
                    'carat_weight' => $diamond->carat_weight ?? 0,
                    'color' => $colorName,
                    'clarity' => $clarityName,
                    'cut' => $cutName,
                    'price' => floatval($diamond->price ?? 0),
                    'price_per_carat' => floatval($diamond->price_per_carat ?? 0),
                    'stock' => intval($diamond->on_hand ?? 0),
                    'type' => 'diamond',
                    'image' => $diamond->image_link ?? ''
                ];
            }

            return response()->json($results);
        } catch (\Exception $e) {
            \Log::error('Diamond search error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function searchUsers(Request $request)
    {
        $search = $request->input('search');

        $users = User::with(['addresses'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        $results = [];
        foreach ($users as $user) {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'addresses' => []
            ];

            foreach ($user->addresses as $address) {
                $userData['addresses'][] = [
                    'id' => $address->id,
                    'first_name' => $address->first_name,
                    'last_name' => $address->last_name,
                    'phone_number' => $address->phone_number,
                    'address' => $address->address,
                    'country' => $address->country,
                    'full_address' => $this->formatAddress($address)
                ];
            }

            $results[] = $userData;
        }

        return response()->json($results);
    }

    private function formatAddress($address)
    {
        if (is_array($address->address)) {
            $addr = $address->address;
        } else {
            $addr = json_decode($address->address, true) ?? [];
        }

        $parts = [
            $address->first_name . ' ' . $address->last_name,
            $addr['street'] ?? $addr['address_line1'] ?? '',
            $addr['city'] ?? $addr['locality'] ?? '',
            $addr['state'] ?? $addr['administrative_area'] ?? '',
            $address->country,
            $addr['zip'] ?? $addr['postal_code'] ?? $addr['pincode'] ?? '',
        ];

        return implode(', ', array_filter($parts, function ($value) {
            return !empty($value) && $value !== ' ';
        }));
    }

    public function fetch(Request $request)
    {
        try {
            $orders = Order::query();

            $total = $orders->count();

            if ($search = $request->input('search.value')) {
                $orders->where(function ($query) use ($search) {
                    $query->where('order_id', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('coupon_code', 'like', "%{$search}%")
                        ->orWhere('payment_mode', 'like', "%{$search}%")
                        ->orWhere('order_status', 'like', "%{$search}%");
                });
            }

            $filteredCount = $orders->count();

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $length = $length > 0 ? $length : 10;

            $data = $orders->orderBy('created_at', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $filteredCount,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Order fetch error: ' . $e->getMessage());

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching orders'
            ]);
        }
    }

    public function show(Order $order)
    {
        try {
            $processedItems = $this->processItemsForOrder($order);

            return view('admin.DiamondMaster.Orders.invoice', compact('order', 'processedItems'));
        } catch (\Exception $e) {
            Log::error('Error in OrderController@show: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->view('admin.DiamondMaster.Orders.invoice_error', [
                'message' => 'Unable to load order details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadInvoice(Order $order)
    {
        $processedItems = $this->processItemsForOrder($order);
        $pdf = Pdf::loadView('admin.DiamondMaster.Orders.invoice', compact('order', 'processedItems'));
        return $pdf->download("Invoice-{$order->order_id}.pdf");
    }

    public function sendInvoice(Request $request, Order $order)
    {
        try {
            $to = $request->query('to', 'user');
            $email = $to === 'admin'
                ? config('mail.from.address')
                : ($order->user->email ?? $order->address_email ?? $order->user->email ?? null);

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid email found for the recipient'
                ], 400);
            }

            // Process items for PDF
            $processedItems = $this->processItemsForOrder($order);

            // Generate PDF with processedItems
            $pdf = Pdf::loadView('admin.DiamondMaster.Orders.invoice', compact('order', 'processedItems'));

            // Store PDF on S3
            $pdfPath = 'invoices/' . $order->order_id . '.pdf';
            Storage::disk('s3')->put($pdfPath, $pdf->output());
            $pdfUrl = Storage::disk('s3')->url($pdfPath);

            // Send email
            Mail::send('admin.DiamondMaster.emails.email_template_invoice', [
                'order' => $order,
                'downloadUrl' => $pdfUrl
            ], function ($message) use ($order, $email, $pdf) {
                $message->to($email)
                    ->subject("Invoice - {$order->order_id}")
                    ->attachData($pdf->output(), "Invoice-{$order->order_id}.pdf");
            });

            return response()->json([
                'success' => true,
                'message' => "Invoice sent to " . ($to === 'admin' ? 'admin' : 'customer')
            ]);
        } catch (\Exception $e) {
            Log::error('Invoice sending error: ' . $e->getMessage());
            Log::error('Invoice error trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice: ' . $e->getMessage()
            ], 500);
        }
    }

private function processItemsForOrder($order)
{
    try {
        $itemDetails = $order->item_details;

        if (is_string($itemDetails)) {
            $itemDetails = json_decode($itemDetails, true);
        }

        if (!is_array($itemDetails)) {
            $itemDetails = [];
        }

        $processedItems = [];

        // Helper function to extract name from array or object
        $extractName = function ($value) {
            if (is_array($value)) {
                return $value['name'] ?? $value['short_name'] ?? $value['shape_name'] ?? 'N/A';
            }
            if (is_object($value)) {
                return $value->name ?? $value->short_name ?? $value->shape_name ?? 'N/A';
            }
            return $value ?? 'N/A';
        };

        // Helper function to create diamond name
        $createDiamondName = function ($item) use ($extractName) {
            $shape = $extractName($item['shape'] ?? null);
            $carat = $item['carat_weight'] ?? $item['carat'] ?? null;
            $color = $extractName($item['color'] ?? null);
            $clarity = $extractName($item['clarity'] ?? null);
            $cert = $item['certificate_number'] ?? null;
            
            // Get diamond type from item
            $diamondTypeValue = $item['diamond_type'] ?? 1; // Default to Natural
            $diamondType = ($diamondTypeValue == 1) ? 'Natural' : 'CVD';

            // Create descriptive name
            $nameParts = [];
            
            if ($shape !== 'N/A' && $shape !== null) {
                $nameParts[] = $shape;
            }
            
            if ($carat && $carat !== 'N/A') {
                $nameParts[] = $carat . 'ct';
            }
            
            if ($color !== 'N/A' && $color !== null) {
                $nameParts[] = $color;
            }
            
            if ($clarity !== 'N/A' && $clarity !== null) {
                $nameParts[] = $clarity;
            }
            
            // Add diamond type to name
            $nameParts[] = $diamondType . ' Diamond';
            
            $name = implode(' ', $nameParts);
            
            // Add certificate if available
            if ($cert && $cert !== 'N/A') {
                $name .= ' (' . $cert . ')';
            }
            
            return $name;
        };

        Log::info('Processing items for order: ' . $order->order_id);

        /* -------------------------
         CASE 1: Indexed Array Items
        ----------------------------*/
        if (is_array($itemDetails) && isset($itemDetails[0])) {
            Log::info('Processing as indexed array');
            foreach ($itemDetails as $item) {
                if (is_array($item)) {
                    $type = $item['productType'] ?? 'gift';
                    $name = $item['name'] ?? ($item['product_name'] ?? 'Product');

                    $processedItem = [
                        'type' => $type,
                        'id' => $item['id'] ?? null,
                        'name' => $name,
                        'quantity' => $item['itemQuantity'] ?? $item['quantity'] ?? 1,
                        'price' => $item['price'] ?? 0,
                        'type_label' => ucfirst($type)
                    ];

                    if ($type === 'diamond') {
                        // For diamonds without proper name, create descriptive name
                        if (empty($name) || $name === 'Diamond' || $name === 'Natural Diamond' || $name === 'CVD Diamond') {
                            $processedItem['name'] = $createDiamondName($item);
                        }
                        
                        // Store diamond type separately as well
                        $processedItem['diamond_type'] = $item['diamond_type'] ?? 1;
                        $processedItem['diamond_type_label'] = ($processedItem['diamond_type'] == 1) ? 'Natural' : 'CVD';
                        
                        $processedItem['certificate_number'] = $item['certificate_number'] ?? 'N/A';
                        $processedItem['carat_weight'] = $item['carat_weight'] ?? $item['carat'] ?? 'N/A';
                        $processedItem['color'] = $extractName($item['color'] ?? null);
                        $processedItem['clarity'] = $extractName($item['clarity'] ?? null);
                        $processedItem['shape'] = $extractName($item['shape'] ?? null);
                        $processedItem['cut'] = $extractName($item['cut'] ?? null);
                        $processedItem['certificate_company'] = $item['certificate_company']['dl_name'] ?? ($item['certificate_company'] ?? 'N/A');
                        $processedItem['polish'] = $item['polish']['name'] ?? ($item['polish'] ?? 'N/A');
                        $processedItem['symmetry'] = $item['symmetry']['name'] ?? ($item['symmetry'] ?? 'N/A');
                        $processedItem['fluorescence'] = $item['fluorescence']['name'] ?? ($item['fluorescence'] ?? 'N/A');
                        $processedItem['measurements'] = $item['measurements'] ?? 'N/A';
                        
                    } elseif ($type === 'jewelry') {
                        $processedItem['metal_type'] = $extractName($item['metal_type'] ?? null);
                        $processedItem['metal_color'] = $extractName($item['metal_color'] ?? null);
                        $processedItem['metal_purity'] = $extractName($item['metal_purity'] ?? null);
                        $processedItem['size'] = $item['size'] ?? ($item['ring_size'] ?? 'N/A');
                        $processedItem['carat'] = $item['carat'] ?? 'N/A';
                    } elseif ($type === 'gift') {
                        $processedItem['size'] = $item['size'] ?? 'N/A';
                        $processedItem['metal_type'] = $extractName($item['metal_type'] ?? null);
                        $processedItem['metal_color'] = $extractName($item['metal_color'] ?? null);
                        $processedItem['shape'] = $extractName($item['shape'] ?? null);
                    } elseif ($type === 'combo') {
                        $processedItem['size'] = $item['size'] ?? 'N/A';
                        $processedItem['metal_type'] = $extractName($item['metal_type'] ?? null);
                        $processedItem['diamond_certificate'] = $item['diamond_certificate'] ?? ($item['diamond']['certificate_number'] ?? 'N/A');
                        $processedItem['ring_price'] = $item['ring_price'] ?? ($item['ring']['price'] ?? 0);
                        $processedItem['diamond_price'] = $item['diamond_price'] ?? ($item['diamond']['price'] ?? 0);
                        $processedItem['diamond_type'] = $item['diamond_type'] ?? ($item['diamond']['diamond_type'] ?? 1);
                        $processedItem['diamond_type_label'] = ($processedItem['diamond_type'] == 1) ? 'Natural' : 'CVD';
                    }

                    $processedItems[] = $processedItem;
                }
            }
        }

        /* -------------------------------------
         CASE 2: Old Style Format (diamond/jewelry/gift/combo)
        ----------------------------------------*/
        if (empty($processedItems)) {
            if (isset($itemDetails['diamond'])) {
                foreach ($itemDetails['diamond'] as $diamond) {
                    $name = $diamond['name'] ?? $diamond['diamond_name'] ?? 'Diamond';
                    if (empty($name) || $name === 'Diamond' || $name === 'Natural Diamond' || $name === 'CVD Diamond') {
                        $name = $createDiamondName($diamond);
                    }
                    
                    $processedItems[] = [
                        'type' => 'diamond',
                        'id' => $diamond['id'] ?? null,
                        'name' => $name,
                        'quantity' => $diamond['quantity'] ?? 1,
                        'price' => $diamond['price'] ?? 0,
                        'diamond_type' => $diamond['diamond_type'] ?? 1,
                        'diamond_type_label' => ($diamond['diamond_type'] == 1) ? 'Natural' : 'CVD',
                        'certificate_number' => $diamond['certificate_number'] ?? 'N/A',
                        'carat_weight' => $diamond['carat_weight'] ?? 'N/A',
                        'color' => $extractName($diamond['color'] ?? null),
                        'clarity' => $extractName($diamond['clarity'] ?? null),
                        'shape' => $extractName($diamond['shape'] ?? null),
                        'type_label' => 'Diamond'
                    ];
                }
            }

            // ... rest of the code for jewelry, gift, combo ...
        }

        /* -------------------------
         CASE 3: Default
        ----------------------------*/
        if (empty($processedItems)) {
            $processedItems[] = [
                'type' => 'product',
                'id' => null,
                'name' => 'Order Products',
                'quantity' => $order->total_quantity ?? 1,
                'price' => $order->total_price ?? 0,
                'type_label' => 'Product'
            ];
        }

        Log::info('Processed items: ' . json_encode($processedItems));
        return $processedItems;
    } catch (\Exception $e) {
        Log::error("Error processing items for order {$order->id}: " . $e->getMessage());
        Log::error("Error trace: " . $e->getTraceAsString());

        return [[
            'type' => 'product',
            'id' => null,
            'name' => 'Order Products',
            'quantity' => $order->total_quantity ?? 1,
            'price' => $order->total_price ?? 0,
            'type_label' => 'Product'
        ]];
    }
}



    // Enhanced changeStatus method to use Order model methods
    public function changeStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|string',
            'reason' => 'nullable|string|max:500'
        ]);

        $newStatus = $request->order_status;
        $reason = $request->reason;

        try {
            switch ($newStatus) {
                case 'cancelled':
                    if ($order->cancel($reason)) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Order cancelled successfully'
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Order cannot be cancelled in its current status'
                        ], 400);
                    }
                    break;

                case 'delivered':
                    $order->markAsDelivered();
                    return response()->json([
                        'success' => true,
                        'message' => 'Order marked as delivered successfully'
                    ]);
                    break;

                case 'shipped':
                    if ($order->markAsShipped()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Order marked as shipped successfully'
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Order cannot be shipped in its current status'
                        ], 400);
                    }
                    break;

                default:
                    // For other status updates
                    $order->update([
                        'order_status' => $newStatus,
                        'updated_at' => now()
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Order status updated successfully'
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Error changing order status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to process refund manually
    public function processRefund(Request $request, Order $order)
    {
        try {
            if ($order->payment_status !== 'refunded' && $order->order_status === 'cancelled') {
                $order->processRefund();

                return response()->json([
                    'success' => true,
                    'message' => 'Refund processed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund cannot be processed for this order'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error processing refund: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing refund: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to check cancellation eligibility
    public function checkCancellation(Order $order)
    {
        return response()->json([
            'can_be_cancelled' => $order->canBeCancelled(),
            'cancellation_message' => $order->cancellation_message,
            'current_status' => $order->order_status
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'user_name' => 'required|string',
            'contact_number' => 'required|string',
            'items' => 'required|array',
            'items.*.id' => 'required',
            'items.*.type' => 'required|in:diamond,jewelry,combo',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric', // यहाँ frontend से आया हुआ total है (पहले ही discount घटा हुआ)
            'shipping_cost' => 'nullable|numeric',
            'coupon_code' => 'nullable|string',
            'coupon_discount' => 'nullable|numeric', // सिर्फ record के लिए
            'address' => 'required|array',
            'billing_address' => 'nullable|array',
            'payment_mode' => 'required|in:cod,card,upi,netbanking,paypal',
            'transaction_id' => 'nullable|string',
        ]);

        // Process items
        $processedItems = [];
        $itemIds = [];
        $hasDiamond = false;
        $hasJewelry = false;
        $totalQuantity = 0;
        $originalSubtotal = 0; // Original subtotal calculate करें

        foreach ($request->items as $item) {
            $processedItem = [
                'type' => $item['type'],
                'id' => $item['id'],
                'name' => $item['name'] ?? 'Product',
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ];

            if ($item['type'] === 'diamond') {
                $hasDiamond = true;
                $processedItem['certificate_number'] = $item['certificate_number'] ?? null;
                $processedItem['carat_weight'] = $item['carat_weight'] ?? null;
                $processedItem['color'] = $item['color'] ?? null;
                $processedItem['clarity'] = $item['clarity'] ?? null;
                $processedItem['shape'] = $item['shape'] ?? null;
                $itemIds[] = $item['id'];
            } elseif ($item['type'] === 'jewelry') {
                $hasJewelry = true;
                $processedItem['metal_type'] = $item['metal_type'] ?? null;
                $processedItem['metal_color'] = $item['metal_color'] ?? null;
                $processedItem['metal_purity'] = $item['metal_purity'] ?? null;
                $processedItem['size'] = $item['size'] ?? null;
                $itemIds[] = $item['id'];
            } else {
                // Combo
                $hasDiamond = true;
                $hasJewelry = true;
                $processedItem['size'] = $item['size'] ?? null;
                $processedItem['metal_type'] = $item['metal_type'] ?? null;
                $itemIds[] = $item['id'];
            }

            $totalQuantity += $item['quantity'];
            $originalSubtotal += $item['price'] * $item['quantity'];
            $processedItems[] = $processedItem;
        }

        // Determine product type
        if ($hasDiamond && $hasJewelry) {
            $data['product_type'] = 'mixed';
        } elseif ($hasDiamond) {
            $data['product_type'] = 'diamond';
        } elseif ($hasJewelry) {
            $data['product_type'] = 'jewelry';
        } else {
            $data['product_type'] = 'combo';
        }

        $data['items_id'] = $itemIds;
        $data['item_details'] = $processedItems;
        $data['order_id'] = 'ORD-' . now()->format('YmdHis') . '-' . Str::random(6);
        $data['total_quantity'] = $totalQuantity;
        $data['payment_status'] = $data['payment_mode'] === 'cod' ? 'pending' : 'paid';
        $data['order_status'] = 'confirmed';
        $data['discount'] = 0; // हमेशा 0 सेट करें

        // Frontend से आया हुआ total_price ही store करें (पहले ही discount घटा हुआ)
        // total_price में coupon discount पहले ही घटा हुआ है

        // Set billing address same as shipping if not provided
        if (empty($data['billing_address'])) {
            $data['billing_address'] = $data['address'];
        }

        Order::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $data['order_id']
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'user_name' => 'sometimes|string',
            'contact_number' => 'sometimes|string',
            'total_price' => 'sometimes|numeric',
            'shipping_cost' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'coupon_code' => 'nullable|string',
            'coupon_discount' => 'nullable|numeric',
            'order_status' => 'sometimes|string',
            'payment_status' => 'sometimes|string',
        ]);

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully'
        ]);
    }

    public function destroy(Order $order)
    {
        try {
            // Check if order can be deleted (only allow deletion of cancelled or very old orders)
            if (
                !in_array($order->order_status, ['cancelled', 'pending']) &&
                $order->created_at->gt(now()->subDays(30))
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only cancelled or old pending orders can be deleted'
                ], 400);
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting order: ' . $e->getMessage()
            ], 500);
        }
    }
}

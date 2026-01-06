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
                        ->orWhere('coupon_code', 'like', "%{$search}%");
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
            $itemDetails = $order->item_details;

            if (is_string($itemDetails)) {
                $itemDetails = json_decode($itemDetails, true);
            }

            if (!is_array($itemDetails)) {
                $itemDetails = [];
            }

            $processedItems = [];

            if (isset($itemDetails['diamond'])) {
                foreach ($itemDetails['diamond'] as $diamond) {
                    $processedItems[] = [
                        'type' => 'diamond',
                        'id' => $diamond['id'] ?? null,
                        'name' => $diamond['diamond_name'] ?? $diamond['name'] ?? 'Diamond',
                        'quantity' => $diamond['quantity'] ?? 1,
                        'price' => $diamond['price'] ?? 0,
                        'certificate_number' => $diamond['certificate_number'] ?? $diamond['certificate_no'] ?? 'N/A',
                        'carat_weight' => $diamond['carat_weight'] ?? $diamond['carat'] ?? 'N/A',
                        'color' => $diamond['color'] ?? 'N/A',
                        'clarity' => $diamond['clarity'] ?? 'N/A',
                        'shape' => $diamond['shape'] ?? 'N/A'
                    ];
                }
            }

            if (isset($itemDetails['jewelry'])) {
                foreach ($itemDetails['jewelry'] as $jewelry) {
                    $processedItems[] = [
                        'type' => 'jewelry',
                        'id' => $jewelry['id'] ?? null,
                        'name' => $jewelry['jewelry_name'] ?? $jewelry['name'] ?? 'Jewelry',
                        'quantity' => $jewelry['quantity'] ?? 1,
                        'price' => $jewelry['price'] ?? 0,
                        'metal_type' => $jewelry['metal_type'] ?? $jewelry['metal'] ?? 'N/A',
                        'metal_color' => $jewelry['metal_color'] ?? $jewelry['color'] ?? 'N/A',
                        'metal_purity' => $jewelry['metal_purity'] ?? $jewelry['purity'] ?? 'N/A',
                        'size' => $jewelry['size'] ?? $jewelry['ring_size'] ?? 'N/A'
                    ];
                }
            }

            if (isset($itemDetails['combo'])) {
                foreach ($itemDetails['combo'] as $combo) {
                    $processedItems[] = [
                        'type' => 'combo',
                        'id' => $combo['id'] ?? null,
                        'name' => $combo['name'] ?? 'Combo Package',
                        'quantity' => $combo['quantity'] ?? 1,
                        'price' => $combo['price'] ?? 0,
                        'size' => $combo['size'] ?? 'N/A',
                    ];
                }
            }

            if (isset($itemDetails['items'])) {
                foreach ($itemDetails['items'] as $item) {
                    $type = $item['productType'] ?? 'jewelry';
                    
                    if ($type === 'combo') {
                        $ringPrice = $item['ring']['price'] ?? 0;
                        $diamondPrice = $item['diamond']['price'] ?? 0;
                        $comboPrice = $ringPrice + $diamondPrice;
                        
                        $processedItems[] = [
                            'type' => 'combo',
                            'id' => $item['ring']['id'] ?? null,
                            'name' => $item['ring']['name'] ?? 'Combo Package',
                            'quantity' => $item['itemQuantity'] ?? 1,
                            'price' => $comboPrice,
                            'size' => $item['size'] ?? 'N/A',
                            'ring_price' => $ringPrice,
                            'diamond_price' => $diamondPrice,
                            'diamond_certificate' => $item['diamond']['certificate_number'] ?? 'N/A',
                            'metal_type' => $item['ring']['metal_color']['name'] ?? 'N/A'
                        ];
                    } else {
                        if (isset($item['ring'])) {
                            $processedItems[] = [
                                'type' => 'jewelry',
                                'id' => $item['ring']['id'] ?? null,
                                'name' => $item['ring']['name'] ?? 'Jewelry',
                                'quantity' => $item['itemQuantity'] ?? 1,
                                'price' => $item['ring']['price'] ?? 0,
                                'metal_type' => $item['ring']['metal_color']['name'] ?? 'N/A',
                                'size' => $item['size'] ?? 'N/A'
                            ];
                        } elseif (isset($item['diamond'])) {
                            $diamondName = isset($item['diamond']['shape']['name']) ? 
                                          $item['diamond']['shape']['name'] . ' Diamond' : 'Diamond';
                                          
                            $processedItems[] = [
                                'type' => 'diamond',
                                'id' => $item['diamond']['diamondid'] ?? null,
                                'name' => $diamondName,
                                'quantity' => $item['itemQuantity'] ?? 1,
                                'price' => $item['diamond']['price'] ?? 0,
                                'certificate_number' => $item['diamond']['certificate_number'] ?? 'N/A',
                                'carat_weight' => $item['diamond']['carat_weight'] ?? 'N/A',
                                'color' => $item['diamond']['color']['name'] ?? 'N/A',
                                'clarity' => $item['diamond']['clarity']['name'] ?? 'N/A'
                            ];
                        } elseif (isset($item['productType'])) {
                            $processedItems[] = [
                                'type' => $item['productType'],
                                'id' => $item['id'] ?? null,
                                'name' => $item['name'] ?? ucfirst($item['productType']),
                                'quantity' => $item['itemQuantity'] ?? 1,
                                'price' => $item['price'] ?? 0,
                                'size' => $item['size'] ?? 'N/A'
                            ];
                        }
                    }
                }
            }

            if (empty($processedItems)) {
                foreach ($itemDetails as $item) {
                    if (is_array($item) && isset($item['type'])) {
                        $processedItems[] = [
                            'type' => $item['type'] ?? 'jewelry',
                            'id' => $item['id'] ?? null,
                            'name' => $item['name'] ?? 'Product',
                            'quantity' => $item['quantity'] ?? 1,
                            'price' => $item['price'] ?? 0,
                            'certificate_number' => $item['certificate_number'] ?? 'N/A',
                            'metal_color' => $item['metal_color'] ?? 'N/A',
                            'size' => $item['size'] ?? 'N/A',
                            'carat_weight' => $item['carat_weight'] ?? 'N/A',
                            'shape' => $item['shape'] ?? 'N/A',
                            'color' => $item['color'] ?? 'N/A',
                            'clarity' => $item['clarity'] ?? 'N/A',
                        ];
                    }
                }
            }

            if (empty($processedItems) && $order->total_price > 0) {
                $processedItems[] = [
                    'type' => 'unknown',
                    'id' => null,
                    'name' => 'Product',
                    'quantity' => 1,
                    'price' => $order->total_price,
                    'certificate_number' => null
                ];
            }

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
        $pdf = Pdf::loadView('admin.DiamondMaster.Orders.invoice', compact('order'));
        return $pdf->download("Invoice-{$order->order_id}.pdf");
    }

public function sendInvoice(Request $request, Order $order)
{
    try {
        $to = $request->query('to', 'user');
        $email = $to === 'admin'
            ? config('mail.from.address')
            : ($order->user->email ?? optional($order->address)['email'] ?? null);

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'No valid email found for the recipient'
            ], 400);
        }

        // Process items for PDF (same as show method)
        $processedItems = $this->processItemsForOrder($order);

        // Generate PDF with processedItems
        $pdf = Pdf::loadView('admin.DiamondMaster.Orders.invoice', compact('order', 'processedItems'));

        // Store PDF on S3
        $pdfPath = 'invoices/' . $order->order_id . '.pdf';
        Storage::disk('s3')->put($pdfPath, $pdf->output());
        $pdfUrl = Storage::disk('s3')->url($pdfPath);

        // Send simple email without processedItems
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

// Add this helper method to process items
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

        if (isset($itemDetails['diamond'])) {
            foreach ($itemDetails['diamond'] as $diamond) {
                $processedItems[] = [
                    'type' => 'diamond',
                    'id' => $diamond['id'] ?? null,
                    'name' => $diamond['diamond_name'] ?? $diamond['name'] ?? 'Diamond',
                    'quantity' => $diamond['quantity'] ?? 1,
                    'price' => $diamond['price'] ?? 0,
                    'certificate_number' => $diamond['certificate_number'] ?? $diamond['certificate_no'] ?? 'N/A',
                    'carat_weight' => $diamond['carat_weight'] ?? $diamond['carat'] ?? 'N/A',
                    'color' => $diamond['color'] ?? 'N/A',
                    'clarity' => $diamond['clarity'] ?? 'N/A',
                    'shape' => $diamond['shape'] ?? 'N/A'
                ];
            }
        }

        if (isset($itemDetails['jewelry'])) {
            foreach ($itemDetails['jewelry'] as $jewelry) {
                $processedItems[] = [
                    'type' => 'jewelry',
                    'id' => $jewelry['id'] ?? null,
                    'name' => $jewelry['jewelry_name'] ?? $jewelry['name'] ?? 'Jewelry',
                    'quantity' => $jewelry['quantity'] ?? 1,
                    'price' => $jewelry['price'] ?? 0,
                    'metal_type' => $jewelry['metal_type'] ?? $jewelry['metal'] ?? 'N/A',
                    'metal_color' => $jewelry['metal_color'] ?? $jewelry['color'] ?? 'N/A',
                    'metal_purity' => $jewelry['metal_purity'] ?? $jewelry['purity'] ?? 'N/A',
                    'size' => $jewelry['size'] ?? $jewelry['ring_size'] ?? 'N/A'
                ];
            }
        }

        if (isset($itemDetails['combo'])) {
            foreach ($itemDetails['combo'] as $combo) {
                $processedItems[] = [
                    'type' => 'combo',
                    'id' => $combo['id'] ?? null,
                    'name' => $combo['name'] ?? 'Combo Package',
                    'quantity' => $combo['quantity'] ?? 1,
                    'price' => $combo['price'] ?? 0,
                    'size' => $combo['size'] ?? 'N/A',
                ];
            }
        }

        if (isset($itemDetails['items'])) {
            foreach ($itemDetails['items'] as $item) {
                $type = $item['productType'] ?? 'jewelry';
                
                if ($type === 'combo') {
                    $ringPrice = $item['ring']['price'] ?? 0;
                    $diamondPrice = $item['diamond']['price'] ?? 0;
                    $comboPrice = $ringPrice + $diamondPrice;
                    
                    $processedItems[] = [
                        'type' => 'combo',
                        'id' => $item['ring']['id'] ?? null,
                        'name' => $item['ring']['name'] ?? 'Combo Package',
                        'quantity' => $item['itemQuantity'] ?? 1,
                        'price' => $comboPrice,
                        'size' => $item['size'] ?? 'N/A',
                        'ring_price' => $ringPrice,
                        'diamond_price' => $diamondPrice,
                        'diamond_certificate' => $item['diamond']['certificate_number'] ?? 'N/A',
                        'metal_type' => $item['ring']['metal_color']['name'] ?? 'N/A'
                    ];
                } else {
                    if (isset($item['ring'])) {
                        $processedItems[] = [
                            'type' => 'jewelry',
                            'id' => $item['ring']['id'] ?? null,
                            'name' => $item['ring']['name'] ?? 'Jewelry',
                            'quantity' => $item['itemQuantity'] ?? 1,
                            'price' => $item['ring']['price'] ?? 0,
                            'metal_type' => $item['ring']['metal_color']['name'] ?? 'N/A',
                            'size' => $item['size'] ?? 'N/A'
                        ];
                    } elseif (isset($item['diamond'])) {
                        $diamondName = isset($item['diamond']['shape']['name']) ? 
                                      $item['diamond']['shape']['name'] . ' Diamond' : 'Diamond';
                                      
                        $processedItems[] = [
                            'type' => 'diamond',
                            'id' => $item['diamond']['diamondid'] ?? null,
                            'name' => $diamondName,
                            'quantity' => $item['itemQuantity'] ?? 1,
                            'price' => $item['diamond']['price'] ?? 0,
                            'certificate_number' => $item['diamond']['certificate_number'] ?? 'N/A',
                            'carat_weight' => $item['diamond']['carat_weight'] ?? 'N/A',
                            'color' => $item['diamond']['color']['name'] ?? 'N/A',
                            'clarity' => $item['diamond']['clarity']['name'] ?? 'N/A'
                        ];
                    } elseif (isset($item['productType'])) {
                        $processedItems[] = [
                            'type' => $item['productType'],
                            'id' => $item['id'] ?? null,
                            'name' => $item['name'] ?? ucfirst($item['productType']),
                            'quantity' => $item['itemQuantity'] ?? 1,
                            'price' => $item['price'] ?? 0,
                            'size' => $item['size'] ?? 'N/A'
                        ];
                    }
                }
            }
        }

        if (empty($processedItems)) {
            foreach ($itemDetails as $item) {
                if (is_array($item) && isset($item['type'])) {
                    $processedItems[] = [
                        'type' => $item['type'] ?? 'jewelry',
                        'id' => $item['id'] ?? null,
                        'name' => $item['name'] ?? 'Product',
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => $item['price'] ?? 0,
                        'certificate_number' => $item['certificate_number'] ?? 'N/A',
                        'metal_color' => $item['metal_color'] ?? 'N/A',
                        'size' => $item['size'] ?? 'N/A',
                        'carat_weight' => $item['carat_weight'] ?? 'N/A',
                        'shape' => $item['shape'] ?? 'N/A',
                        'color' => $item['color'] ?? 'N/A',
                        'clarity' => $item['clarity'] ?? 'N/A',
                    ];
                }
            }
        }

        if (empty($processedItems) && $order->total_price > 0) {
            $processedItems[] = [
                'type' => 'unknown',
                'id' => null,
                'name' => 'Product',
                'quantity' => 1,
                'price' => $order->total_price,
                'certificate_number' => null
            ];
        }

        return $processedItems;
    } catch (\Exception $e) {
        Log::error('Error processing items for order: ' . $e->getMessage());
        return [];
    }
}

    public function changeStatus(Request $request, Order $order)
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'confirmed' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => ['returned', 'cancelled'],
            'cancelled' => [],
            'returned' => [],
        ];

        $currentStatus = $order->order_status;
        $newStatus = $request->order_status;

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            return response()->json([
                'success' => false,
                'message' => "Invalid status transition"
            ], 400);
        }

        $order->update(['order_status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'new_status' => $newStatus
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
            'items.*.type' => 'required|in:diamond,jewelry',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric',
            'shipping_cost' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'address' => 'required|array',
        ]);

        // Process items
        $processedItems = [];
        $itemIds = [];
        $hasDiamond = false;
        $hasJewelry = false;
        $totalQuantity = 0;

        foreach ($request->items as $item) {
            $processedItem = [
                'type' => $item['type'],
                'id' => $item['id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ];

            if ($item['type'] === 'diamond') {
                $hasDiamond = true;
                $processedItem['certificate_number'] = $item['certificate_number'] ?? null;
                $itemIds[] = $item['id'];
            } else {
                $hasJewelry = true;
                $processedItem['metal_type'] = $item['metal_type'] ?? null;
                $processedItem['metal_color'] = $item['metal_color'] ?? null;
                $processedItem['size'] = $item['size'] ?? null;
                $itemIds[] = $item['id'];
            }

            $totalQuantity += $item['quantity'];
            $processedItems[] = $processedItem;
        }

        // Determine product type
        if ($hasDiamond && $hasJewelry) {
            $data['product_type'] = 'mixed';
        } elseif ($hasJewelry) {
            $data['product_type'] = 'jewelry';
        } else {
            $data['product_type'] = 'diamond';
        }

        $data['items_id'] = $itemIds;
        $data['item_details'] = $processedItems;
        $data['order_id'] = 'ORD-' . now()->format('Ymd') . '-' . Str::random(4);
        $data['total_quantity'] = $totalQuantity;
        $data['payment_mode'] = 'cod';
        $data['payment_status'] = 'pending';
        $data['order_status'] = 'pending';

        Order::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully'
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
            'order_status' => 'sometimes|string',
        ]);

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully'
        ]);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }
}
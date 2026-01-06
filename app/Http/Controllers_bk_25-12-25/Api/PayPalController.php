<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use App\Http\Controllers\Controller;
use App\Models\Order;

class PayPalController extends Controller
{
    // Helper to get PayPal HTTP client
    private function paypalClient()
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.secret');
        $environment = new SandboxEnvironment($clientId, $clientSecret);
        return new PayPalHttpClient($environment);
    }

    // Create PayPal order
    public function createOrder(Request $request)
    {
        // Initialize PayPal client
        $client = $this->paypalClient();
        $pendingOrderId = $request->input('order_id');

        // Create order request
        $orderRequest = new OrdersCreateRequest();
        $orderRequest->prefer('return=representation');

        $orderRequest->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $request->input('amount') // default to 10.00 if not provided
                ],
                // Optional: attach reference id to identify order in PayPal
                'reference_id' => $pendingOrderId,
            ]],
            'application_context' => [
                'return_url' => route('paypal.capture', ['order_id' => $pendingOrderId]), // define this route in web.php
                'cancel_url' => route('paypal.cancel', ['order_id' => $pendingOrderId]),
            ]
        ];

        // Execute the request
        try {
            $response = $client->execute($orderRequest);
            $approveLink = collect($response->result->links)->firstWhere('rel', 'approve')->href;

            return response()->json([
                'orderID' => $response->result->id,
                'approve_url' => $approveLink
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function captureOrder(Request $request)
    // {
    //     $paypalOrderId = $request->query('token'); // PayPal sends token as "token"
    //     $dbOrderId = $request->query('order_id');

    //     if (!$dbOrderId) {
    //         return response('Order ID missing', 400);
    //     }

    //     $client = $this->paypalClient();
    //     $captureRequest = new OrdersCaptureRequest($paypalOrderId);
    //     $captureRequest->prefer('return=representation');

    //     try {
    //         $response = $client->execute($captureRequest);

    //         // Extract desired details
    //         $paypalOrderId = $response->result->id ?? '';
    //         $status = $response->result->status ?? '';
    //         $payerEmail = $response->result->payer->email_address ?? '';

    //         // Redirect to React with success and key data
    //         $order = Order::findOrFail($dbOrderId);
    //         $order->payment_status = 'completed';
    //         $order->order_status = 'processing';
    //         $order->paypal_order_id = $paypalOrderId;
    //         $order->payer_email = $payerEmail;
    //         $order->save();

    //         return redirect(env('FRONTEND_URL') . '/thankyou');

    //     } catch (\Exception $e) {
    //         // Log error if needed
    //          $order = Order::find($dbOrderId);
    //     if ($order) {
    //         $order->payment_status = 'failed';
    //         $order->order_status = 'cancelled';
    //         $order->save();
    //     }

    //     return redirect(env('FRONTEND_URL') . '/checkout?paypal_status=fail');
    //     }
    // }

    public function captureOrder(Request $request)
    {
        $paypalOrderId = $request->query('token'); // PayPal order ID
        $dbOrderId = $request->query('order_id');   // Laravel DB order ID

        if (!$paypalOrderId || !$dbOrderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing token or order_id in request'
            ], 400);
        }

        $client = $this->paypalClient();
        $captureRequest = new \PayPalCheckoutSdk\Orders\OrdersCaptureRequest($paypalOrderId);
        $captureRequest->prefer('return=representation');

        try {
            $response = $client->execute($captureRequest);

            // Update order in database
            $order = Order::where('order_id', $dbOrderId)->firstOrFail();
            $order->payment_status = 'completed';
            $order->order_status = 'processing';
            $order->paypal_order_id = $paypalOrderId;
            $order->payer_email = $response->result->payer->email_address ?? '';
            $order->save();
            return redirect()->away(env('FRONTEND_URL') . "/thankyou?order_id={$dbOrderId}");
            /* return response()->json([
                'status' => 'success',
                'message' => 'Payment captured successfully',
                'order' => $order,
                'paypal_response' => $response->result
            ]); */

        } catch (\Exception $e) {
            // Update order as failed
            $order = Order::find($dbOrderId);
            if ($order) {
                $order->payment_status = 'failed';
                $order->order_status = 'cancelled';
                $order->save();
            }

            return redirect()->away(env('FRONTEND_URL') . "/payment-failed?order_id={$dbOrderId}&error=" . urlencode($e->getMessage()));
            // Return JSON error
            /* return response()->json([
                'status' => 'error',
                'message' => 'PayPal capture failed',
                'error' => $e->getMessage()
            ], 500); */
        }
    }


    public function cancel(Request $request)
    {
        $dbOrderId = $request->query('order_id');
        $order = Order::find($dbOrderId);
        if ($order) {
            $order->payment_status = 'failed';
            $order->order_status = 'cancelled';
            $order->save();
        }

        return redirect()->away(env('FRONTEND_URL') . "/payment-failed?order_id={$dbOrderId}&error=" . urlencode("Payment cancelled by user"));
    }

}

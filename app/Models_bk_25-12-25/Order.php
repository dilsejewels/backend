<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'user_name',
        'contact_number',

        'items_id',
        'item_details',
        'total_price',
        'total_quantity',
        'shipping_cost',
        'discount',
        'coupon_code',
        'coupon_discount',
        'address',
        'billing_address',

        'payment_mode',
        'transaction_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'payment_status',
        'order_status',

        'paypal_order_id',
        'payer_email',

        'delivery_date',
        'cancelled_at',
        'cancellation_reason',
        'product_type',
        'certificate_number',
        'metal_type',
        'metal_color',
        'metal_purity',
        'stone_details',
        'size',
    ];

    protected $casts = [
        'items_id' => 'array',
        'item_details' => 'array',
        'address' => 'array',
        'billing_address' => 'array',
        'total_quantity' => 'integer',
        'cancelled_at' => 'datetime',
        'discount' => 'float',
        'coupon_discount' => 'float',
    ];

    protected $appends = [
        'formatted_address',
        'formatted_billing_address',
        'grand_total',
        'status_label',
        'product_type_label',
        'payment_mode_label',
        'cancellation_message',
        'can_be_cancelled',
        'payment_transaction_id',
        'formatted_delivery_date',
        'formatted_cancelled_date',
        'coupon_details',
    ];

    // Cancel order method
    public function cancel($reason = null)
    {
        if ($this->canBeCancelled()) {
            $updateData = [
                'order_status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'updated_at' => now()
            ];

            // Set payment status based on payment mode
            if ($this->payment_mode === 'cod') {
                $updateData['payment_status'] = 'cancelled';
            } else {
                // For online payments, process refund and set status to refunded
                $updateData['payment_status'] = 'refunded';
                $this->processRefund();
            }

            $this->update($updateData);

            return true;
        }

        return false;
    }

    // Check if order can be cancelled
    public function canBeCancelled()
    {
        $nonCancellableStatuses = ['delivered', 'shipped', 'cancelled'];
        return !in_array($this->order_status, $nonCancellableStatuses);
    }

    // Process refund
    protected function processRefund()
    {
        try {
            // Log refund initiation
            \Log::info("Refund initiated for order: {$this->order_id}, Amount: {$this->total_price}, Payment Mode: {$this->payment_mode}");
            
            // In production, integrate with actual payment gateways
            switch ($this->payment_mode) {
                case 'paypal':
                    $this->processPaypalRefund();
                    break;
                case 'card':
                case 'upi':
                case 'netbanking':
                    $this->processRazorpayRefund();
                    break;
                default:
                    \Log::info("Refund processed for order: {$this->order_id}");
                    break;
            }
            
        } catch (\Exception $e) {
            \Log::error("Refund processing failed for order {$this->order_id}: " . $e->getMessage());
        }
    }

    // PayPal refund
    protected function processPaypalRefund()
    {
        \Log::info("PayPal refund processed for order: {$this->order_id}");
    }

    // Razorpay refund
    protected function processRazorpayRefund()
    {
        \Log::info("Razorpay refund processed for order: {$this->order_id}");
    }

    // Automatically complete payment when delivered
    public function markAsDelivered()
    {
        $updateData = [
            'order_status' => 'delivered',
            'delivery_date' => now(),
            'updated_at' => now()
        ];

        // For COD orders, mark payment as completed upon delivery
        if ($this->payment_mode === 'cod') {
            $updateData['payment_status'] = 'paid';
        }

        $this->update($updateData);
    }

    // Admin method to mark as shipped
    public function markAsShipped()
    {
        if ($this->order_status === 'confirmed' || $this->order_status === 'pending') {
            $this->update([
                'order_status' => 'shipped',
                'updated_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for formatted address
    public function getFormattedAddressAttribute(): string
    {
        $address = $this->address;

        if (!is_array($address)) {
            $address = json_decode($address, true) ?? [];
        }

        if (is_array($address) && !empty($address)) {
            $parts = [];
            
            // Check different possible address structures
            if (isset($address['first_name']) || isset($address['name'])) {
                $parts[] = $address['first_name'] . ' ' . ($address['last_name'] ?? '');
            }
            
            if (isset($address['apartment']) && !empty($address['apartment'])) {
                $parts[] = $address['apartment'];
            }
            
            $streetParts = [];
            if (isset($address['address']) && !empty($address['address'])) {
                $streetParts[] = $address['address'];
            }
            if (isset($address['street']) && !empty($address['street'])) {
                $streetParts[] = $address['street'];
            }
            if (isset($address['address_line1']) && !empty($address['address_line1'])) {
                $streetParts[] = $address['address_line1'];
            }
            if (!empty($streetParts)) {
                $parts[] = implode(', ', $streetParts);
            }
            
            if (isset($address['city']) && !empty($address['city'])) {
                $parts[] = $address['city'];
            } elseif (isset($address['locality']) && !empty($address['locality'])) {
                $parts[] = $address['locality'];
            }
            
            if (isset($address['state']) && !empty($address['state'])) {
                $parts[] = $address['state'];
            } elseif (isset($address['administrative_area']) && !empty($address['administrative_area'])) {
                $parts[] = $address['administrative_area'];
            }
            
            if (isset($address['country']) && !empty($address['country'])) {
                $parts[] = $address['country'];
            }
            
            $postalParts = [];
            if (isset($address['zip_code']) && !empty($address['zip_code'])) {
                $postalParts[] = $address['zip_code'];
            }
            if (isset($address['zip']) && !empty($address['zip'])) {
                $postalParts[] = $address['zip'];
            }
            if (isset($address['postal_code']) && !empty($address['postal_code'])) {
                $postalParts[] = $address['postal_code'];
            }
            if (isset($address['pincode']) && !empty($address['pincode'])) {
                $postalParts[] = $address['pincode'];
            }
            if (!empty($postalParts)) {
                $parts[] = implode(' - ', $postalParts);
            }
            
            $formattedAddress = implode(', ', array_filter($parts, function ($value) {
                return !empty($value) && trim($value) !== '';
            }));
            
            if (!empty($formattedAddress)) {
                return $formattedAddress;
            }
        }

        // Fallback if address is a string
        if (is_string($this->address) && !empty($this->address)) {
            return $this->address;
        }

        return 'No address provided';
    }

    // Accessor for formatted billing address
    public function getFormattedBillingAddressAttribute(): string
    {
        $address = $this->billing_address;

        if (!is_array($address)) {
            $address = json_decode($address, true) ?? [];
        }

        if (is_array($address) && !empty($address)) {
            $parts = [];
            
            if (isset($address['first_name']) || isset($address['name'])) {
                $parts[] = $address['first_name'] . ' ' . ($address['last_name'] ?? '');
            }
            
            if (isset($address['apartment']) && !empty($address['apartment'])) {
                $parts[] = $address['apartment'];
            }
            
            $streetParts = [];
            if (isset($address['address']) && !empty($address['address'])) {
                $streetParts[] = $address['address'];
            }
            if (isset($address['street']) && !empty($address['street'])) {
                $streetParts[] = $address['street'];
            }
            if (isset($address['address_line1']) && !empty($address['address_line1'])) {
                $streetParts[] = $address['address_line1'];
            }
            if (!empty($streetParts)) {
                $parts[] = implode(', ', $streetParts);
            }
            
            if (isset($address['city']) && !empty($address['city'])) {
                $parts[] = $address['city'];
            } elseif (isset($address['locality']) && !empty($address['locality'])) {
                $parts[] = $address['locality'];
            }
            
            if (isset($address['state']) && !empty($address['state'])) {
                $parts[] = $address['state'];
            } elseif (isset($address['administrative_area']) && !empty($address['administrative_area'])) {
                $parts[] = $address['administrative_area'];
            }
            
            if (isset($address['country']) && !empty($address['country'])) {
                $parts[] = $address['country'];
            }
            
            $postalParts = [];
            if (isset($address['zip_code']) && !empty($address['zip_code'])) {
                $postalParts[] = $address['zip_code'];
            }
            if (isset($address['zip']) && !empty($address['zip'])) {
                $postalParts[] = $address['zip'];
            }
            if (isset($address['postal_code']) && !empty($address['postal_code'])) {
                $postalParts[] = $address['postal_code'];
            }
            if (isset($address['pincode']) && !empty($address['pincode'])) {
                $postalParts[] = $address['pincode'];
            }
            if (!empty($postalParts)) {
                $parts[] = implode(' - ', $postalParts);
            }
            
            $formattedAddress = implode(', ', array_filter($parts, function ($value) {
                return !empty($value) && trim($value) !== '';
            }));
            
            if (!empty($formattedAddress)) {
                return $formattedAddress;
            }
        }

        // If billing address is same as shipping address
        if (empty($address) || $address === $this->address) {
            return $this->formatted_address;
        }

        // Fallback if address is a string
        if (is_string($this->billing_address) && !empty($this->billing_address)) {
            return $this->billing_address;
        }

        return 'Same as shipping address';
    }

    // Get cancellation eligibility
    public function getCanBeCancelledAttribute(): bool
    {
        return $this->canBeCancelled();
    }

    // Get cancellation eligibility message
    public function getCancellationMessageAttribute(): string
    {
        if ($this->order_status === 'delivered') {
            return "This order has been delivered and cannot be cancelled";
        }
        if ($this->order_status === 'shipped') {
            return "This order has been shipped and cannot be cancelled";
        }
        if ($this->order_status === 'cancelled') {
            return "This order has already been cancelled";
        }
        return "You can cancel this order";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->order_status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
            default => ucfirst($this->order_status),
        };
    }

    public function getProductTypeLabelAttribute(): string
    {
        return match($this->product_type) {
            'diamond' => 'Diamond',
            'jewelry' => 'Jewelry',
            'gift' => 'Gift',
            'mixed' => 'Mixed',
            'combo' => 'Combo',
            default => ucfirst($this->product_type),
        };
    }

    public function getPaymentModeLabelAttribute(): string
    {
        return match ($this->payment_mode) {
            'cash' => 'Cash',
            'card' => 'Card',
            'upi' => 'UPI',
            'netbanking' => 'Net Banking',
            'paypal' => 'PayPal',
            'cod' => 'Cash on Delivery',
            default => ucfirst($this->payment_mode),
        };
    }

    public function getFormattedDeliveryDateAttribute(): ?string
    {
        return $this->delivery_date
            ? date('d-m-Y', strtotime($this->delivery_date))
            : null;
    }

    public function getFormattedCancelledDateAttribute(): ?string
    {
        return $this->cancelled_at
            ? date('d-m-Y h:i A', strtotime($this->cancelled_at))
            : null;
    }

    // Grand total - ONLY add shipping cost
    public function getGrandTotalAttribute(): float
    {
        return $this->total_price + $this->shipping_cost;
    }

    // Get payment transaction ID based on payment mode
    public function getPaymentTransactionIdAttribute(): ?string
    {
        return match($this->payment_mode) {
            'paypal' => $this->paypal_order_id,
            'razorpay' => $this->razorpay_payment_id,
            default => $this->transaction_id,
        };
    }

    // Get coupon details
    public function getCouponDetailsAttribute(): string
    {
        if ($this->coupon_code && $this->coupon_discount > 0) {
            return "{$this->coupon_code} (-$" . number_format($this->coupon_discount, 2) . ")";
        }
        return 'No coupon';
    }

    // Get address email
    public function getAddressEmailAttribute(): ?string
    {
        $address = $this->address;
        if (!is_array($address)) {
            $address = json_decode($address, true) ?? [];
        }
        return $address['email'] ?? null;
    }

    // Get address phone
    public function getAddressPhoneAttribute(): ?string
    {
        $address = $this->address;
        if (!is_array($address)) {
            $address = json_decode($address, true) ?? [];
        }
        return $address['phone'] ?? $address['phone_number'] ?? $this->contact_number;
    }
}
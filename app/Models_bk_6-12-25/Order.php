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
        'payment_mode',
        'payment_id',
        'payment_status',
        'order_status',
        'paypal_order_id',
        'payer_email',
        'delivery_date',
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
        'total_quantity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            default => ucfirst($this->payment_mode),
        };
    }

    public function getFormattedAddressAttribute(): string
    {
        $address = $this->address;

        if (!is_array($address)) {
            $address = json_decode($address, true) ?? [];
        }

        $parts = [
            $address['apartment'] ?? '',
            $address['street'] ?? $address['address_line1'] ?? '',
            $address['city'] ?? $address['locality'] ?? '',
            $address['state'] ?? $address['administrative_area'] ?? '',
            $address['country'] ?? '',
            $address['zip'] ?? $address['postal_code'] ?? $address['pincode'] ?? '',
        ];

        return implode(', ', array_filter($parts, function ($value) {
            return !empty($value);
        }));
    }

    public function getFormattedDeliveryDateAttribute(): ?string
    {
        return $this->delivery_date
            ? date('d-m-Y', strtotime($this->delivery_date))
            : null;
    }

    public function getGrandTotalAttribute(): float
    {
        return $this->total_price + $this->shipping_cost - $this->discount;
    }
} 
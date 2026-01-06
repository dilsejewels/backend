<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'type', 
        'value', 
        'min_cart_value',
        'max_discount', 
        'valid_from', 
        'valid_until',
        'usage_limit', 
        'used_count', 
        'is_active'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Check if coupon is valid
    public function isValid()
    {
        $now = now();
        return $this->is_active && 
               $now->between($this->valid_from, $this->valid_until) &&
               $this->used_count < $this->usage_limit;
    }

    // Calculate discount
    public function calculateDiscount($cartTotal)
    {
        if ($cartTotal < $this->min_cart_value) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return min($this->value, $cartTotal);
        }

        // For percentage type
        $discount = ($cartTotal * $this->value) / 100;
        
        if ($this->max_discount) {
            return min($discount, $this->max_discount);
        }

        return $discount;
    }
}
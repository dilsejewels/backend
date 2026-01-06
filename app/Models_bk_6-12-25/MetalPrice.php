<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'metal_type',
        'metal_quality',
        'price_per_gram',
        'price_per_10gram'
    ];

    protected $casts = [
        'date' => 'date',
        'price_per_gram' => 'decimal:2',
        'price_per_10gram' => 'decimal:2'
    ];

    public function metalType()
    {
        return $this->belongsTo(MetalType::class, 'metal_type', 'dmt_name');
    }

    public function scopeLatestPrices($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('metal_prices')
                ->groupBy('metal_type', 'metal_quality');
        });
    }
}

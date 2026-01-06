<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopTaxRate extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'tax_rates_id';
    protected $table = 'shop_tax_rates';
    public $timestamps = false;
    
    protected $fillable = [
        'tax_zone_id',
        'tax_class_id',
        'tax_priority',
        'tax_rate',
        'tax_description',
        'date_added',
        'date_modified',
        'added_by',
        'updated_by',
        'tax_retailer_id'
    ];
    
    public function taxZone()
    {
        return $this->belongsTo(ShopZone::class, 'tax_zone_id', 'zone_id');
    }
    
    public function taxClass()
    {
        return $this->belongsTo(ShopTaxClass::class, 'tax_class_id', 'tax_class_id');
    }
}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsToMetalType extends Model
{
    protected $table = 'products_to_metal_type';
    protected $primaryKey = 'sptmt_id';

    public $timestamps = false;

    protected $fillable = [
        'sptmt_products_id',
        'sptmt_metal_type_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'sptmt_products_id', 'products_id');
    }

    public function metalType()
    {
        return $this->belongsTo(MetalType::class, 'sptmt_metal_type_id', 'dmt_id');
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductToStoneType extends Model
{
    protected $table = 'products_to_stone_type';
    protected $primaryKey = 'sptst_id';
    public $timestamps = false;

    protected $fillable = [
        'sptst_products_id',
        'sptst_stone_type_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'sptst_products_id', 'products_id');
    }

    public function stoneType()
    {
        return $this->belongsTo(ProductStoneType::class, 'sptst_stone_type_id', 'pst_id');
    }
}
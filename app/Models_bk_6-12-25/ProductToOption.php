<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductToOption extends Model
{
    protected $table = 'products_to_options';
    protected $primaryKey = 'products_to_option_id';
    public $timestamps = false;

    protected $fillable = [
        'products_id', 'options_id', 'value_id',
        'options_price', 'options_symbol', 'estimated_weight',
        'estimated_symbol', 'sort_order', 'option_image'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'products_id');
    }

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'options_id', 'options_id');
    }

    public function value()
    {
        return $this->belongsTo(ProductOptionValue::class, 'value_id', 'value_id');
    }
}

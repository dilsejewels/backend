<?php
// app/Models/ProductToStyleCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductToStyleCategory extends Model
{
    use HasFactory;

    protected $table = 'products_to_style_category';
    protected $primaryKey = 'sptsc_id';
    public $timestamps = false;
    protected $fillable = ['sptsc_products_id', 'sptsc_style_category_id'];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'sptsc_products_id', 'products_id');
    }

    public function styleCategory()
    {
        return $this->belongsTo(\App\Models\ProductStyleCategory::class, 'sptsc_style_category_id', 'psc_id');
    }
}

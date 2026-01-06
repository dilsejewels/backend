<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductToCategory extends Model
{
    use HasFactory;

    protected $table = 'products_to_categories';
    public $incrementing = false;
    protected $primaryKey = ['products_id', 'categories_id']; // Define composite key

    protected $fillable = [
        'products_id',
        'categories_id',
    ];

    // Add this method to handle composite keys
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('products_id', $this->getAttribute('products_id'))
                    ->where('categories_id', $this->getAttribute('categories_id'));
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'products_id', 'products_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'categories_id', 'category_id');
    }
}
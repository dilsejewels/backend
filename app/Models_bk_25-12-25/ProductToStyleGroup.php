<?php

// app/Models/ProductToStyleGroup.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductToStyleGroup extends Model
{
    protected $table = 'products_to_style_group';
    protected $primaryKey = 'sptsg_id';
    public $timestamps = false;

    protected $fillable = [
        'sptsg_products_id',
        'sptsg_style_category_id'
    ];
}

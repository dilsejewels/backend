<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsColorMaster extends Model
{
    protected $table = 'products_color_master';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'short_name',
        'alias',
        'remark',
        'display_in_front',
        'sort_order',
        'date_added',
        'date_modify',
    ];
}

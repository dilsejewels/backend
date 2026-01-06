<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStoneType extends Model
{
    protected $table = 'products_stone_type';
    protected $primaryKey = 'pst_id';
    public $timestamps = false;

    protected $fillable = [
        'pst_category_id',
        'pst_name',
        'pst_alias',
        'pst_description',
        'pst_image',
        'pst_status',
        'pst_sort_order',
        'pst_display_in_front',
        'date_added',
        'date_modified',
        'added_by',
        'updated_by',
    ];
}

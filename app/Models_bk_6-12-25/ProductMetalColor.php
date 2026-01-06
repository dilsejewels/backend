<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMetalColor extends Model
{
    protected $table = 'products_metal_color';
    protected $primaryKey = 'dmc_id';
    public $timestamps = false;

    protected $fillable = [
        'dmc_name',
        'dmc_status',
        'added_by',
        'date_added',
        'updated_by',
        'date_modified',
    ];
}

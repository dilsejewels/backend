<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $table = 'products_options';
    protected $primaryKey = 'options_id';
    public $timestamps = false;

    protected $fillable = [
        'options_name',
        'options_type',
        'sort_order',
        'date_added',
        'is_compulsory',
        'added_by',
        'date_modified',
        'updated_by',
    ];
}

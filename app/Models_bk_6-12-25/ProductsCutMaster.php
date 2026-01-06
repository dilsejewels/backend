<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsCutMaster extends Model
{
    protected $table = 'products_cut_master';
    protected $primaryKey = 'id';
    public $timestamps = false; 

    protected $fillable = [
        'name',
        'alias',
        'shortname',
        'remark',
        'display_in_front',
        'sort_order',
        'date_added',
        'date_modify',
    ];
}

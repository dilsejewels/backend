<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductClarityMaster extends Model
{
    use HasFactory;

    protected $table = 'products_clarity_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'alias',
        'remark',
        'display_in_front',
        'sort_order',
        'date_added',
        'date_modify',
    ];
}

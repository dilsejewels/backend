<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondShade extends Model
{
    use HasFactory;
    protected $table = 'diamond_shade_master';
    protected $primaryKey = 'ds_id';
    public $timestamps = false;

    protected $fillable = [
        'ds_name',
        'ds_short_name',
        'ds_alise',
        'ds_remark',
        'ds_display_in_front',
        'ds_sort_order',
        'date_added',
        'date_modify',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondCulet extends Model
{
    use HasFactory;
    protected $table='diamond_culet_master';
    protected $primaryKey = 'dc_id';
    public $timestamps=false;

    protected $fillable=[
        'dc_name',
        'dc_short_name',
        'dc_alise',
        'dc_remark',
        'dc_display_in_front',
        'dc_sort_order',
        'date_added',
        'date_modify',
    ];
}

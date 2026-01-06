<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondFancyColor extends Model
{
    use HasFactory;
    protected $table='diamond_fancycolor_overtones_master';
    protected $primaryKey='fco_id';
    public $timestamps= false;

    protected  $fillable=[
        'fco_name',
        'fco_short_name',
        'fco_alise',
        'fco_remark',
        'fco_display_in_front',
        'fco_sort_order',
        'date_added',
        'date_modify',
    ];
}

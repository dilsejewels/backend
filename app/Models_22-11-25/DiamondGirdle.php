<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondGirdle extends Model
{
    use HasFactory;
    protected $table='diamond_girdle_master';
    protected $primaryKey = 'dg_id';
    public $timestamps=false;

    protected $fillable=[
        'dg_name',
        'dg_short_name',
        'dg_alise',
        'dg_remark',
        'dg_display_in_front',
        'dg_sort_order',
        'date_added',
        'date_modify',
    ];
}

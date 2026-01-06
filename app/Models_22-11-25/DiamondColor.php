<?php

namespace App\Models;

use App\Models\DiamondMaster;
use Illuminate\Database\Eloquent\Model;

class DiamondColor extends Model
{
    protected $table = 'diamond_color_master';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'short_name',
        'ALIAS',
        'remark',
        'display_in_front',
        'dc_is_fancy_color',
        'sort_order',
        'date_added',
        'date_modify',
    ];

    public function diamondMasters()
    {
        return $this->hasMany(DiamondMaster::class);
    }
}

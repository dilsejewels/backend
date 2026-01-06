<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopZone extends Model
{
    protected $primaryKey = 'zone_id';
    protected $fillable = [
        'zone_country_id', 'zone_code', 'zone_name', 'added_by', 'updated_by'
    ];
    public $timestamps = false;
}

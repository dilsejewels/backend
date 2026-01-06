<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopZonesToGeoZone extends Model
{
    protected $table = 'shop_zones_to_geo_zones';

    protected $primaryKey = 'association_id';
    public $timestamps = false;

    protected $fillable = [
        'country_id', 'zone_id', 'geo_zone_id', 
        'created_by', 'updated_by'
    ];
}

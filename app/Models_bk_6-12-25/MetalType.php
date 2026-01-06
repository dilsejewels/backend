<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetalType extends Model
{
    protected $table = 'metal_type';
    protected $primaryKey = 'dmt_id';
    public $timestamps = false;

    protected $fillable = [
        'dmt_name', 'dmt_tooltip', 'dmt_status', 'sort_order',
        'color_code', 'metal_icon', 'added_by', 'date_added',
        'updated_by', 'date_modified'
    ];

    public function productVariations()
    {
        return $this->hasMany(ProductVariation::class, 'metal_color_id', 'dmt_id');
    }
}

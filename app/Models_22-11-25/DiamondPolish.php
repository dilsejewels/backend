<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiamondPolish extends Model
{
    protected $table = 'diamond_polish_master';
    protected $fillable = [
        'name', 'alias', 'short_name', 'full_name', 'pol_status', 'sort_order', 'date_added', 'date_modify'
    ];
    public $timestamps = false;
    public function diamondMasters()
    {
        return $this->hasMany(DiamondMaster::class);
    }
}


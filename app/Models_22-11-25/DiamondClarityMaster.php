<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiamondClarityMaster extends Model
{
    // Is model se associated table ka naam
    protected $table = 'diamond_clarity_master';

    // Mass assignable fields
    protected $fillable = [
        'name',
        'ALIAS',
        'remark',
        'display_in_front',
        'sort_order',
        'date_added',
        'date_modify'
    ];

    // Agar aapke migration me timestamps nahi hain, to isse false set karein
    public $timestamps = false;

    public function diamondMasters()
    {
        return $this->hasMany(DiamondMaster::class);
    }
}


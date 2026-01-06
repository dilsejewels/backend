<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DiamondMaster;

class DiamondSymmetry extends Model
{
    use HasFactory;
    protected $table = 'diamond_symmetry_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'alias',
        'short_name',
        'full_name',
        'sym_ststus',
        'sort_order',
        'date_added',
        'date_modify',
    ];
    
    public function diamondMasters()
    {
        return $this->hasMany(DiamondMaster::class);
    }
}

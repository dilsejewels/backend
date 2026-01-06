<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DiamondMaster;

class DiamondCut extends Model
{
    use HasFactory;

    protected $table = 'diamond_cut_master';
    protected $primaryKey='id';
    public $timestamps=false;
    protected $fillable = [
        'name',
        'shortname',
        'full_name',
        'ALIAS',
        'remark',
        'display_in_front',
        'sort_order',
        'date_added',
        'date_modify',
    ];

    public function diamondMasters()
    {
        return $this->hasMany(DiamondMaster::class);
    }
}

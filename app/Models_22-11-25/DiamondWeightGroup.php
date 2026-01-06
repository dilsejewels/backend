<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondWeightGroup extends Model
{
    use HasFactory;

    protected $table = 'diamond_weight_group';
    protected $primaryKey = 'dwg_id';
    
    protected $fillable = [
        'dwg_name',
        'dwg_from',
        'dwg_to',
        'dwg_status',
        'dwg_sort_order',
        'added_by',
        'updated_by',
        'date_added',
        'date_updated'
    ];
}
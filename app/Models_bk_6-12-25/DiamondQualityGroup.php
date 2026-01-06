<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondQualityGroup extends Model
{
    use HasFactory;

    protected $table = 'diamond_quality_group';
    protected $primaryKey = 'dqg_id';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'dqg_name',
        'dqg_alias',
        'dqg_short_name',
        'description',
        'dqg_icon',
        'dqg_sort_order',
        'dqg_status',
        'dqg_origin',
        'added_by',
        'date_added',
        'updated_by',
        'date_modified',
    ];
}

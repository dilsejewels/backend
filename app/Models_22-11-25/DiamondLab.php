<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondLab extends Model
{
    use HasFactory;

    protected $table = 'diamond_lab_master';

    protected $primaryKey = 'dl_id';

    protected $fillable = [
        'dl_name',
        'dl_display_in_front',
        'dl_sort_order',
        'image',
        'cert_url',
        'date_added',
        'date_modify'
    ];

    public $timestamps = false;
}

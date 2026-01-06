<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondFancyColorIntensity extends Model
{
    use HasFactory;

    protected $table = 'diamond_fancycolor_intensity_master';
    protected $primaryKey = 'fci_id';
    public $timestamps = false;

    protected $fillable = [
        'fci_name',
        'fci_short_name',
        'fci_alias',
        'fci_remark',
        'fci_display_in_front',
        'fci_sort_order',
        'date_added',
        'date_modify'
    ];
}
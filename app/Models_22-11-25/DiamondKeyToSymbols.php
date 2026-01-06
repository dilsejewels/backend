<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondKeyToSymbols extends Model
{
    use HasFactory;

    protected $table = 'diamond_key_to_symbols_master';

    protected $fillable = [
        'name',
        'alias',
        'short_name',
        'sym_status',
        'sort_order',
        'date_added',
        'date_modify'
    ];

    public $timestamps = false;
}

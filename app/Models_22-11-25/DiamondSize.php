<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiamondSize extends Model
{
    use HasFactory;
    protected $table = 'diamond_size_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'size1',
        'size2',
        'retailer_id',
        'status',
        'sort_order',
        'date_added',
        'date_updated',
        'added_by',
        'updated_by'
    ];
}

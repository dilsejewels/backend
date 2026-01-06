<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    use HasFactory;

    protected $table = 'products_options_values';
    protected $primaryKey = 'value_id';
    public $timestamps = false; 

    protected $fillable = [
        'options_id',
        'value_name',
        'sort_order',
        'date_added',
        'added_by',
        'date_modified',
        'updated_by',
    ];

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'options_id');
    }

}

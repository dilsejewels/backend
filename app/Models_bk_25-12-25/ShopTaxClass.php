<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopTaxClass extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'tax_class_id';
    protected $table = 'shop_tax_classes';
    public $timestamps = false;
    
    protected $fillable = [
        'tax_class_title',
        'tax_class_description',
        'date_added',
        'date_modified',
        'added_by',
        'updated_by'
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductToShape extends Model
{
    use HasFactory;

    protected $table = 'products_to_shape';
    protected $primaryKey = 'pts_id';
    public $timestamps = false;

    protected $fillable = ['products_id', 'shape_id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'products_id');
    }

    public function shape()
    {
        return $this->belongsTo(DiamondShape::class, 'shape_id', 'id');
    }
}

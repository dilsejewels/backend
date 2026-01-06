<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $table = 'products_image';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'products_id',
        'image_path',
        'is_featured'
    ];

    protected static function booted()
    {
        static::deleted(function ($image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }); 
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'products_id');
    }
}
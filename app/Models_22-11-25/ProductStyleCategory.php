<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStyleCategory extends Model
{
    protected $table = 'products_style_category';
    protected $primaryKey = 'psc_id';
    public $timestamps = false;

    protected $fillable = [
        'parent_category_id',
        'psc_category_id',
        'psc_name',
        'psc_image',
        'engagement_menu',
        'banner_image',
        'psc_status',
        'psc_sort_order',
        'psc_alias',
        'psc_display_in_front',
        'date_added',
        'date_modified',
        'added_by',
        'updated_by',
    ];

    protected $appends = ['image_url', 'banner_image_url']; // Added banner accessor

    public function getImageUrlAttribute()
    {
        if ($this->psc_image && file_exists(public_path('uploads/product_style_category/' . $this->psc_image))) {
            return url('uploads/product_style_category/' . $this->psc_image);
        }
        return null;
    }

    // Banner image accessor
    public function getBannerImageUrlAttribute()
    {
        if ($this->banner_image && file_exists(public_path('uploads/product_style_category/banner/' . $this->banner_image))) {
            return url('uploads/product_style_category/banner/' . $this->banner_image);
        }
        return null;
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_category_id', 'category_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'psc_category_id', 'category_id');
    }
}
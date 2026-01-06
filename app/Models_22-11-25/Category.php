<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $primaryKey = 'category_id';
    protected $table = 'categories';
    public $incrementing = true; 

    protected $fillable = [
        'parent_id',  
        'category_name',
        'category_alias',
        'category_description',
        'is_display_front',
        'category_image',
        'category_header_banner',
        'category_status',
        'seo_url',
        'category_meta_title',
        'category_meta_description',
        'category_meta_keyword',
        'category_h1_tag',
        'sort_order',
        'deleted',
        'category_date_added',
        'category_date_modified',
        'added_by',
        'updated_by',
        'product_type'
    ];

    protected $casts = [
        'category_date_added' => 'datetime',
        'category_date_modified' => 'datetime',
        'is_display_front' => 'boolean',
        'category_status' => 'boolean',
        'deleted' => 'boolean',
        'product_type' => 'array',
    ];

    // Accessor to handle JSON string to array conversion
    public function getProductTypeAttribute($value)
    {
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                return [];
            }
        }
        
        return $value ?? [];
    }

    // Mutator to ensure it's stored as JSON
    public function setProductTypeAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['product_type'] = json_encode(array_values($value));
        } else if (is_string($value) && !empty($value)) {
            // If it's already JSON string, store as is
            $this->attributes['product_type'] = $value;
        } else {
            $this->attributes['product_type'] = null;
        }
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'category_id');
    }
    
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'category_id');
    }
    
    public function styleCategories()
    {
        return $this->hasMany(ProductStyleCategory::class, 'psc_category_id', 'category_id');
    }

    // Helper method to get product type names
    public function getProductTypeNamesAttribute()
    {
        $productTypes = [
            '0' => 'Jewelry',
            '1' => 'Engagement',
            '2' => 'Wedding',
            '3' => 'Gifts',
            '4' => 'Sale'
        ];

        $types = $this->product_type;
        
        if (empty($types) || !is_array($types)) {
            return [];
        }

        return array_map(function($type) use ($productTypes) {
            $typeStr = (string)$type;
            return $productTypes[$typeStr] ?? $type;
        }, $types);
    }
}
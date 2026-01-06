<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCollection extends Model
{
    protected $table = 'product_collections';
    public $timestamps = false;

    protected $fillable = [
        'product_type',
        'product_category_ids', 
        'parent_category_ids',
        'name',
        'collection_image',
        'heading',
        'description',
        'banner_image',
        'banner_video',
        'status',
        'sort_order',
        'alias',
        'display_in_menu',
        'date_added',
        'date_modified',
        'added_by',
        'updated_by',
    ];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getProductTypeNamesAttribute()
    {
        if (!$this->product_type) return [];
        
        $typeNames = [
            0 => 'Jewelry',
            1 => 'Engagement',
            2 => 'Wedding',
            3 => 'Gifts',
            4 => 'Sale'
        ];
        
        $types = json_decode($this->product_type);
        return array_map(function($type) use ($typeNames) {
            return $typeNames[$type] ?? 'Unknown';
        }, $types);
    }
}
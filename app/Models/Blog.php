<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'image',
        'slug',
        'paragraph',
        'writer_name',
        'read_time',
        'publish_date'
    ];

    protected $casts = [
        'publish_date' => 'date',
    ];

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/blogs/' . $this->image) : null;
    }

    /**
     * Get the image path
     */
    public function getImagePathAttribute()
    {
        return $this->image ? storage_path('app/public/blogs/' . $this->image) : null;
    }
}
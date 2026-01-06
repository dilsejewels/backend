<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStyleGroup extends Model
{
    protected $table = 'products_style_group';
    protected $primaryKey = 'psg_id';
    public $timestamps = false;
    
    protected $fillable = [
        'collection_id',
        'psg_names',
        'psg_image',
        'psg_status',
        'psg_sort_order',
        'psg_alias',
        'psg_display_in_front',
        'date_added',
        'date_modified',
        'added_by',
        'updated_by'
    ];
    
    protected $casts = [
        'psg_names' => 'array', 
    ];

    
    public function collection()
    {
        return $this->belongsTo(ProductCollection::class, 'collection_id');
    }
}
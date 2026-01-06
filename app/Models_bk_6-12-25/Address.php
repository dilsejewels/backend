<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'country',
        'address',
        'phone_number',
        'is_get_offer',
    ];

    protected $casts = [
        'address' => 'array',         // JSON data
        'is_get_offer' => 'boolean',  // 1 or 0 as true/false
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
}

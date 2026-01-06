<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $table = 'contact_us';

    protected $fillable = [
        'name',
        'email',
        'phone', 
        'topic',
        'question'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Responses relationship
    public function responses()
    {
        return $this->hasMany(ContactUsResponse::class, 'contact_us_id');
    }

    // Latest response
    public function latestResponse()
    {
        return $this->hasOne(ContactUsResponse::class, 'contact_us_id')->latest();
    }

    // Check if responded
    public function getIsRespondedAttribute()
    {
        return $this->responses()->exists();
    }
}
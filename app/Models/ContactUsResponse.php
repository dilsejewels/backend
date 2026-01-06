<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsResponse extends Model
{
    use HasFactory;

    protected $table = 'contact_us_responses';

    protected $fillable = [
        'contact_us_id',
        'responded_by',
        'message'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Contact us relationship
    public function contactUs()
    {
        return $this->belongsTo(ContactUs::class, 'contact_us_id');
    }

    // User who responded
    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
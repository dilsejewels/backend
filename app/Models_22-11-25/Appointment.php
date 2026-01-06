<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'service_id',
        'name',
        'contact_number',
        'email',
        'appointment_date',
        'appointment_time',
        'appointment_type',
        'location',
        'meeting_link',
        'status',
        'time_zone',
        'today_time',
        'guest_email',
        'category',
        'other_category',
        'additional_information'
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];
}
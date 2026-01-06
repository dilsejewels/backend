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
        'address' => 'array',
        'is_get_offer' => 'boolean',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
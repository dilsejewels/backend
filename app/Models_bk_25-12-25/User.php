<?php

// namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

// class User extends Authenticatable implements MustVerifyEmail
// {
//     use HasApiTokens, HasFactory, Notifiable;

//     /**
//      * The attributes that are mass assignable.
//      *
//      * @var array<int, string>
//      */
//     protected $fillable = [
//         'name',
//         'email',
//         'password',
//         'user_type',
//         'title',
//         'dob',                
//         'anniversary_date',
//     ];

//     /**
//      * The attributes that should be hidden for serialization.
//      *
//      * @var array<int, string>
//      */
//     protected $hidden = [
//         'password',
//         'remember_token',
//     ];

//     /**
//      * The attributes that should be cast.
//      *
//      * @var array<string, string>
//      */
//     protected $casts = [
//         'email_verified_at' => 'datetime',
//         'dob' => 'date:Y-m-d',
//         'anniversary_date' => 'date:Y-m-d',
//     ]; 
//      public function addresses()
//     {
//         return $this->hasMany(Address::class);
//     }
// }


namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'title',
        'dob',                
        'anniversary_date',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date:Y-m-d',
        'anniversary_date' => 'date:Y-m-d',
    ]; 

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        
        // For images stored in storage
        return asset('storage/' . $this->image);
    }

    /**
     * Append the image URL to the model
     */
    protected $appends = ['image_url'];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $primaryKey = 'country_id';

    protected $fillable = [
        'country_name',
        'iso_code_2',
        'iso_code_3',
        'phone_code',
        'is_active',
    ];

    public $timestamps = true;
}

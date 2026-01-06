<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewShare extends Model
{
    use HasFactory;

    protected $fillable = ['review_id', 'user_id', 'guest_identifier', 'platform'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
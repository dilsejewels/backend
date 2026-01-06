<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAssignOption extends Model
{
    protected $table = 'products_assign_options';

    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'products_id',
        'options_id'
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoryId',
        'name',
        'price',
        'amount',
        'description' ,
        'wood',
        'woodFinish',
    ];

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function galleries()
    {
        return $this->hasMany('App\Models\Gallery');
    }
}

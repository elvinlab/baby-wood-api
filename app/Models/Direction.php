<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;

    protected $table='directions';

    protected $fillable = [
        'clientId','country','province','city','zipCode','streetAddress'
    ];

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

}
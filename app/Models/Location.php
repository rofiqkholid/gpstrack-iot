<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'speed',
    ];
}

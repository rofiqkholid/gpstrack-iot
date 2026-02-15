<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSchedule extends Model
{
    protected $fillable = [
        'vehicle_type',
        'component',
        'interval_km',
        'description',
    ];

    public function scopeForType($query, string $type)
    {
        return $query->where('vehicle_type', $type);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRecordItem extends Model
{
    protected $fillable = [
        'service_record_id',
        'component_name',
        'description',
        'cost',
    ];

    public function serviceRecord()
    {
        return $this->belongsTo(ServiceRecord::class);
    }
}

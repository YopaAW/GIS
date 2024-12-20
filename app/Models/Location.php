<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'description',
        'risk_level',
        'address',
        'district',
        'city',
        'incident_time',
        'incident_count',
        'image_path',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'incident_time' => 'datetime',
        'is_active' => 'boolean',
        'incident_count' => 'integer'
    ];
}

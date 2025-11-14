<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile',
        'password',
        'license_number',
        'driver_type',
        'own_vehicle_id',
        'status',
        'address',
        'emergency_contact',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relationships
    public function ownVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'own_vehicle_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    // Helper methods
    public function isAvailable()
    {
        return $this->status === 'active' && !$this->hasActiveTrip();
    }

    public function hasActiveTrip()
    {
        return $this->trips()
            ->whereIn('status', ['assigned', 'in_transit'])
            ->exists();
    }
}

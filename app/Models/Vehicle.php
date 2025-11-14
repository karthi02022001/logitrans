<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'vehicle_number',
        'status',
        'registration_date',
        'insurance_expiry',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'insurance_expiry' => 'date',
    ];

    // Relationships
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function ownDriver()
    {
        return $this->hasOne(Driver::class, 'own_vehicle_id');
    }
}

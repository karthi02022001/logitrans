<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'vehicle_type_id',
        'cargo_weight',
        'status',
        'priority',
        'created_by',
    ];

    protected $casts = [
        'cargo_weight' => 'decimal:2',
    ];

    // Relationships
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    // Get assigned trip (if any)
    public function assignedTrip()
    {
        return $this->hasOne(Trip::class)->whereIn('status', ['assigned', 'in_transit', 'delivered']);
    }

    // Helper methods
    public function isAssigned()
    {
        return $this->assignedTrip()->exists();
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'assigned' => 'info',
            'in_transit' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getPriorityBadgeClass()
    {
        return match ($this->priority) {
            'normal' => 'secondary',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary'
        };
    }

    // Generate unique shipment number
    public static function generateShipmentNumber()
    {
        $year = date('Y');
        $lastShipment = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastShipment) {
            preg_match('/SHP-\d{4}-(\d+)/', $lastShipment->shipment_number, $matches);
            $nextNumber = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        }

        return sprintf('SHP-%s-%04d', $year, $nextNumber);
    }

    // Get available vehicles for this shipment
    public function getAvailableVehicles()
    {
        return Vehicle::where('vehicle_type_id', $this->vehicle_type_id)
            ->where('status', 'active')
            ->with('vehicleType')
            ->get();
    }

    // Get available drivers
    public function getAvailableDrivers()
    {
        // Get all active drivers
        return Driver::where('status', 'active')
            ->with('ownVehicle')
            ->get()
            ->filter(function ($driver) {
                // Driver is available if they don't have an active trip
                return !$driver->hasActiveTrip();
            });
    }
}

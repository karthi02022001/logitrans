<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_number',
        'vehicle_id',
        'driver_id',
        'shipment_id',
        'shipment_reference',
        'has_multiple_locations',
        'pickup_location',
        'drop_location',
        'trip_instructions',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'has_multiple_locations' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vehicle assigned to this trip
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver assigned to this trip
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the shipment associated with this trip
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the user who created this trip
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the files associated with this trip
     */
    public function files()
    {
        return $this->hasMany(TripFile::class);
    }

    /**
     * Get the cost details for this trip
     */
    public function cost()
    {
        return $this->hasOne(TripCost::class);
    }

    /**
     * Get the delivery details for this trip
     */
    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    /**
     * Scope to filter trips by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active trips (assigned or in_transit)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'in_transit']);
    }

    /**
     * Scope to get completed trips (delivered)
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Check if trip is active
     */
    public function isActive()
    {
        return in_array($this->status, ['assigned', 'in_transit']);
    }

    /**
     * Check if trip is completed
     */
    public function isCompleted()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if trip is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if trip has drop location
     */
    public function hasDropLocation()
    {
        return $this->has_multiple_locations && !empty($this->drop_location);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'secondary',
            'assigned' => 'primary',
            'in_transit' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get formatted trip duration
     */
    public function getDurationAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        $diff = $this->start_date->diff($this->end_date);

        if ($diff->days > 0) {
            return $diff->days . ' day(s)';
        }

        return $diff->format('%H hours %I minutes');
    }

    /**
     * Generate unique trip number
     */
    public static function generateTripNumber()
    {
        $lastTrip = self::latest('id')->first();
        return 'TRIP-' . str_pad(($lastTrip ? $lastTrip->id + 1 : 1), 6, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'base_cost',
        'toll_cost',
        'driver_allowance',
        'fuel_cost',
        'other_costs',
        'total_cost',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'toll_cost' => 'decimal:2',
        'driver_allowance' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
        'other_costs' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relationships
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'base_price',
        'description',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    // Relationships
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}

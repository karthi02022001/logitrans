<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivered_at',
        'signature_path',
        'pod_file_path',
        'delivery_remarks',
        'delivery_status',
        'created_by',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
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

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('delivery_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('delivery_status', 'pending');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('delivered_at', now()->month)
            ->whereYear('delivered_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('delivered_at', now()->year);
    }
}

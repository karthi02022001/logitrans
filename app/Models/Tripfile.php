<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    public $timestamps = false;

    // Relationships
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

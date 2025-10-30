<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'terminal_id',
        'sequence',
        'arrival_at',
        'departure_at',
        'is_active',
        'is_origin',
        'is_destination',
        'actual_arrival_at',
        'actual_departure_at',
        'remarks',
    ];

    protected $casts = [
        'arrival_at' => 'datetime',
        'departure_at' => 'datetime',
        'actual_arrival_at' => 'datetime',
        'actual_departure_at' => 'datetime',
        'is_active' => 'boolean',
        'is_origin' => 'boolean',
        'is_destination' => 'boolean',
    ];

    // =============================
    // Relationships
    // =============================
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }
}

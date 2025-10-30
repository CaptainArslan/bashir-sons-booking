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
    ];

    protected $casts = [
        'arrival_at' => 'datetime',
        'departure_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }
}

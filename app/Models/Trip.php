<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'timetable_id',
        'route_id',
        'bus_id',
        'departure_date',
        'departure_datetime',
        'estimated_arrival_datetime',
        'driver_name',
        'driver_phone',
        'driver_license',
        'driver_cnic',
        'driver_address',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'departure_datetime' => 'datetime',
            'estimated_arrival_datetime' => 'datetime',
        ];
    }

    // =============================
    // Relationships
    // =============================
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

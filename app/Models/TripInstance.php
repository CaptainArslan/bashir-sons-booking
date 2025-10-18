<?php

namespace App\Models;

use App\Enums\TripInstanceStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripInstance extends Model
{
    /** @use HasFactory<\Database\Factories\TripInstanceFactory> */
    use HasFactory;

    protected $fillable = [
        'route_timetable_id',
        'assigned_bus_id',
        'assigned_driver_id',
        'departure_date',
        'planned_departure_time',
        'planned_arrival_time',
        'actual_departure_time',
        'actual_arrival_time',
        'status',
        'remarks',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'planned_departure_time' => 'time',
        'planned_arrival_time' => 'time',
        'actual_departure_time' => 'time',
        'actual_arrival_time' => 'time',
        'status' => TripInstanceStatusEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function routeTimetable(): BelongsTo
    {
        return $this->belongsTo(RouteTimetable::class);
    }

    public function assignedBus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'assigned_bus_id');
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }
}

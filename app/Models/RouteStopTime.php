<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStopTime extends Model
{
    /** @use HasFactory<\Database\Factories\RouteStopTimeFactory> */
    use HasFactory;

    protected $fillable = [
        'timetable_id',
        'route_stop_id',
        'sequence',
        'arrival_time',
        'departure_time',
        'allow_online_booking',
    ];

    protected $casts = [
        'allow_online_booking' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(RouteTimetable::class);
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }
}

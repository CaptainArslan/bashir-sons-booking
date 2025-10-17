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

    public function terminal()
    {
        return $this->hasOneThrough(
            Terminal::class,
            RouteStop::class,
            'id', // Foreign key on RouteStop table
            'id', // Foreign key on Terminal table
            'route_stop_id', // Local key on RouteStopTime table
            'terminal_id' // Local key on RouteStop table
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the actual arrival time for a given date.
     */
    public function getArrivalTimeForDate(\Carbon\Carbon $date): ?\Carbon\Carbon
    {
        if (!$this->arrival_time) {
            return null;
        }

        return \Carbon\Carbon::createFromFormat('H:i', $this->arrival_time)
            ->setDate($date->year, $date->month, $date->day);
    }

    /**
     * Get the actual departure time for a given date.
     */
    public function getDepartureTimeForDate(\Carbon\Carbon $date): ?\Carbon\Carbon
    {
        if (!$this->departure_time) {
            return null;
        }

        return \Carbon\Carbon::createFromFormat('H:i', $this->departure_time)
            ->setDate($date->year, $date->month, $date->day);
    }

    /**
     * Check if this stop time is available for booking on a given date.
     */
    public function isAvailableForBooking(\Carbon\Carbon $date): bool
    {
        if (!$this->allow_online_booking) {
            return false;
        }

        $departureTime = $this->getDepartureTimeForDate($date);
        
        if (!$departureTime) {
            return false;
        }

        // Check if the departure time is in the future
        return $departureTime->isFuture();
    }
}

<?php

namespace App\Models;

use App\Enums\FrequencyTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteTimetable extends Model
{
    /** @use HasFactory<\Database\Factories\RouteTimetableFactory> */
    use HasFactory;

    protected $fillable = [
        'route_id',
        'trip_code',
        'departure_time',
        'arrival_time',
        'frequency',
        'operating_days',
        'is_active',
    ];

    protected $casts = [
        'departure_time' => 'datetime:H:i',
        'arrival_time' => 'datetime:H:i',
        'frequency' => FrequencyTypeEnum::class,
        'operating_days' => 'array',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStopTime::class, 'timetable_id');
    }

    public function stopsOrdered()
    {
        return $this->hasMany(RouteStopTime::class, 'timetable_id')->orderBy('sequence');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    protected function operatingDays(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? json_decode($value, true) : null,
            set: fn($value) => $value ? json_encode($value) : null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the timetable operates on a specific day.
     */
    public function operatesOn(string $day): bool
    {
        if ($this->frequency === FrequencyTypeEnum::DAILY) {
            return true;
        }

        if ($this->frequency === FrequencyTypeEnum::WEEKDAYS) {
            return !in_array(strtolower($day), ['saturday', 'sunday']);
        }

        if ($this->frequency === FrequencyTypeEnum::WEEKENDS) {
            return in_array(strtolower($day), ['saturday', 'sunday']);
        }

        if ($this->frequency === FrequencyTypeEnum::CUSTOM) {
            return in_array(strtolower($day), $this->operating_days ?? []);
        }

        return false;
    }

    /**
     * Get the next departure time for a given date.
     */
    public function getNextDepartureTime(\Carbon\Carbon $date): ?\Carbon\Carbon
    {
        if (!$this->is_active || !$this->operatesOn($date->format('l'))) {
            return null;
        }

        $departureTime = \Carbon\Carbon::createFromFormat('H:i', $this->departure_time)
            ->setDate($date->year, $date->month, $date->day);

        // If the departure time has already passed today, return null
        if ($departureTime->isPast()) {
            return null;
        }

        return $departureTime;
    }

    /**
     * Get all available booking stops for this timetable.
     */
    public function getBookingStops()
    {
        return $this->stopsOrdered()
            ->where('allow_online_booking', true)
            ->with('routeStop.terminal')
            ->get();
    }
}

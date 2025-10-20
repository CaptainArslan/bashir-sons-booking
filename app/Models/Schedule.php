<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\FrequencyTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'route_id',
        'code',
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
     * Check if the schedule operates on a specific day.
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
    public function getNextDepartureTime(Carbon $date): ?Carbon
    {
        if (!$this->is_active || !$this->operatesOn($date->format('l'))) {
            return null;
        }

        $departureTime = Carbon::createFromFormat('H:i', $this->departure_time)
            ->setDate($date->year, $date->month, $date->day);

        // If the departure time has already passed today, return null
        if ($departureTime->isPast()) {
            return null;
        }

        return $departureTime;
    }
}

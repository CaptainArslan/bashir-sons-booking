<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'name',
        'start_departure_time',
        'end_arrival_time',
        'is_active',
    ];

    protected $casts = [
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

    public function timetableStops(): HasMany
    {
        return $this->hasMany(TimetableStop::class);
    }

    public function activeStops(): HasMany
    {
        return $this->timetableStops()->where('is_active', true)->orderBy('sequence');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    protected function startDepartureTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('H:i'),
            set: fn ($value) => Carbon::parse($value)->format('H:i'),
        );
    }

    protected function endArrivalTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('H:i') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('H:i:s') : null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function getFirstStop()
    {
        return $this->timetableStops()->orderBy('sequence')->first();
    }

    public function getLastStop()
    {
        return $this->timetableStops()->orderByDesc('sequence')->first();
    }

    public function getTotalStops()
    {
        return $this->timetableStops()->count();
    }

    public function getTotalDuration()
    {
        $startTime = Carbon::parse($this->start_departure_time);
        $endTime = $this->end_arrival_time ? Carbon::parse($this->end_arrival_time) : null;

        if ($endTime) {
            return $startTime->diffInMinutes($endTime);
        }

        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }
}

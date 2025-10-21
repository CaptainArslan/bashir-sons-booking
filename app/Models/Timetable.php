<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'code',
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

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    protected function startDepartureTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('H:i'),
            set: fn($value) => Carbon::parse($value)->format('H:i'),
        );
    }

    protected function endArrivalTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('H:i'),
            set: fn($value) => Carbon::parse($value)->format('H:i'),
        );
    }
}

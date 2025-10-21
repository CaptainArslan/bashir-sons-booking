<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class TimetableStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'timetable_id',
        'terminal_id',
        'sequence',
        'arrival_time',
        'departure_time',
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
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    protected function arrivalTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('H:i:s'),
            set: fn($value) => Carbon::parse($value)->format('H:i:s'),
        );
    }

    protected function departureTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('H:i:s'),
            set: fn($value) => Carbon::parse($value)->format('H:i:s'),
        );
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

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
            get: fn($value) => $value ? Carbon::parse($value)->format('h:i A') : null,
        );
    }

    protected function departureTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('h:i A') : null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isFirstStop()
    {
        return $this->sequence === 1;
    }

    public function isLastStop()
    {
        $maxSequence = $this->timetable->timetableStops()->max('sequence');

        return $this->sequence === $maxSequence;
    }

    public function getNextStop()
    {
        return $this->timetable->timetableStops()
            ->where('sequence', '>', $this->sequence)
            ->orderBy('sequence')
            ->first();
    }

    public function getPreviousStop()
    {
        return $this->timetable->timetableStops()
            ->where('sequence', '<', $this->sequence)
            ->orderByDesc('sequence')
            ->first();
    }

    // =============================
    // Scopes
    // =============================
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence');
    }
}

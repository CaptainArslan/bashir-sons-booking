<?php

namespace App\Models;

use App\Enums\SeatLockTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'seat_id',
        'seat_number',
        'seat_row',
        'seat_column',
        'lock_type',
        'locked_at',
        'expires_at',
        'released_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'lock_type' => SeatLockTypeEnum::class,
            'locked_at' => 'datetime',
            'expires_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->whereNull('released_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->whereNull('released_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeForTrip($query, int $tripId)
    {
        return $query->where('trip_id', $tripId);
    }

    public function scopeForSeat($query, string $seatId)
    {
        return $query->where('seat_id', $seatId);
    }

    public function scopeTemporary($query)
    {
        return $query->where('lock_type', SeatLockTypeEnum::Temporary);
    }

    public function scopePhoneHold($query)
    {
        return $query->where('lock_type', SeatLockTypeEnum::PhoneHold);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        if (! is_null($this->released_at)) {
            return false;
        }

        if (is_null($this->expires_at)) {
            return true;
        }

        return $this->expires_at > now();
    }

    public function isExpired(): bool
    {
        return is_null($this->released_at) &&
            ! is_null($this->expires_at) &&
            $this->expires_at <= now();
    }

    public function release(): bool
    {
        return $this->update([
            'released_at' => now(),
        ]);
    }

    public function getSeatIdentifier(): string
    {
        return "{$this->seat_row}{$this->seat_column}";
    }

    public function getRemainingTime(): ?int
    {
        if (is_null($this->expires_at) || ! $this->isActive()) {
            return null;
        }

        return max(0, now()->diffInSeconds($this->expires_at));
    }
}

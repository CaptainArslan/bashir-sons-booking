<?php

namespace App\Models;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'trip_id',
        'user_id',
        'booked_by_user_id',
        'from_stop_id',
        'to_stop_id',
        'type',
        'status',
        'total_fare',
        'discount_amount',
        'final_amount',
        'currency',
        'total_passengers',
        'passenger_contact_phone',
        'passenger_contact_email',
        'passenger_contact_name',
        'notes',
        'metadata',
        'confirmed_at',
        'cancelled_at',
        'reserved_until',
        'payment_status',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'type' => BookingTypeEnum::class,
            'status' => BookingStatusEnum::class,
            'total_fare' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'total_passengers' => 'integer',
            'metadata' => 'array',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reserved_until' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot Method
    |--------------------------------------------------------------------------
    */

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = self::generateBookingNumber();
            }
        });
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by_user_id');
    }

    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'from_stop_id');
    }

    public function toStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'to_stop_id');
    }

    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeConfirmed($query)
    {
        return $query->where('status', BookingStatusEnum::Confirmed);
    }

    public function scopePending($query)
    {
        return $query->where('status', BookingStatusEnum::Pending);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', BookingStatusEnum::Cancelled);
    }

    public function scopeForTrip($query, int $tripId)
    {
        return $query->where('trip_id', $tripId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOnline($query)
    {
        return $query->where('type', BookingTypeEnum::Online);
    }

    public function scopeCounter($query)
    {
        return $query->where('type', BookingTypeEnum::Counter);
    }

    public function scopePhone($query)
    {
        return $query->where('type', BookingTypeEnum::Phone);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatusEnum::Confirmed;
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatusEnum::Pending;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatusEnum::Cancelled;
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled() && $this->trip->departure_datetime > now();
    }

    public function isPhoneBooking(): bool
    {
        return $this->type === BookingTypeEnum::Phone;
    }

    public function isOnlineBooking(): bool
    {
        return $this->type === BookingTypeEnum::Online;
    }

    public function isCounterBooking(): bool
    {
        return $this->type === BookingTypeEnum::Counter;
    }

    public function confirm(): bool
    {
        return $this->update([
            'status' => BookingStatusEnum::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(?string $reason = null): bool
    {
        if (! $this->canBeCancelled()) {
            return false;
        }

        $metadata = $this->metadata ?? [];
        $metadata['cancellation_reason'] = $reason;
        $metadata['cancelled_by'] = auth()->id();

        return $this->update([
            'status' => BookingStatusEnum::Cancelled,
            'cancelled_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    public static function generateBookingNumber(): string
    {
        do {
            $number = 'BKG-'.strtoupper(Str::random(8));
        } while (self::where('booking_number', $number)->exists());

        return $number;
    }

    public function calculateFare(): float
    {
        // This will be implemented based on route stops fare calculation
        return $this->total_fare - $this->discount_amount;
    }
}

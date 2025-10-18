<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\BookingChannelEnum;
use App\Enums\SeatReservationStatusEnum;
use App\Models\TripInstance;
use App\Models\Booking;
use App\Models\BookingPassenger;

class SeatReservation extends Model
{
    /** @use HasFactory<\Database\Factories\SeatReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'trip_instance_id',
        'booking_id',
        'booking_passenger_id',
        'seat_number',
        'hold_expires_at',
        'channel',
        'status',
    ];

    protected $casts = [
        'trip_instance_id' => 'integer',
        'booking_id' => 'integer',
        'booking_passenger_id' => 'integer',
        'seat_number' => 'integer',
        'hold_expires_at' => 'datetime',
        'channel' => BookingChannelEnum::class,
        'status' => SeatReservationStatusEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function tripInstance(): BelongsTo
    {
        return $this->belongsTo(TripInstance::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingPassenger(): BelongsTo
    {
        return $this->belongsTo(BookingPassenger::class);
    }
}

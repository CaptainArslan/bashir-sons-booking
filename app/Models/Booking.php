<?php

namespace App\Models;

use App\Models\TripInstance;
use App\Models\RouteTimetable;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\BookingPassenger;
use App\Enums\BookingStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\BookingChannelEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'trip_instance_id',
        'route_timetable_id',
        'agent_id',
        'from_stop_id',
        'to_stop_id',
        'terminal_id',
        'channel',
        'total_seats',
        'base_fare_per_seat',
        'total_fare',
        'currency',
        'status',
        'payment_method',
        'online_payment_method',
        'expiry_at',
        'uuid',
        'booking_reference_number',
        'remarks',
    ];

    protected $casts = [
        'trip_instance_id' => 'integer',
        'route_timetable_id' => 'integer',
        'agent_id' => 'integer',
        'from_stop_id' => 'integer',
        'to_stop_id' => 'integer',
        'terminal_id' => 'integer',
        'channel' => BookingChannelEnum::class,
        'total_seats' => 'integer',
        'base_fare_per_seat' => 'decimal:2',
        'total_fare' => 'decimal:2',
        'status' => BookingStatusEnum::class,
        'payment_method' => PaymentMethodEnum::class,
        'online_payment_method' => 'string',
        'expiry_at' => 'datetime',
        'uuid' => 'string',
        'booking_reference_number' => 'string',
        'remarks' => 'string',
    ];

    // =============================
    // Relationships
    // =============================
    public function tripInstance(): BelongsTo
    {
        return $this->belongsTo(TripInstance::class);
    }
    public function routeTimetable(): BelongsTo
    {
        return $this->belongsTo(RouteTimetable::class);
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'from_stop_id');
    }

    public function toStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'to_stop_id');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'terminal_id');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(BookingPassenger::class);
    }

    // =============================
    // Accessors & Mutators
    // =============================
    protected function formattedTotalFare(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'PKR ' . number_format($this->total_fare, 2),
        );
    }
}

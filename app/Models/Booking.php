<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'trip_id',
        'created_by_type',
        'user_id',
        'booked_by_user_id',
        'terminal_id',
        'from_stop_id',
        'to_stop_id',
        'channel',
        'status',
        'reserved_until',
        'payment_status',
        'payment_method',
        'online_transaction_id',
        'total_fare',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'currency',
        'total_passengers',
        'notes',
        'payment_received_from_customer',
        'return_after_deduction_from_customer',
        'confirmed_at',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancelled_by_type',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'reserved_until' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'total_fare' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'payment_received_from_customer' => 'decimal:2',
            'return_after_deduction_from_customer' => 'decimal:2',
            'total_passengers' => 'integer',
        ];
    }

    // =============================
    // Relationships
    // =============================
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'from_stop_id');
    }

    public function toStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'to_stop_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    public function activeSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class)->whereNull('cancelled_at');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(BookingPassenger::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by_user_id');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    // =============================
    // Accessors & Mutators
    // =============================
    protected function bookingNumber(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => str_pad($value, 6, '0', STR_PAD_LEFT),
            set: fn ($value) => str_pad($value, 6, '0', STR_PAD_LEFT),
        );
    }

    // =============================
    // Scopes
    // =============================
    public function scopeActiveForAvailability($q)
    {
        return $q->whereIn('status', ['hold', 'confirmed', 'checked_in', 'boarded'])
            ->where(function ($qq) {
                $qq->where('status', '!=', 'hold')
                    ->orWhere('reserved_until', '>', now());
            });
    }
}

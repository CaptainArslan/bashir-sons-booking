<?php

namespace App\Models;

use App\Enums\TripStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'timetable_id',
        'route_id',
        'bus_id',
        'departure_date',
        'departure_datetime',
        'estimated_arrival_datetime',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'departure_datetime' => 'datetime',
            'estimated_arrival_datetime' => 'datetime',
            'status' => TripStatusEnum::class,
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

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function confirmedBookings(): HasMany
    {
        return $this->bookings()->where('status', 'confirmed');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function seatLocks(): HasMany
    {
        return $this->hasMany(SeatLock::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUpcoming($query)
    {
        return $query->where('departure_datetime', '>=', now())
            ->whereIn('status', ['pending', 'scheduled', 'boarding']);
    }

    public function scopeForRoute($query, int $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('departure_date', $date);
    }

    public function scopeWithBus($query)
    {
        return $query->whereNotNull('bus_id');
    }

    public function scopeWithoutBus($query)
    {
        return $query->whereNull('bus_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function hasBusAssigned(): bool
    {
        return ! is_null($this->bus_id);
    }

    public function canAcceptBookings(): bool
    {
        return $this->status->allowsBooking();
    }

    public function canAssignBus(): bool
    {
        return $this->status->allowsBusAssignment();
    }

    public function canAddExpenses(): bool
    {
        return $this->hasBusAssigned() && $this->status->allowsExpenses();
    }

    public function getTotalRevenue(): float
    {
        return $this->confirmedBookings()->sum('final_amount');
    }

    public function getTotalExpenses(): float
    {
        return $this->expenses()->sum('amount');
    }

    public function getNetProfit(): float
    {
        return $this->getTotalRevenue() - $this->getTotalExpenses();
    }

    public function getAvailableSeats(): int
    {
        if (! $this->bus || ! $this->bus->busLayout) {
            return 0;
        }

        $totalSeats = $this->bus->busLayout->total_seats;
        $bookedSeats = $this->confirmedBookings()
            ->withCount('bookingSeats')
            ->get()
            ->sum('booking_seats_count');

        return max(0, $totalSeats - $bookedSeats);
    }

    public function getOccupancyRate(): float
    {
        if (! $this->bus || ! $this->bus->busLayout) {
            return 0;
        }

        $totalSeats = $this->bus->busLayout->total_seats;
        if ($totalSeats === 0) {
            return 0;
        }

        $bookedSeats = $this->confirmedBookings()
            ->withCount('bookingSeats')
            ->get()
            ->sum('booking_seats_count');

        return round(($bookedSeats / $totalSeats) * 100, 2);
    }
}

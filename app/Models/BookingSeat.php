<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'seat_number',
        'seat_row',
        'seat_column',
        'passenger_name',
        'passenger_age',
        'passenger_gender',
        'passenger_cnic',
        'passenger_phone',
        'fare',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fare' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function getSeatIdentifier(): string
    {
        return "{$this->seat_row}{$this->seat_column}";
    }

    public function getFullSeatInfo(): array
    {
        return [
            'seat_number' => $this->seat_number,
            'seat_row' => $this->seat_row,
            'seat_column' => $this->seat_column,
            'identifier' => $this->getSeatIdentifier(),
        ];
    }

    public function getPassengerInfo(): array
    {
        return [
            'name' => $this->passenger_name,
            'age' => $this->passenger_age,
            'gender' => $this->passenger_gender,
            'cnic' => $this->passenger_cnic,
            'phone' => $this->passenger_phone,
        ];
    }
}

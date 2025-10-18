<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Booking;
use App\Models\City;

class BookingPassenger extends Model
{
    /** @use HasFactory<\Database\Factories\BookingPassengerFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'city_id',
        'name',
        'cnic',
        'email',
        'phone',
        'address',
        'gender',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'city_id' => 'integer',
        'name' => 'string',
        'cnic' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'gender' => GenderEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}

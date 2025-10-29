<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

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
        'total_fare',
        'discount_amount',
        'final_amount',
        'currency',
        'total_passengers',
        'notes',
        'metadata',
        'confirmed_at',
        'cancelled_at',
    ];
}

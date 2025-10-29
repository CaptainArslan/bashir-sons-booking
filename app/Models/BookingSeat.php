<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'from_stop_id',
        'to_stop_id',
        'seat_number',
        'seat_row',
        'seat_column',
    ];

    protected $casts = [
        'seat_number' => 'integer',
        'seat_row' => 'string',
        'seat_column' => 'string',
    ];

    // =============================
    // Relationships
    // =============================
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

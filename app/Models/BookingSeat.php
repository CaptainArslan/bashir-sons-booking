<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'from_stop_id',
        'to_stop_id',
        'seat_number',
        'gender',
        'fare',
        'tax_amount',
        'final_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gender' => GenderEnum::class,
            'fare' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
        ];
    }

    // =============================
    // Relationships
    // =============================
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'from_stop_id');
    }

    public function toStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'to_stop_id');
    }
}

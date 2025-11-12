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
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'gender' => GenderEnum::class,
            'fare' => 'integer',
            'tax_amount' => 'integer',
            'final_amount' => 'integer',
            'cancelled_at' => 'datetime',
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

    // =============================
    // Scopes
    // =============================
    public function scopeActive($query)
    {
        return $query->whereNull('cancelled_at');
    }

    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_at');
    }

    // =============================
    // Accessors & Mutators
    // =============================
    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }
}

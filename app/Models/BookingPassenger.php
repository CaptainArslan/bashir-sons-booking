<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'name',
        'age',
        'gender',
        'cnic',
        'phone',
        'email',
        // 'status',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'gender' => GenderEnum::class,
        ];
    }

    // =============================
    // Relationships
    // =============================
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

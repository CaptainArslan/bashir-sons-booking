<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BusLayoutEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BusLayout extends Model
{
    /** @use HasFactory<\Database\Factories\BusLayoutFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'total_rows',
        'total_columns',
        'total_seats',
        'seat_map',
        'status',
    ];

    protected $casts = [
        'status' => BusLayoutEnum::class,
        'seat_map' => 'array',
    ];

    // =============================
    // Relationships
    // =============================
    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    // =============================
    // Accessors & Mutators
    // =============================
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucfirst($value),
        );
    }

    protected function totalSeats(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->total_rows * $this->total_columns ?? 0,
        );
    }
}

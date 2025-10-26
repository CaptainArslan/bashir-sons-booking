<?php

namespace App\Models;

use App\Enums\BusLayoutEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'total_rows' => 'integer',
        'total_columns' => 'integer',
        'total_seats' => 'integer',
        'seat_map' => 'array',
        'status' => BusLayoutEnum::class,
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
            get: fn ($value) => ucfirst($value),
        );
    }

    protected function totalSeats(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->total_rows * $this->total_columns ?? 0,
        );
    }
}

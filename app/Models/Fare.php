<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\FareStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Fare extends Model
{
    /** @use HasFactory<\Database\Factories\FareFactory> */
    use HasFactory;

    protected $fillable = [
        'from_terminal_id',
        'to_terminal_id',
        'base_fare',
        'discount_type',
        'discount_value',
        'final_fare',
        'currency',
        'status',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'discount_type' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_fare' => 'decimal:2',
        'currency' => 'string',
        'status' => FareStatusEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function fromTerminal()
    {
        return $this->belongsTo(Terminal::class, 'from_terminal_id');
    }

    public function toTerminal()
    {
        return $this->belongsTo(Terminal::class, 'to_terminal_id');
    }

    // =============================
    // Helpers
    // =============================
    public function getFinalFareAttribute()
    {
        return $this->base_fare - ($this->discount_type * $this->discount_value);
    }

    public function getDiscountTypeAttribute()
    {
        return $this->discount_type;
    }
}

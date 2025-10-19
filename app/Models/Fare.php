<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\FareStatusEnum;
use App\Enums\DiscountTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fare extends Model
{
    /** @use HasFactory<\Database\Factories\FareFactory> */
    use HasFactory, SoftDeletes;

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
        'discount_value' => 'decimal:2',
        'final_fare' => 'decimal:2',
        'status' => FareStatusEnum::class,
        'discount_type' => DiscountTypeEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function fromTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'from_terminal_id')
            ->with('city');
    }

    public function toTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'to_terminal_id')
            ->with('city');
    }

    // =============================
    // Accessors & Mutators
    // =============================
    protected function calculatedFare(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->calculateFare(),
        );
    }

    protected function formattedBaseFare(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->currency . ' ' . number_format($this->base_fare, 2),
        );
    }

    protected function formattedFinalFare(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->currency . ' ' . number_format($this->final_fare, 2),
        );
    }

    protected function formattedDiscount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->discount_type === DiscountTypeEnum::PERCENT 
                ? $this->discount_value . '%' 
                : $this->currency . ' ' . number_format($this->discount_value, 2),
        );
    }

    protected function routePath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->fromTerminal?->city?->name . ' → ' . $this->toTerminal?->city?->name,
        );
    }

    protected function terminalPath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->fromTerminal?->name . ' → ' . $this->toTerminal?->name,
        );
    }

    // =============================
    // Methods
    // =============================
    private function calculateFare(): float
    {
        if (!$this->discount_type || !$this->discount_value) {
            return $this->base_fare;
        }

        return match ($this->discount_type) {
            DiscountTypeEnum::FLAT => max(0, $this->base_fare - $this->discount_value),
            DiscountTypeEnum::PERCENT => max(0, $this->base_fare - ($this->base_fare * $this->discount_value / 100)),
            default => $this->base_fare,
        };
    }

    public function updateFinalFare(): void
    {
        $this->final_fare = $this->calculateFare();
        $this->save();
    }

    public function getDiscountAmount(): float
    {
        if (!$this->discount_type || !$this->discount_value) {
            return 0;
        }

        return match ($this->discount_type) {
            DiscountTypeEnum::FLAT => $this->discount_value,
            DiscountTypeEnum::PERCENT => $this->base_fare * $this->discount_value / 100,
            default => 0,
        };
    }

    public function isActive(): bool
    {
        return $this->status === FareStatusEnum::ACTIVE;
    }

    // =============================
    // Scopes
    // =============================
    public function scopeActive($query)
    {
        return $query->where('status', FareStatusEnum::ACTIVE->value);
    }

    public function scopeBetweenTerminals($query, $fromTerminalId, $toTerminalId)
    {
        return $query->where('from_terminal_id', $fromTerminalId)
                    ->where('to_terminal_id', $toTerminalId);
    }

    public function scopeForRoute($query, $routeId)
    {
        // Get terminals in the route
        $terminalIds = RouteStop::where('route_id', $routeId)->pluck('terminal_id')->toArray();
        
        return $query->whereIn('from_terminal_id', $terminalIds)
                    ->whereIn('to_terminal_id', $terminalIds);
    }
}

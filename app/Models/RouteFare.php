<?php

namespace App\Models;

use App\Enums\RouteFareStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteFare extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'from_stop_id',
        'to_stop_id',
        'base_fare',
        'discount_type',
        'discount_value',
        'final_fare',
        'status',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_fare' => 'decimal:2',
        'status' => RouteFareStatusEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'from_stop_id', 'id')
            ->with('terminal.city');
    }

    public function toStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class, 'to_stop_id', 'id')
            ->with('terminal.city');
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
            get: fn($value) => 'PKR ' . number_format($this->base_fare, 2),
        );
    }

    protected function formattedFinalFare(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'PKR ' . number_format($this->final_fare, 2),
        );
    }

    protected function formattedDiscount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->discount_type === 'percent' 
                ? $this->discount_value . '%' 
                : 'PKR ' . number_format($this->discount_value, 2),
        );
    }

    protected function routePath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->fromStop?->terminal?->city?->name . ' → ' . $this->toStop?->terminal?->city?->name,
        );
    }

    protected function stopPath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->fromStop?->terminal?->name . ' → ' . $this->toStop?->terminal?->name,
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
            'flat' => max(0, $this->base_fare - $this->discount_value),
            'percent' => max(0, $this->base_fare - ($this->base_fare * $this->discount_value / 100)),
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
            'flat' => $this->discount_value,
            'percent' => $this->base_fare * $this->discount_value / 100,
            default => 0,
        };
    }

    public function isActive(): bool
    {
        return $this->status === RouteFareStatusEnum::ACTIVE;
    }

    // =============================
    // Scopes
    // =============================
    public function scopeActive($query)
    {
        return $query->where('status', RouteFareStatusEnum::ACTIVE->value);
    }

    public function scopeForRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    public function scopeBetweenStops($query, $fromStopId, $toStopId)
    {
        return $query->where('from_stop_id', $fromStopId)
                    ->where('to_stop_id', $toStopId);
    }
}

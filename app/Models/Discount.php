<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    /** @use HasFactory<\Database\Factories\DiscountFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'route_id',
        'discount_type',
        'value',
        'is_android',
        'is_ios',
        'is_web',
        'is_counter',
        'starts_at',
        'ends_at',
        'start_time',
        'end_time',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_android' => 'boolean',
            'is_ios' => 'boolean',
            'is_web' => 'boolean',
            'is_counter' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the route this discount applies to.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the user who created this discount.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active discounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    /**
     * Scope for discounts by platform.
     */
    public function scopeForPlatform($query, string $platform)
    {
        return match ($platform) {
            'android' => $query->where('is_android', true),
            'ios' => $query->where('is_ios', true),
            'web' => $query->where('is_web', true),
            'counter' => $query->where('is_counter', true),
            default => $query,
        };
    }

    /**
     * Check if discount is currently valid.
     */
    public function isValid(): bool
    {
        $now = now();

        return $this->is_active
            && $this->starts_at <= $now
            && $this->ends_at >= $now;
    }

    /**
     * Check if discount is expired.
     */
    public function isExpired(): bool
    {
        return $this->ends_at < now();
    }

    /**
     * Check if discount is active for specific platform.
     */
    public function isActiveForPlatform(string $platform): bool
    {
        return match ($platform) {
            'android' => $this->is_android,
            'ios' => $this->is_ios,
            'web' => $this->is_web,
            'counter' => $this->is_counter,
            default => false,
        };
    }

    /**
     * Calculate discount amount for given order amount.
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if (! $this->isValid()) {
            return 0;
        }

        return match ($this->discount_type) {
            'fixed' => min($this->value, $orderAmount),
            'percentage' => min(($orderAmount * $this->value) / 100, $orderAmount),
            default => 0,
        };
    }

    /**
     * Get formatted discount value.
     */
    public function getFormattedValueAttribute(): string
    {
        return match ($this->discount_type) {
            'fixed' => '₹'.number_format($this->value, 2),
            'percentage' => $this->value.'%',
            default => '₹'.number_format($this->value, 2),
        };
    }

    /**
     * Get platforms where discount is active.
     */
    public function getActivePlatformsAttribute(): array
    {
        $platforms = [];
        if ($this->is_android) {
            $platforms[] = 'Android';
        }
        if ($this->is_ios) {
            $platforms[] = 'iOS';
        }
        if ($this->is_web) {
            $platforms[] = 'Web';
        }
        if ($this->is_counter) {
            $platforms[] = 'Counter';
        }

        return $platforms;
    }
}

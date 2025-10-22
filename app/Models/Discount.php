<?php

namespace App\Models;

use App\Enums\DiscountTypeEnum;
use App\Enums\PlatformEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
        'created_by'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_android' => 'boolean',
        'is_ios' => 'boolean',
        'is_web' => 'boolean',
        'is_counter' => 'boolean',
        'is_active' => 'boolean',
    ];

    // =============================
    // Relationships
    // =============================
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    // =============================
    // Scopes
    // =============================
    public function scopeActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    // =============================
    // Methods
    // =============================

    public function isValidForPlatform(string $platform): bool
    {
        $key = match ($platform) {
            PlatformEnum::ANDROID->value => 'is_android',
            PlatformEnum::IOS->value => 'is_ios',
            PlatformEnum::WEB->value => 'is_web',
            PlatformEnum::COUNTER->value => 'is_counter',
            default => null,
        };

        return $key ? $this->{$key} : false;
    }

    public function isCurrentlyActive(): bool
    {
        $now = now();

        if (!$this->is_active) return false;
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;

        // Optional time window check
        if ($this->start_time && $this->end_time) {
            $nowTime = $now->format('H:i:s');
            if ($nowTime < $this->start_time || $nowTime > $this->end_time) {
                return false;
            }
        }

        return true;
    }

    public function applyToFare(float $baseFare): float
    {
        if ($this->discount_type === DiscountTypeEnum::PERCENT->value) {
            return max(0, $baseFare - ($baseFare * ($this->value / 100)));
        } else {
            return max(0, $baseFare - $this->value);
        }
    }
}

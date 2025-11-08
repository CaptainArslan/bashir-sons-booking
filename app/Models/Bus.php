<?php

namespace App\Models;

use App\Enums\BusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    /** @use HasFactory<\Database\Factories\BusFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'bus_type_id',
        'bus_layout_id',
        'registration_number',
        'model',
        'color',
        'status',
    ];

    protected $casts = [
        'status' => BusEnum::class,
        'bus_type_id' => 'integer',
        'bus_layout_id' => 'integer',
    ];

    // =============================
    // Relationships
    // =============================
    public function busType(): BelongsTo
    {
        return $this->belongsTo(BusType::class);
    }

    public function busLayout(): BelongsTo
    {
        return $this->belongsTo(BusLayout::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'bus_facility');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    // =============================
    // Accessors & Mutators
    // =============================
    protected function seatCount(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Load busLayout relationship if not already loaded
                if (! $this->relationLoaded('busLayout')) {
                    $this->load('busLayout');
                }

                // Get seat count from busLayout
                if ($this->busLayout) {
                    return $this->busLayout->total_seats ?? BusLayout::DEFAULT_SEATS;
                }

                return BusLayout::DEFAULT_SEATS;
            },
        );
    }
}

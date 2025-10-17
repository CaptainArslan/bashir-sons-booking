<?php

namespace App\Models;

use App\Models\Bus;
use App\Enums\FacilityEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Facility extends Model
{
    /** @use HasFactory<\Database\Factories\FacilityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'status',
    ];

    protected $casts = [
        'status' => FacilityEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function buses(): BelongsToMany
    {
        return $this->belongsToMany(Bus::class, 'bus_facility');
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
}

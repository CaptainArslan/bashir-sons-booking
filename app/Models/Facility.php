<?php

namespace App\Models;

use App\Models\Bus;
use App\Enums\FacilityEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}

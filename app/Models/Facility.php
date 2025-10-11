<?php

namespace App\Models;

use App\Models\Bus;
use App\Enums\FacilityEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function buses()
    {
        return $this->belongsToMany(Bus::class, 'bus_facilities');
    }
}

<?php

namespace App\Models;

use App\Enums\BusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}

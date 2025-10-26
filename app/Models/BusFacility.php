<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BusFacility extends Pivot
{
    protected $fillable = [
        'bus_id',
        'facility_id',
    ];

    protected $casts = [
        'bus_id' => 'integer',
        'facility_id' => 'integer',
    ];

    // =============================
    // Relationships
    // =============================
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}

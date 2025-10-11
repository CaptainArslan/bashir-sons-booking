<?php

namespace App\Models;

use App\Enums\BusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function busType()
    {
        return $this->belongsTo(BusType::class);
    }

    public function busLayout()
    {
        return $this->belongsTo(BusLayout::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'bus_facilities');
    }
}

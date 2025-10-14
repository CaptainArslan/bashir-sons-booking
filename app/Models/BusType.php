<?php

namespace App\Models;

use App\Enums\BusTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusType extends Model
{
    /** @use HasFactory<\Database\Factories\BusTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => BusTypeEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }
}

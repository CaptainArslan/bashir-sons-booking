<?php

namespace App\Models;

use App\Enums\CityEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'name' => 'string',
        'status' => CityEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class);
    }


    // Accessors & Mutators
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtolower(str_replace(' ', '_', $value)),
            get: fn($value) => ucwords(str_replace('_', ' ', $value)),
        );
    }
}

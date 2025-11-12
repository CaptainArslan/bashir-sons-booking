<?php

namespace App\Models;

use App\Enums\CityEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string',
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
            get: fn ($value) => ucwords(str_replace('_', ' ', $value)),
            set: fn ($value) => strtolower(str_replace(' ', '_', $value)),
        );
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?: strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->name), 0, 3)),
            set: fn ($value) => $value ? strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $value)) : null,
        );
    }
}

<?php

namespace App\Models;

use App\Enums\CityEnum;
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


    // Accessors & Mutators
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucfirst($value),
        );
    }
}

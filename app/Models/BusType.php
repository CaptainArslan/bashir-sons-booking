<?php

namespace App\Models;

use App\Enums\BusTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

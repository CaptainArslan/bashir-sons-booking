<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\BannerTypeEnum;
use App\Enums\BannerStatusEnum;

class Banner extends Model
{
    /** @use HasFactory<\Database\Factories\BannerFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'path',
        'status',
    ];

    protected $casts = [
        'type' => BannerTypeEnum::class,
        'status' => BannerStatusEnum::class,
    ];
}

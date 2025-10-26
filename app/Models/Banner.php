<?php

namespace App\Models;

use App\Enums\BannerStatusEnum;
use App\Enums\BannerTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

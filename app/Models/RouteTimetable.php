<?php

namespace App\Models;

use App\Enums\FrequencyTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteTimetable extends Model
{
    /** @use HasFactory<\Database\Factories\RouteTimetableFactory> */
    use HasFactory;

    protected $fillable = [
        'route_id',
        'trip_code',
        'departure_time',
        'arrival_time',
        'frequency',
        'operating_days',
        'is_active',
    ];

    protected $casts = [
        'frequency' => FrequencyTypeEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStopTime::class, 'timetable_id');
    }
}

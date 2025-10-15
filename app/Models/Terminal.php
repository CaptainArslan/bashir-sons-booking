<?php

namespace App\Models;

use App\Enums\TerminalEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Terminal extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'landmark',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'city_id' => 'integer',
        'code' => 'string',
        'status' => TerminalEnum::class,
    ];

    // =============================
    // Relationships
    // =============================
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function routeStops()
    {
        return $this->hasMany(RouteStop::class);
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'route_stops')
            ->withPivot(['sequence', 'distance_from_previous', 'approx_travel_time'])
            ->orderBy('pivot_sequence');
    }


    // Accessors & Mutators
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucfirst($value),
        );
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value),
        );
    }
}

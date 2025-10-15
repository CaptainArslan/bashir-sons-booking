<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'code',
        'name',
        'direction',
        'is_return_of',
        'base_currency',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function returnRoute()
    {
        return $this->belongsTo(self::class, 'is_return_of');
    }

    public function routeStops()
    {
        return $this->hasMany(RouteStop::class)->orderBy('sequence');
    }

    public function terminals()
    {
        return $this->belongsToMany(Terminal::class, 'route_stops')
            ->withPivot([
                'sequence',
                'distance_from_previous',
                'approx_travel_time',
                'is_pickup_allowed',
                'is_dropoff_allowed'
            ])
            ->orderBy('pivot_sequence');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
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
    
    protected function firstTerminal(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->routeStops()->orderBy('sequence')->first()?->terminal,
        );
    }

    protected function lastTerminal(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->routeStops()->orderByDesc('sequence')->first()?->terminal,
        );
    }
}

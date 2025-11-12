<?php

namespace App\Models;

use App\Enums\RouteStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'from_city_id',
        'to_city_id',
        'code',
        'name',
        'direction',
        'is_return_of',
        'base_currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => RouteStatusEnum::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'route_user')
            ->withTimestamps();
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'to_city_id');
    }

    public function returnRoute(): BelongsTo
    {
        return $this->belongsTo(self::class, 'is_return_of');
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class)->orderBy('sequence');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function terminals()
    {
        return $this->belongsToMany(Terminal::class, 'route_stops')
            ->withPivot(['sequence'])
            ->orderBy('pivot_sequence');
    }

    public function firstStop()
    {
        return $this->routeStops()->orderBy('sequence')->first();
    }

    public function lastStop()
    {
        return $this->routeStops()->orderByDesc('sequence')->first();
    }

    public function activeDiscounts()
    {
        return $this->discounts()->active()
            ->orderBy('starts_at', 'asc')
            ->orderBy('start_time', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
        );
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtoupper($value),
        );
    }

    protected function totalTravelTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => 0, // Distance and travel time are no longer tracked at route_stops level
        );
    }

    protected function totalDistance(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => 0, // Distance and travel time are no longer tracked at route_stops level
        );
    }

    protected function totalStops(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->routeStops()->count(),
        );
    }

    protected function firstTerminal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->routeStops()->orderBy('sequence')->first()?->terminal,
        );
    }

    protected function lastTerminal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->routeStops()->orderByDesc('sequence')->first()?->terminal,
        );
    }

    protected function totalFare(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->getTotalFare(),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function getTotalFare()
    {
        // Get all stop terminal IDs for this route in correct order
        $terminalIds = $this->routeStops()
            ->orderBy('sequence') // assuming you have an 'order' column
            ->pluck('terminal_id')
            ->toArray();

        // If fewer than 2 stops, no fare calculation needed
        if (count($terminalIds) < 2) {
            return 0;
        }

        $totalFare = 0;

        // Loop through consecutive stops and sum up fares
        for ($i = 0; $i < count($terminalIds) - 1; $i++) {
            $fromId = $terminalIds[$i];
            $toId = $terminalIds[$i + 1];

            $fare = Fare::where('from_terminal_id', $fromId)
                ->where('to_terminal_id', $toId)
                ->value('final_fare'); // fetch only the fare value

            $totalFare += $fare ?? 0;
        }

        return $totalFare;
    }
}

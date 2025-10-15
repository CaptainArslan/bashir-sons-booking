<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'terminal_id',
        'sequence',
        'distance_from_previous',
        'approx_travel_time',
        'is_pickup_allowed',
        'is_dropoff_allowed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function terminal()
    {
        return $this->belongsTo(Terminal::class);
    }
}

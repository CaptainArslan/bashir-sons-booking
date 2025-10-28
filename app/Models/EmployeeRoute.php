<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRoute extends Model
{
    protected $fillable = [
        'user_id',
        'route_id',
        'starting_terminal_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the employee/user for this assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route for this assignment
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the starting terminal for this assignment
     */
    public function startingTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'starting_terminal_id');
    }
}

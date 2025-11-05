<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Trip;
use App\Models\TripStop;

class BookingPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function create(User $user, array $payload): bool
    {
        if ($user->isAdmin()) return true;

        $trip = Trip::with('timetable')->findOrFail($payload['trip_id']);
        $from = TripStop::findOrFail($payload['from_stop_id']);
        $to   = TripStop::findOrFail($payload['to_stop_id']);

        // Employee terminal constraint: booking must be created from the employee's assigned terminal
        $employeeTerminalId = $user->employee?->terminal_id; // adjust to your schema
        $fromTerminalId = $from->terminal_id;

        $hasTerminal = ($employeeTerminalId && $employeeTerminalId === ($payload['terminal_id'] ?? null));
        $forward = $from->sequence < $to->sequence;

        // Route permission check (adjust to your pivot)
        $hasRoutePerm = $user->employee?->routePermissions()->where('route_id',$trip->route_id)->where('active',true)->exists() ?? false;

        return $hasTerminal && $forward && $hasRoutePerm;
    }
}

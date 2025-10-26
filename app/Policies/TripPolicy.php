<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    /**
     * Determine if the user can view any trips.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view trips
    }

    /**
     * Determine if the user can view the trip.
     */
    public function view(User $user, Trip $trip): bool
    {
        return true; // All authenticated users can view trip details
    }

    /**
     * Determine if the user can create trips.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can update the trip.
     */
    public function update(User $user, Trip $trip): bool
    {
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can delete the trip.
     */
    public function delete(User $user, Trip $trip): bool
    {
        // Only allow deletion if no bookings exist
        if ($trip->bookings()->exists()) {
            return false;
        }

        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can assign a bus to the trip.
     */
    public function assignBus(User $user, Trip $trip): bool
    {
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can start the trip.
     */
    public function start(User $user, Trip $trip): bool
    {
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can complete the trip.
     */
    public function complete(User $user, Trip $trip): bool
    {
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can cancel the trip.
     */
    public function cancel(User $user, Trip $trip): bool
    {
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can view trip statistics.
     */
    public function viewStatistics(User $user, Trip $trip): bool
    {
        return $user->hasRole(['Admin', 'Super Admin', 'Employee']);
    }
}

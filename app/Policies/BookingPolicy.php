<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view bookings') ||
            $user->hasRole(['Admin', 'Super Admin', 'Employee']);
    }

    /**
     * Determine if the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // User can view their own bookings
        if ($booking->user_id === $user->id || $booking->booked_by_user_id === $user->id) {
            return true;
        }

        // Admin and Super Admin can view all bookings
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can create bookings.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create bookings') ||
            $user->hasRole(['Admin', 'Super Admin', 'Employee', 'Customer']);
    }

    /**
     * Determine if the user can create counter bookings.
     */
    public function createCounterBooking(User $user): bool
    {
        return $user->hasRole(['Admin', 'Super Admin', 'Employee']);
    }

    /**
     * Determine if the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Only pending bookings can be updated
        if (! $booking->isPending()) {
            return false;
        }

        // User can update their own bookings
        if ($booking->user_id === $user->id) {
            return true;
        }

        // Admin and Super Admin can update all bookings
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Only admin can delete
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // Check if booking can be cancelled
        if (! $booking->canBeCancelled()) {
            return false;
        }

        // User can cancel their own bookings
        if ($booking->user_id === $user->id) {
            return true;
        }

        // Admin and Super Admin can cancel all bookings
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can confirm the booking.
     */
    public function confirm(User $user, Booking $booking): bool
    {
        // Only pending bookings can be confirmed
        if (! $booking->isPending()) {
            return false;
        }

        // User can confirm their own bookings
        if ($booking->user_id === $user->id) {
            return true;
        }

        // Admin, Super Admin, and Employee can confirm bookings
        return $user->hasRole(['Admin', 'Super Admin', 'Employee']);
    }
}

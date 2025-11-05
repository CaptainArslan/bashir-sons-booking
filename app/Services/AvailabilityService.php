<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RouteStop;
use App\Models\Trip;
use App\Models\TripStop;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    /**
     * Resolve segment using TripStop IDs and return sequences
     *
     * @param  int  $fromStopId  TripStop ID
     * @param  int  $toStopId  TripStop ID
     * @return array [seqFrom, seqTo, tripStopMap]
     */
    public function resolveSegment(int $tripId, int $fromStopId, int $toStopId): array
    {
        $stops = TripStop::where('trip_id', $tripId)->get(['id', 'sequence'])->keyBy('id');

        if (! isset($stops[$fromStopId]) || ! isset($stops[$toStopId])) {
            throw ValidationException::withMessages(['segment' => 'Invalid trip stops provided.']);
        }

        $a = $stops[$fromStopId]->sequence;
        $b = $stops[$toStopId]->sequence;

        if ($a >= $b) {
            throw ValidationException::withMessages(['segment' => 'Invalid segment (order).']);
        }

        return [$a, $b, $stops];
    }

    /**
     * Get RouteStop map for a trip's route
     * Maps RouteStop ID to sequence
     *
     * @return array RouteStop ID => sequence
     */
    private function getRouteStopMap(int $routeId): array
    {
        $routeStops = RouteStop::where('route_id', $routeId)
            ->get(['id', 'sequence'])
            ->keyBy('id');

        $map = [];
        foreach ($routeStops as $routeStop) {
            $map[$routeStop->id] = $routeStop->sequence;
        }

        return $map;
    }

    public function seatCount(Trip $trip): int
    {
        return $trip->bus?->seatCount ?? 44;
    }

    /**
     * Get available seats for a segment
     *
     * @param  int  $fromStopId  TripStop ID (for query segment)
     * @param  int  $toStopId  TripStop ID (for query segment)
     * @return array Available seat numbers
     */
    public function availableSeats(int $tripId, int $fromStopId, int $toStopId, ?int $limit = null): array
    {
        // Resolve query segment using TripStop IDs
        [$seqFrom, $seqTo, $tripStopMap] = $this->resolveSegment($tripId, $fromStopId, $toStopId);

        $trip = Trip::with('bus', 'route')->findOrFail($tripId);
        $seatCount = $this->seatCount($trip);

        // Get RouteStop map for checking booking overlaps
        $routeStopMap = $this->getRouteStopMap($trip->route_id);

        // Load bookings with RouteStop relationships
        // Exclude cancelled seats when loading seats
        $bookings = Booking::with(['seats' => function ($query) {
            $query->whereNull('cancelled_at')->select('booking_id', 'seat_number');
        }, 'fromStop:id,sequence', 'toStop:id,sequence'])
            ->where('trip_id', $tripId)
            ->activeForAvailability()
            ->get(['id', 'from_stop_id', 'to_stop_id', 'status']);

        // Initialize occupancy array: seat_number => [overlapping segments]
        $occ = array_fill(1, $seatCount, []);

        foreach ($bookings as $booking) {
            // Get sequences from RouteStop (bookings store RouteStop IDs)
            $bookingFromSeq = $booking->fromStop?->sequence ?? null;
            $bookingToSeq = $booking->toStop?->sequence ?? null;

            // Skip if RouteStop sequences are not found
            if ($bookingFromSeq === null || $bookingToSeq === null) {
                // Fallback: try to get from routeStopMap using stored IDs
                $bookingFromSeq = $routeStopMap[$booking->from_stop_id] ?? null;
                $bookingToSeq = $routeStopMap[$booking->to_stop_id] ?? null;
            }

            // Skip this booking if we still don't have sequences
            if ($bookingFromSeq === null || $bookingToSeq === null) {
                continue;
            }

            // Check if booking segment overlaps with query segment
            // Overlap occurs when: bookingFrom < queryTo AND queryFrom < bookingTo
            if ($bookingFromSeq < $seqTo && $seqFrom < $bookingToSeq) {
                foreach ($booking->seats as $seat) {
                    $seatNum = $seat->seat_number;
                    if ($seatNum >= 1 && $seatNum <= $seatCount) {
                        $occ[$seatNum][] = [$bookingFromSeq, $bookingToSeq];
                    }
                }
            }
        }

        // Find available seats
        $free = [];
        for ($n = 1; $n <= $seatCount; $n++) {
            $hit = false;

            // Check if seat has any overlapping bookings
            foreach ($occ[$n] as [$a, $c]) {
                // Overlap check: booking segment overlaps query segment
                if ($a < $seqTo && $seqFrom < $c) {
                    $hit = true;
                    break;
                }
            }

            if (! $hit) {
                $free[] = $n;
                if ($limit && count($free) >= $limit) {
                    break;
                }
            }
        }

        return $free;
    }

    public function countAvailable(int $tripId, int $fromStopId, int $toStopId): int
    {
        return count($this->availableSeats($tripId, $fromStopId, $toStopId));
    }
}

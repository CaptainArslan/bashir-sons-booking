<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\TripStop;
use App\Models\Booking;


class AvailabilityService
{
    public function seatCountForTrip(Trip $trip): int
    {
        return $trip->bus?->seat_count ?? 44;
    }

    public function segmentSequences(int $tripId, int $fromTripStopId, int $toTripStopId): array
    {
        $stops = TripStop::where('trip_id', $tripId)->get(['id', 'sequence'])->keyBy('id');
        $a = $stops[$fromTripStopId]->sequence ?? null;
        $b = $stops[$toTripStopId]->sequence ?? null;
        if ($a === null || $b === null || $a >= $b) {
            throw new \RuntimeException('Invalid segment');
        }
        return [$a, $b, $stops];
    }

    public function availableSeats(int $tripId, int $fromTripStopId, int $toTripStopId, ?int $limit = null): array
    {
        [$seqFrom, $seqTo, $stopsMap] = $this->segmentSequences($tripId, $fromTripStopId, $toTripStopId);

        $trip = Trip::with('bus')->findOrFail($tripId);
        $seatCount = $this->seatCountForTrip($trip);

        $bookings = Booking::with(['seats'])
            ->where('trip_id', $tripId)
            ->activeForAvailability()
            ->get(['id', 'from_stop_id', 'to_stop_id', 'status']);

        $occupied = array_fill(1, $seatCount, []);
        foreach ($bookings as $b) {
            $a = $stopsMap[$b->from_stop_id]->sequence;
            $c = $stopsMap[$b->to_stop_id]->sequence;
            foreach ($b->seats as $s) {
                $occupied[$s->seat_number][] = [$a, $c];
            }
        }

        $free = [];
        for ($n = 1; $n <= $seatCount; $n++) {
            $hit = false;
            foreach ($occupied[$n] as [$a, $c]) {
                if ($a < $seqTo && $seqFrom < $c) {
                    $hit = true;
                    break;
                } // overlap
            }
            if (!$hit) {
                $free[] = $n;
                if ($limit && count($free) >= $limit) break;
            }
        }
        return $free;
    }
}

<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Booking;
use App\Models\TripStop;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    public function resolveSegment(int $tripId, int $fromStopId, int $toStopId): array
    {
        $stops = TripStop::where('trip_id', $tripId)->get(['id', 'sequence'])->keyBy('id');
        $a = $stops[$fromStopId]->sequence ?? null;
        $b = $stops[$toStopId]->sequence ?? null;
        if ($a === null || $b === null || $a >= $b) {
            throw ValidationException::withMessages(['segment' => 'Invalid segment (order).']);
        }
        return [$a, $b, $stops];
    }

    public function seatCount(Trip $trip): int
    {
        return $trip->bus?->seat_count ?? 44;
    }

    public function availableSeats(int $tripId, int $fromStopId, int $toStopId, ?int $limit = null): array
    {
        [$seqFrom, $seqTo, $map] = $this->resolveSegment($tripId, $fromStopId, $toStopId);
        $trip = Trip::with('bus')->findOrFail($tripId);
        $seatCount = $this->seatCount($trip);

        $bookings = Booking::with('seats:booking_id,seat_number')
            ->where('trip_id', $tripId)->activeForAvailability()
            ->get(['id', 'from_stop_id', 'to_stop_id', 'status']);

        $occ = array_fill(1, $seatCount, []);
        foreach ($bookings as $b) {
            $a = $map[$b->from_stop_id]->sequence;
            $c = $map[$b->to_stop_id]->sequence;
            foreach ($b->seats as $seat) {
                $occ[$seat->seat_number][] = [$a, $c];
            }
        }

        $free = [];
        for ($n = 1; $n <= $seatCount; $n++) {
            $hit = false;
            foreach ($occ[$n] as [$a, $c]) {
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

    public function countAvailable(int $tripId, int $fromStopId, int $toStopId): int
    {
        return count($this->availableSeats($tripId, $fromStopId, $toStopId));
    }
}

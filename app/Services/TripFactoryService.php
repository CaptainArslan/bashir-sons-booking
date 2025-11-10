<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\Trip;
use App\Models\TripStop;
use Carbon\Carbon;

class TripFactoryService
{
    public function createFromTimetable(int $timetableId, string $date, array $attrs = []): Trip
    {
        $tt = Timetable::with(['route', 'timetableStops' => fn ($q) => $q->orderBy('sequence')])->findOrFail($timetableId);

        // Calculate departure and arrival datetimes from timetable stops before creating trip
        $originStop = $tt->timetableStops->first();
        $destinationStop = $tt->timetableStops->last();

        $departureDatetime = null;
        $estimatedArrivalDatetime = null;

        if ($originStop && $originStop->departure_time) {
            $departureDatetime = Carbon::parse("{$date} {$originStop->departure_time}");
        }

        if ($destinationStop && $destinationStop->arrival_time) {
            $estimatedArrivalDatetime = Carbon::parse("{$date} {$destinationStop->arrival_time}");
        }

        $trip = Trip::create([
            'timetable_id' => $tt->id,
            'route_id' => $tt->route_id,
            'bus_id' => $attrs['bus_id'] ?? null,
            'departure_date' => $date,
            'departure_datetime' => $departureDatetime,
            'estimated_arrival_datetime' => $estimatedArrivalDatetime,
            'driver_name' => $attrs['driver_name'] ?? null,
            'driver_phone' => $attrs['driver_phone'] ?? null,
            'driver_license' => $attrs['driver_license'] ?? null,
            'driver_cnic' => $attrs['driver_cnic'] ?? null,
            'driver_address' => $attrs['driver_address'] ?? null,
            'status' => 'scheduled',
            'notes' => $attrs['notes'] ?? null,
        ]);

        $rows = [];
        foreach ($tt->timetableStops as $i => $s) {
            $arr = Carbon::parse("{$date} {$s->arrival_time}");
            $dep = Carbon::parse("{$date} {$s->departure_time}");
            $rows[] = [
                'trip_id' => $trip->id,
                'terminal_id' => $s->terminal_id,
                'sequence' => $s->sequence,
                'arrival_at' => $arr,
                'departure_at' => $dep,
                'is_active' => (bool) $s->is_active,
                'is_origin' => $i === 0,
                'is_destination' => $i === (count($tt->timetableStops) - 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        TripStop::insert($rows);

        return $trip->load('stops');
    }
}

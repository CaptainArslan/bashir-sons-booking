<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRouteStopTimeRequest;
use App\Models\RouteStop;
use App\Models\RouteStopTime;
use App\Models\RouteTimetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RouteStopTimeController extends Controller
{
    /**
     * Show the form for creating stop times for a timetable.
     */
    public function create(RouteTimetable $routeTimetable)
    {
        $routeTimetable->load('route.routeStops.terminal');
        
        // Get existing stop times for this timetable
        $existingStopTimes = $routeTimetable->stops()
            ->with('routeStop.terminal')
            ->orderBy('sequence')
            ->get()
            ->keyBy('route_stop_id');

        return view('admin.route-stop-times.create', compact('routeTimetable', 'existingStopTimes'));
    }

    /**
     * Store stop times for a timetable.
     */
    public function store(StoreRouteStopTimeRequest $request, RouteTimetable $routeTimetable)
    {

        DB::beginTransaction();
        try {
            // Delete existing stop times for this timetable
            $routeTimetable->stops()->delete();

            // Create new stop times
            foreach ($request->stop_times as $stopTimeData) {
                RouteStopTime::create([
                    'timetable_id' => $routeTimetable->id,
                    'route_stop_id' => $stopTimeData['route_stop_id'],
                    'sequence' => $stopTimeData['sequence'],
                    'arrival_time' => $stopTimeData['arrival_time'] ?? null,
                    'departure_time' => $stopTimeData['departure_time'] ?? null,
                    'allow_online_booking' => $stopTimeData['allow_online_booking'] ?? true,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.route-timetables.show', $routeTimetable)
                ->with('success', 'Stop times created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create stop times: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing stop times for a timetable.
     */
    public function edit(RouteTimetable $routeTimetable)
    {
        $routeTimetable->load('route.routeStops.terminal');
        
        $stopTimes = $routeTimetable->stops()
            ->with('routeStop.terminal')
            ->orderBy('sequence')
            ->get();

        return view('admin.route-stop-times.edit', compact('routeTimetable', 'stopTimes'));
    }

    /**
     * Update stop times for a timetable.
     */
    public function update(Request $request, RouteTimetable $routeTimetable)
    {
        $request->validate([
            'stop_times' => 'required|array|min:1',
            'stop_times.*.id' => 'required|exists:route_stop_times,id',
            'stop_times.*.route_stop_id' => 'required|exists:route_stops,id',
            'stop_times.*.sequence' => 'required|integer|min:1',
            'stop_times.*.arrival_time' => 'nullable|date_format:H:i',
            'stop_times.*.departure_time' => 'nullable|date_format:H:i',
            'stop_times.*.allow_online_booking' => 'boolean',
        ]);

        // Validate that all route_stop_ids belong to the same route as the timetable
        $routeStopIds = collect($request->stop_times)->pluck('route_stop_id');
        $validRouteStopIds = $routeTimetable->route->routeStops->pluck('id');
        
        if (!$routeStopIds->every(fn($id) => $validRouteStopIds->contains($id))) {
            return back()->withInput()->with('error', 'Invalid route stops provided.');
        }

        // Validate sequence order
        $sequences = collect($request->stop_times)->pluck('sequence')->sort();
        $expectedSequences = range(1, count($request->stop_times));
        
        if ($sequences->values()->toArray() !== $expectedSequences) {
            return back()->withInput()->with('error', 'Stop sequences must be consecutive starting from 1.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->stop_times as $stopTimeData) {
                RouteStopTime::where('id', $stopTimeData['id'])
                    ->update([
                        'route_stop_id' => $stopTimeData['route_stop_id'],
                        'sequence' => $stopTimeData['sequence'],
                        'arrival_time' => $stopTimeData['arrival_time'] ?? null,
                        'departure_time' => $stopTimeData['departure_time'] ?? null,
                        'allow_online_booking' => $stopTimeData['allow_online_booking'] ?? true,
                    ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.route-timetables.show', $routeTimetable)
                ->with('success', 'Stop times updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update stop times: ' . $e->getMessage());
        }
    }

    /**
     * Remove stop times for a timetable.
     */
    public function destroy(RouteTimetable $routeTimetable)
    {
        DB::beginTransaction();
        try {
            $routeTimetable->stops()->delete();
            DB::commit();

            return redirect()
                ->route('admin.route-timetables.show', $routeTimetable)
                ->with('success', 'Stop times deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete stop times: ' . $e->getMessage());
        }
    }

    /**
     * Generate stop times automatically based on route stops and timetable.
     */
    public function generate(RouteTimetable $routeTimetable)
    {
        $route = $routeTimetable->route;
        $routeStops = $route->routeStops()->with('terminal')->orderBy('sequence')->get();
        
        if ($routeStops->isEmpty()) {
            return back()->with('error', 'No route stops found for this route.');
        }

        $baseDepartureTime = Carbon::parse($routeTimetable->departure_time);
        $stopTimes = [];

        foreach ($routeStops as $index => $routeStop) {
            $arrivalTime = null;
            $departureTime = null;

            if ($index === 0) {
                // First stop - use the base departure time
                $departureTime = $baseDepartureTime->format('H:i');
            } else {
                // Calculate arrival time based on previous stop's travel time
                $previousStop = $routeStops[$index - 1];
                $travelTime = $previousStop->approx_travel_time ?? 30; // Default 30 minutes
                $arrivalTime = $baseDepartureTime->addMinutes($travelTime)->format('H:i');
                
                // Departure time is same as arrival for intermediate stops
                $departureTime = $arrivalTime;
            }

            $stopTimes[] = [
                'route_stop_id' => $routeStop->id,
                'sequence' => $routeStop->sequence,
                'arrival_time' => $arrivalTime,
                'departure_time' => $departureTime,
                'allow_online_booking' => true,
            ];
        }

        // Update the timetable's arrival time if not set
        if (!$routeTimetable->arrival_time && !empty($stopTimes)) {
            $lastStopTime = end($stopTimes);
            $routeTimetable->update(['arrival_time' => $lastStopTime['departure_time']]);
        }

        return view('admin.route-stop-times.create', [
            'routeTimetable' => $routeTimetable,
            'existingStopTimes' => collect(),
            'generatedStopTimes' => $stopTimes
        ]);
    }
}

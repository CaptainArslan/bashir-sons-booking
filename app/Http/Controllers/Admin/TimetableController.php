<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Models\Timetable;
use App\Models\TimetableStop;
use App\Models\Route;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.timetables.index');
    }

    /**
     * Get timetable data for AJAX request
     */
    public function getData(): JsonResponse
    {
        $timetables = Timetable::with(['route', 'timetableStops.terminal'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($timetable) {
                $stops = $timetable->timetableStops()
                    ->orderBy('sequence')
                    ->get()
                    ->map(function ($stop) {
                        return [
                            'id' => $stop->terminal_id,
                            'name' => $stop->terminal->name,
                            'arrival_time' => $stop->arrival_time,
                            'departure_time' => $stop->departure_time,
                            'sequence' => $stop->sequence,
                        ];
                    });

                $firstStop = $stops->first();
                $lastStop = $stops->last();

                return [
                    'id' => $timetable->id,
                    'route_name' => $timetable->route->name ?? 'N/A',
                    'route_code' => $timetable->route->code ?? 'N/A',
                    'start_terminal' => $firstStop ? $firstStop['name'] : 'N/A',
                    'end_terminal' => $lastStop ? $lastStop['name'] : 'N/A',
                    'start_departure_time' => $timetable->start_departure_time,
                    'total_stops' => $stops->count(),
                    'status' => $timetable->is_active ? 'active' : 'inactive',
                    'created_at' => $timetable->created_at->format('Y-m-d H:i:s'),
                    'stops' => $stops,
                ];
            });

        return response()->json(['data' => $timetables]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $routes = Route::with(['routeStops.terminal'])
            ->where('status', 'active')
            ->get()
            ->map(function ($route) {
                return [
                    'id' => $route->id,
                    'name' => $route->name,
                    'code' => $route->code,
                    'stops' => $route->routeStops->map(function ($stop) {
                        return [
                            'id' => $stop->terminal_id,
                            'name' => $stop->terminal->name,
                            'sequence' => $stop->sequence
                        ];
                    })
                ];
            });

        return view('admin.timetables.create', compact('routes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimetableRequest $request): RedirectResponse
    {
        $route = Route::with(['routeStops.terminal'])->findOrFail($request->route_id);
        $routeStops = $route->routeStops()->orderBy('sequence')->get();

        if ($routeStops->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'Selected route has no stops configured.']);
        }

        // Process each timetable from the form
        foreach ($request->timetables as $timetableIndex => $timetableData) {
            // Get the first stop's departure time to set as start_departure_time
            $firstStopData = $timetableData['stops'][0];
            $startDepartureTime = $firstStopData['departure_time'] ?? null;
            
            if (!$startDepartureTime) {
                return redirect()->back()->withErrors(['error' => 'First stop must have a departure time.']);
            }

            $timetable = Timetable::create([
                'route_id' => $route->id,
                'name' => $route->name . ' - Trip ' . ($timetableIndex + 1),
                'start_departure_time' => $startDepartureTime,
                'is_active' => true,
            ]);

            // Create timetable stops with user-provided times
            foreach ($timetableData['stops'] as $stopIndex => $stopData) {
                $isFirstStop = $stopIndex === 0;
                $isLastStop = $stopIndex === count($timetableData['stops']) - 1;
                
                $arrivalTime = null;
                $departureTime = null;

                // Set arrival time (not for first stop)
                if (!$isFirstStop && isset($stopData['arrival_time'])) {
                    $arrivalTime = $stopData['arrival_time'];
                }

                // Set departure time (not for last stop)
                if (!$isLastStop && isset($stopData['departure_time'])) {
                    $departureTime = $stopData['departure_time'];
                }

                TimetableStop::create([
                    'timetable_id' => $timetable->id,
                    'terminal_id' => $stopData['stop_id'],
                    'sequence' => $stopData['sequence'],
                    'arrival_time' => $arrivalTime,
                    'departure_time' => $departureTime,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.timetables.index')
            ->with('success', 'Timetables created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Timetable $timetable): View
    {
        $timetable->load(['route', 'timetableStops.terminal']);
        $timetableStops = $timetable->timetableStops()->orderBy('sequence')->get();
        
        return view('admin.timetables.show', compact('timetable', 'timetableStops'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timetable $timetable): View
    {
        $timetable->load(['route', 'timetableStops.terminal']);
        $timetableStops = $timetable->timetableStops()->orderBy('sequence')->get();
        
        return view('admin.timetables.edit', compact('timetable', 'timetableStops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimetableRequest $request, Timetable $timetable): RedirectResponse
    {

        $timetable->update([
            'name' => $request->name,
            'start_departure_time' => $request->start_departure_time,
            'end_arrival_time' => $request->end_arrival_time,
            'is_active' => $request->has('is_active'),
        ]);

        // Update timetable stops
        foreach ($request->stops as $stopData) {
            $timetableStop = TimetableStop::find($stopData['id']);
            if ($timetableStop) {
                $timetableStop->update([
                    'arrival_time' => $stopData['arrival_time'],
                    'departure_time' => $stopData['departure_time'],
                ]);
            }
        }

        return redirect()->route('admin.timetables.index')
            ->with('success', 'Timetable updated successfully!');
    }

    /**
     * Toggle timetable status (active/inactive)
     */
    public function toggleStatus(Request $request, Timetable $timetable): JsonResponse
    {
        try {
            $newStatus = $request->input('status');
            
            if (!in_array($newStatus, ['active', 'inactive'])) {
                return response()->json(['success' => false, 'message' => 'Invalid status provided.'], 400);
            }
            
            $timetable->update([
                'is_active' => $newStatus === 'active'
            ]);
            
            $action = $newStatus === 'active' ? 'activated' : 'deactivated';
            return response()->json([
                'success' => true, 
                'message' => "Timetable {$action} successfully!"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating timetable status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable): JsonResponse
    {
        try {
            $timetable->delete();
            return response()->json(['success' => true, 'message' => 'Timetable deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting timetable: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get route stops for AJAX request
     */
    public function getRouteStops(Route $route): JsonResponse
    {
        $stops = $route->routeStops()
            ->with('terminal')
            ->orderBy('sequence')
            ->get()
            ->map(function ($stop) {
                return [
                    'id' => $stop->terminal_id,
                    'name' => $stop->terminal->name,
                    'sequence' => $stop->sequence,
                ];
            });

        return response()->json($stops);
    }
}

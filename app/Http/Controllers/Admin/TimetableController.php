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
     * Get timetable data for DataTables
     */
    public function getData(): JsonResponse
    {
        $timetables = Timetable::with(['route', 'timetableStops.terminal'])
            ->select('timetables.*');

        return DataTables::of($timetables)
            ->addColumn('route_name', function ($timetable) {
                return $timetable->route->name ?? 'N/A';
            })
            ->addColumn('route_code', function ($timetable) {
                return $timetable->route->code ?? 'N/A';
            })
            ->addColumn('start_terminal', function ($timetable) {
                $firstStop = $timetable->timetableStops()->orderBy('sequence')->first();
                return $firstStop ? $firstStop->terminal->name : 'N/A';
            })
            ->addColumn('end_terminal', function ($timetable) {
                $lastStop = $timetable->timetableStops()->orderByDesc('sequence')->first();
                return $lastStop ? $lastStop->terminal->name : 'N/A';
            })
            ->addColumn('total_stops', function ($timetable) {
                return $timetable->timetableStops()->count();
            })
            ->addColumn('status', function ($timetable) {
                return $timetable->is_active 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($timetable) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<a href="' . route('admin.timetables.show', $timetable->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                $actions .= '<a href="' . route('admin.timetables.edit', $timetable->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteTimetable(' . $timetable->id . ')" title="Delete"><i class="fas fa-trash"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
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

        $startTime = Carbon::parse($request->start_time);
        $timeInterval = $request->time_interval; // in minutes

        // Create timetables
        for ($i = 0; $i < $request->departure_count; $i++) {
            $departureTime = $startTime->copy()->addMinutes($i * $timeInterval);
            
            $timetable = Timetable::create([
                'route_id' => $route->id,
                'name' => $route->name . ' - Trip ' . ($i + 1),
                'start_departure_time' => $departureTime->format('H:i:s'),
                'is_active' => true,
            ]);

            // Create timetable stops
            foreach ($routeStops as $index => $routeStop) {
                $isFirstStop = $index === 0;
                $isLastStop = $index === $routeStops->count() - 1;
                
                $arrivalTime = null;
                $departureTimeStr = null;
                $currentTime = $departureTime->copy();

                if ($isFirstStop) {
                    // First stop: only departure time
                    $departureTimeStr = $currentTime->format('H:i:s');
                } elseif ($isLastStop) {
                    // Last stop: only arrival time
                    $arrivalTime = $currentTime->addMinutes(30)->format('H:i:s'); // Add 30 minutes for last stop
                } else {
                    // Middle stops: both arrival and departure
                    $arrivalTime = $currentTime->addMinutes(15)->format('H:i:s'); // Add 15 minutes for arrival
                    $departureTimeStr = $currentTime->addMinutes(5)->format('H:i:s'); // Add 5 minutes for departure
                }

                TimetableStop::create([
                    'timetable_id' => $timetable->id,
                    'terminal_id' => $routeStop->terminal_id,
                    'sequence' => $routeStop->sequence,
                    'arrival_time' => $arrivalTime,
                    'departure_time' => $departureTimeStr,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.timetables.index')
            ->with('success', 'Timetables generated successfully!');
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

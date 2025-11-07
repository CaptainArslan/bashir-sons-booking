<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Models\Route;
use App\Models\Timetable;
use App\Models\TimetableStop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
        $this->authorize('view timetables');

        $user = auth()->user();
        $hasEditPermission = $user->can('edit timetables');
        $hasDeletePermission = $user->can('delete timetables');

        $timetables = Timetable::with(['route', 'timetableStops.terminal'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($timetable) use ($hasEditPermission, $hasDeletePermission) {
                $stops = $timetable->timetableStops()
                    ->orderBy('sequence')
                    ->get()
                    ->map(function ($stop) {
                        return [
                            'id' => $stop->id, // TimetableStop ID for toggle functionality
                            'terminal_id' => $stop->terminal_id,
                            'name' => $stop->terminal->name,
                            'arrival_time' => $stop->arrival_time ?? null,
                            'departure_time' => $stop->departure_time ?? null,
                            'sequence' => $stop->sequence,
                            'is_active' => $stop->is_active,
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
                    'can_edit' => $hasEditPermission,
                    'can_delete' => $hasDeletePermission,
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
                            'sequence' => $stop->sequence,
                        ];
                    }),
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

        // dd($request->all());
        DB::beginTransaction();

        try {
            foreach ($request->timetables as $timetableIndex => $timetableData) {

                $firstStopData = $timetableData['stops'][0];
                $startDepartureTime = $firstStopData['departure_time'] ?? null;

                if (! $startDepartureTime) {
                    DB::rollBack();

                    return redirect()->back()->withErrors(['error' => 'First stop must have a departure time.']);
                }

                // Auto-calculate end_arrival_time from last stop's arrival
                $lastStopData = end($timetableData['stops']);
                $endArrivalTime = $lastStopData['arrival_time'] ?? null;

                // Format times properly (add :00 seconds if needed)
                $startDepartureTimeFormatted = str_contains($startDepartureTime, ':') && substr_count($startDepartureTime, ':') === 1
                    ? $startDepartureTime.':00'
                    : $startDepartureTime;
                $endArrivalTimeFormatted = $endArrivalTime && str_contains($endArrivalTime, ':') && substr_count($endArrivalTime, ':') === 1
                    ? $endArrivalTime.':00'
                    : $endArrivalTime;

                $timetable = Timetable::create([
                    'route_id' => $route->id,
                    'name' => $route->name.' - Trip '.($timetableIndex + 1),
                    'start_departure_time' => $startDepartureTimeFormatted,
                    'end_arrival_time' => $endArrivalTimeFormatted,
                    'is_active' => true,
                ]);

                foreach ($timetableData['stops'] as $stopIndex => $stopData) {
                    // Format times properly (add :00 seconds if needed)
                    // First stop's arrival time is optional - normalize empty strings to null
                    $arrivalTime = $stopData['arrival_time'] ?? null;
                    $departureTime = $stopData['departure_time'] ?? null;

                    // Normalize empty strings to null for optional first stop arrival time
                    if ($arrivalTime === '' || $arrivalTime === null) {
                        $arrivalTime = null;
                    } elseif (str_contains($arrivalTime, ':') && substr_count($arrivalTime, ':') === 1) {
                        $arrivalTime = $arrivalTime.':00';
                    }

                    if ($departureTime && str_contains($departureTime, ':') && substr_count($departureTime, ':') === 1) {
                        $departureTime = $departureTime.':00';
                    }

                    TimetableStop::create([
                        'timetable_id' => $timetable->id,
                        'terminal_id' => $stopData['stop_id'],
                        'sequence' => $stopData['sequence'],
                        'arrival_time' => $arrivalTime, // null for empty first stop arrival time
                        'departure_time' => $departureTime,
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'error' => 'Something went wrong while saving timetables.',
                'details' => $e->getMessage(), // Remove in production
            ]);
        }

        return redirect()->route('admin.timetables.index')
            ->with('success', 'Timetables created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Timetable $timetable): View
    {
        $this->authorize('view timetables');

        $timetable->load(['route', 'timetableStops.terminal']);
        $timetableStops = $timetable->timetableStops()->orderBy('sequence')->get();

        return view('admin.timetables.show', compact('timetable', 'timetableStops'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timetable $timetable): View
    {
        $this->authorize('edit timetables');

        $timetable->load(['route', 'timetableStops.terminal']);
        $timetableStops = $timetable->timetableStops()->orderBy('sequence')->get();

        return view('admin.timetables.edit', compact('timetable', 'timetableStops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimetableRequest $request, Timetable $timetable): RedirectResponse
    {
        $this->authorize('edit timetables');

        DB::beginTransaction();

        try {
            // Update timetable stops first
            foreach ($request->stops as $stopData) {
                $timetableStop = TimetableStop::find($stopData['id']);
                if ($timetableStop && $timetableStop->timetable_id === $timetable->id) {
                    // Format time properly (add :00 seconds if H:i format)
                    // First stop's arrival time is optional - normalize empty strings to null
                    $arrivalTime = $stopData['arrival_time'] ?? null;
                    $departureTime = $stopData['departure_time'] ?? null;

                    // Normalize empty strings to null for optional first stop arrival time
                    if ($arrivalTime === '' || $arrivalTime === null) {
                        $arrivalTime = null;
                    } elseif (str_contains($arrivalTime, ':') && substr_count($arrivalTime, ':') === 1) {
                        $arrivalTime = $arrivalTime.':00';
                    }

                    if ($departureTime && str_contains($departureTime, ':') && substr_count($departureTime, ':') === 1) {
                        $departureTime = $departureTime.':00';
                    }

                    $timetableStop->update([
                        'arrival_time' => $arrivalTime, // null for empty first stop arrival time
                        'departure_time' => $departureTime,
                    ]);
                }
            }

            // Query fresh timetable stops from database (after updates)
            // Query raw values directly to avoid accessor formatting
            $startDepartureTime = TimetableStop::where('timetable_id', $timetable->id)
                ->orderBy('sequence')
                ->value('departure_time');

            $endArrivalTime = TimetableStop::where('timetable_id', $timetable->id)
                ->orderByDesc('sequence')
                ->value('arrival_time');

            // Update timetable with auto-calculated times
            $timetable->update([
                'name' => $request->name,
                'start_departure_time' => $startDepartureTime,
                'end_arrival_time' => $endArrivalTime,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            return redirect()->route('admin.timetables.index')
                ->with('success', 'Timetable updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update timetable: '.$e->getMessage()]);
        }
    }

    /**
     * Toggle timetable status (active/inactive)
     */
    public function toggleStatus(Request $request, Timetable $timetable): JsonResponse
    {
        try {
            $this->authorize('edit timetables');

            $newStatus = $request->input('status');

            if (! in_array($newStatus, ['active', 'inactive'])) {
                return response()->json(['success' => false, 'message' => 'Invalid status provided.'], 400);
            }

            $timetable->update([
                'is_active' => $newStatus === 'active',
            ]);

            $action = $newStatus === 'active' ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Timetable {$action} successfully!",
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to edit timetables.'], 403);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating timetable status: '.$e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable): JsonResponse
    {
        try {
            $this->authorize('delete timetables');

            // Check if timetable has associated trips
            if ($timetable->trips()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete timetable. It has associated trips. Please delete the trips first.',
                ], 400);
            }

            $timetable->delete();

            return response()->json(['success' => true, 'message' => 'Timetable deleted successfully!']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete timetables.'], 403);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting timetable: '.$e->getMessage()], 500);
        }
    }

    /**
     * Toggle timetable stop status (active/inactive)
     */
    public function toggleStopStatus(Request $request, Timetable $timetable, TimetableStop $timetableStop): JsonResponse
    {
        try {
            $this->authorize('edit timetables');

            // Verify the stop belongs to this timetable
            if ($timetableStop->timetable_id !== $timetable->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timetable stop does not belong to this timetable.',
                ], 400);
            }

            // Prevent disabling first or last stop
            $firstStop = $timetable->timetableStops()->orderBy('sequence')->first();
            $lastStop = $timetable->timetableStops()->orderByDesc('sequence')->first();

            if (($timetableStop->id === $firstStop->id || $timetableStop->id === $lastStop->id) && $timetableStop->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot disable the first or last stop of a timetable.',
                ], 400);
            }

            $timetableStop->update([
                'is_active' => ! $timetableStop->is_active,
            ]);

            $action = $timetableStop->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Timetable stop {$action} successfully!",
                'is_active' => $timetableStop->is_active,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit timetables.',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating timetable stop status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle all timetable stops status (activate/deactivate all)
     */
    public function toggleAllStops(Request $request, Timetable $timetable): JsonResponse
    {
        try {
            $this->authorize('edit timetables');

            $status = $request->input('status'); // 'active' or 'inactive'
            $isActive = $status === 'active';

            if (! in_array($status, ['active', 'inactive'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided. Use "active" or "inactive".',
                ], 400);
            }

            // Get all stops except first and last (which must remain active)
            $firstStop = $timetable->timetableStops()->orderBy('sequence')->first();
            $lastStop = $timetable->timetableStops()->orderByDesc('sequence')->first();

            // Update all intermediate stops
            $updatedCount = TimetableStop::where('timetable_id', $timetable->id)
                ->where('id', '!=', $firstStop->id)
                ->where('id', '!=', $lastStop->id)
                ->update(['is_active' => $isActive]);

            $action = $isActive ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Successfully {$action} {$updatedCount} stops! (First and last stops are always active)",
                'updated_count' => $updatedCount,
                'status' => $status,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit timetables.',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating timetable stops: '.$e->getMessage(),
            ], 500);
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

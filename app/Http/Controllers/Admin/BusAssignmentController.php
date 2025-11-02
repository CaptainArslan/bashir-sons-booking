<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusAssignment;
use App\Models\Trip;
use App\Models\TripStop;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BusAssignmentController extends Controller
{
    /**
     * Display a listing of bus assignments for a trip.
     */
    public function index(Request $request): View|JsonResponse
    {
        $tripId = $request->get('trip_id');

        // Load recent trips for dropdown
        $recentTrips = Trip::with(['route'])
            ->where('departure_date', '>=', now()->subDays(7))
            ->where('departure_date', '<=', now()->addDays(30))
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_datetime', 'desc')
            ->limit(50)
            ->get();

        if (! $tripId) {
            return view('admin.bus-assignments.index', compact('recentTrips'));
        }

        $trip = Trip::with(['route', 'stops.terminal', 'busAssignments' => function ($query) {
            $query->with(['fromTripStop.terminal', 'toTripStop.terminal', 'bus', 'assignedBy']);
        }])->findOrFail($tripId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'trip' => $trip,
                'assignments' => $trip->busAssignments,
            ]);
        }

        $buses = Bus::where('status', 'active')->orderBy('name')->get(['id', 'name', 'registration_number']);

        return view('admin.bus-assignments.index', compact('trip', 'buses', 'recentTrips'));
    }

    /**
     * Show the form for creating a new bus assignment.
     */
    public function create(Request $request): View
    {
        $tripId = $request->get('trip_id');

        if (! $tripId) {
            abort(404, 'Trip ID is required');
        }

        $trip = Trip::with(['stops.terminal'])->findOrFail($tripId);
        $buses = Bus::where('status', 'active')->orderBy('name')->get(['id', 'name', 'registration_number']);

        return view('admin.bus-assignments.create', compact('trip', 'buses'));
    }

    /**
     * Store a newly created bus assignment.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_trip_stop_id' => 'required|exists:trip_stops,id',
            'to_trip_stop_id' => 'required|exists:trip_stops,id|different:from_trip_stop_id',
            'bus_id' => 'required|exists:buses,id',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:20',
            'driver_cnic' => 'required|string|max:50',
            'driver_license' => 'required|string|max:100',
            'driver_address' => 'nullable|string|max:500',
            'host_name' => 'nullable|string|max:255',
            'host_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validate that trip stops belong to the trip and sequence is correct
        $fromStop = TripStop::findOrFail($validated['from_trip_stop_id']);
        $toStop = TripStop::findOrFail($validated['to_trip_stop_id']);

        if ($fromStop->trip_id != $validated['trip_id'] || $toStop->trip_id != $validated['trip_id']) {
            throw ValidationException::withMessages([
                'from_trip_stop_id' => 'Trip stops must belong to the selected trip.',
            ]);
        }

        if ($fromStop->sequence >= $toStop->sequence) {
            throw ValidationException::withMessages([
                'to_trip_stop_id' => 'Destination must come after origin in the trip sequence.',
            ]);
        }

        // Check for overlapping assignments (optional - can be removed if multiple buses allowed on same segment)
        $overlapping = BusAssignment::where('trip_id', $validated['trip_id'])
            ->where(function ($query) use ($fromStop, $toStop) {
                $query->where(function ($q) use ($fromStop) {
                    // New assignment starts within existing segment
                    $q->where('from_trip_stop_id', '<=', $fromStop->id)
                        ->where('to_trip_stop_id', '>', $fromStop->id);
                })->orWhere(function ($q) use ($toStop) {
                    // New assignment ends within existing segment
                    $q->where('from_trip_stop_id', '<', $toStop->id)
                        ->where('to_trip_stop_id', '>=', $toStop->id);
                })->orWhere(function ($q) use ($fromStop, $toStop) {
                    // New assignment completely contains existing segment
                    $q->where('from_trip_stop_id', '>=', $fromStop->id)
                        ->where('to_trip_stop_id', '<=', $toStop->id);
                });
            })
            ->exists();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'from_trip_stop_id' => 'An assignment already exists for this segment or overlaps with it.',
            ]);
        }

        DB::transaction(function () use ($validated) {
            BusAssignment::create([
                'trip_id' => $validated['trip_id'],
                'from_trip_stop_id' => $validated['from_trip_stop_id'],
                'to_trip_stop_id' => $validated['to_trip_stop_id'],
                'bus_id' => $validated['bus_id'],
                'driver_name' => $validated['driver_name'],
                'driver_phone' => $validated['driver_phone'],
                'driver_cnic' => $validated['driver_cnic'],
                'driver_license' => $validated['driver_license'],
                'driver_address' => $validated['driver_address'] ?? null,
                'host_name' => $validated['host_name'] ?? null,
                'host_phone' => $validated['host_phone'] ?? null,
                'assigned_by_user_id' => Auth::id(),
                'assigned_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus assignment created successfully.',
            ]);
        }

        return redirect()->route('admin.bus-assignments.index', ['trip_id' => $validated['trip_id']])
            ->with('success', 'Bus assignment created successfully.');
    }

    /**
     * Display the specified bus assignment.
     */
    public function show(BusAssignment $busAssignment): View|JsonResponse
    {
        $busAssignment->load(['trip.route', 'fromTripStop.terminal', 'toTripStop.terminal', 'bus', 'assignedBy']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'assignment' => $busAssignment,
            ]);
        }

        return view('admin.bus-assignments.show', compact('busAssignment'));
    }

    /**
     * Show the form for editing the specified bus assignment.
     */
    public function edit(BusAssignment $busAssignment): View
    {
        $busAssignment->load(['trip.stops.terminal']);
        $buses = Bus::where('status', 'active')->orderBy('name')->get(['id', 'name', 'registration_number']);

        return view('admin.bus-assignments.edit', compact('busAssignment', 'buses'));
    }

    /**
     * Update the specified bus assignment.
     */
    public function update(Request $request, BusAssignment $busAssignment): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:20',
            'driver_cnic' => 'required|string|max:50',
            'driver_license' => 'required|string|max:100',
            'driver_address' => 'nullable|string|max:500',
            'host_name' => 'nullable|string|max:255',
            'host_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $busAssignment->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus assignment updated successfully.',
                'assignment' => $busAssignment->load(['fromTripStop.terminal', 'toTripStop.terminal', 'bus']),
            ]);
        }

        return redirect()->route('admin.bus-assignments.index', ['trip_id' => $busAssignment->trip_id])
            ->with('success', 'Bus assignment updated successfully.');
    }

    /**
     * Remove the specified bus assignment.
     */
    public function destroy(BusAssignment $busAssignment): JsonResponse|RedirectResponse
    {
        $tripId = $busAssignment->trip_id;
        $busAssignment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bus assignment deleted successfully.',
            ]);
        }

        return redirect()->route('admin.bus-assignments.index', ['trip_id' => $tripId])
            ->with('success', 'Bus assignment deleted successfully.');
    }

    /**
     * Get available trip stops for a trip (for AJAX dropdowns).
     */
    public function getTripStops(Request $request, int $tripId): JsonResponse
    {
        $trip = Trip::with(['stops.terminal'])->findOrFail($tripId);
        $stops = $trip->stops->map(function ($stop) {
            return [
                'id' => $stop->id,
                'terminal_id' => $stop->terminal_id,
                'terminal_name' => $stop->terminal->name,
                'terminal_code' => $stop->terminal->code,
                'sequence' => $stop->sequence,
                'label' => sprintf('%s (%s) - Sequence %d', $stop->terminal->name, $stop->terminal->code, $stop->sequence),
            ];
        });

        return response()->json([
            'success' => true,
            'stops' => $stops,
        ]);
    }
}

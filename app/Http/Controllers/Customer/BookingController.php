<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Terminal;
use App\Services\RouteTimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected RouteTimetableService $timetableService;

    public function __construct(RouteTimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    /**
     * Show the booking form.
     */
    public function index()
    {
        $terminals = Terminal::where('status', 'active')
            ->with('city')
            ->orderBy('name')
            ->get();

        return view('customer.booking.index', compact('terminals'));
    }

    /**
     * Get available routes between two terminals.
     */
    public function getAvailableRoutes(Request $request)
    {
        $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'travel_date' => 'required|date|after_or_equal:today',
        ]);

        $fromTerminalId = $request->from_terminal_id;
        $toTerminalId = $request->to_terminal_id;
        $travelDate = Carbon::parse($request->travel_date);

        // Find routes that have both terminals
        $routes = Route::where('status', 'active')
            ->whereHas('routeStops', function ($query) use ($fromTerminalId) {
                $query->where('terminal_id', $fromTerminalId);
            })
            ->whereHas('routeStops', function ($query) use ($toTerminalId) {
                $query->where('terminal_id', $toTerminalId);
            })
            ->with(['routeStops.terminal.city', 'activeTimetables'])
            ->get();

        $availableRoutes = [];

        foreach ($routes as $route) {
            // Get the route stops for from and to terminals
            $fromStop = $route->routeStops->where('terminal_id', $fromTerminalId)->first();
            $toStop = $route->routeStops->where('terminal_id', $toTerminalId)->first();

            // Check if from stop comes before to stop in the route
            if ($fromStop && $toStop && $fromStop->sequence < $toStop->sequence) {
                // Check if there are available timetables for this route
                if ($route->activeTimetables->count() > 0) {
                    $availableRoutes[] = [
                        'route' => $route,
                        'from_stop' => $fromStop,
                        'to_stop' => $toStop,
                        'timetables_count' => $route->activeTimetables->count(),
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'routes' => $availableRoutes,
            'travel_date' => $travelDate->format('Y-m-d'),
        ]);
    }

    /**
     * Search for available trips.
     */
    public function search(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
            'travel_date' => 'required|date|after_or_equal:today',
        ]);

        $route = Route::findOrFail($request->route_id);
        $travelDate = Carbon::parse($request->travel_date);

        $availableTrips = $this->timetableService->getAvailableTrips(
            $route,
            $request->from_stop_id,
            $request->to_stop_id,
            $travelDate
        );

        return view('customer.booking.results', [
            'route' => $route,
            'fromStopId' => $request->from_stop_id,
            'toStopId' => $request->to_stop_id,
            'travelDate' => $travelDate,
            'availableTrips' => $availableTrips,
        ]);
    }

    /**
     * Show trip details for booking.
     */
    public function show(Request $request, Route $route)
    {
        $request->validate([
            'timetable_id' => 'required|exists:route_timetables,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
            'travel_date' => 'required|date|after_or_equal:today',
        ]);

        $timetable = $route->timetables()->findOrFail($request->timetable_id);
        $travelDate = Carbon::parse($request->travel_date);

        // Verify the trip is still available
        if (!$this->timetableService->isTripAvailableForBooking(
            $timetable,
            $request->from_stop_id,
            $request->to_stop_id,
            $travelDate
        )) {
            return redirect()->route('customer.booking.index')
                ->with('error', 'This trip is no longer available for booking.');
        }

        $fromStop = $timetable->stopsOrdered()
            ->where('route_stop_id', $request->from_stop_id)
            ->with('routeStop.terminal')
            ->first();

        $toStop = $timetable->stopsOrdered()
            ->where('route_stop_id', $request->to_stop_id)
            ->with('routeStop.terminal')
            ->first();

        return view('customer.booking.details', [
            'route' => $route,
            'timetable' => $timetable,
            'fromStop' => $fromStop,
            'toStop' => $toStop,
            'travelDate' => $travelDate,
        ]);
    }

    /**
     * Process the booking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'timetable_id' => 'required|exists:route_timetables,id',
            'from_stop_id' => 'required|exists:route_stops,id',
            'to_stop_id' => 'required|exists:route_stops,id',
            'travel_date' => 'required|date|after_or_equal:today',
            'passenger_name' => 'required|string|max:255',
            'passenger_phone' => 'required|string|max:20',
            'passenger_email' => 'nullable|email|max:255',
            'seat_preference' => 'nullable|string|max:255',
        ]);

        // Here you would implement the actual booking logic
        // For now, we'll just return a success message
        
        return redirect()->route('customer.booking.index')
            ->with('success', 'Booking request submitted successfully! You will receive a confirmation shortly.');
    }
}

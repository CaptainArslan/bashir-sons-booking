<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Route;
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
        $routes = Route::where('status', 'active')
            // ->with(['firstStop', 'lastStop'])
            ->get();

        return view('customer.booking.index', compact('routes'));
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

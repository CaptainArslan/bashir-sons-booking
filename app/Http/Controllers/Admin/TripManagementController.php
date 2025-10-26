<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TripStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignBusToTripRequest;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use App\Services\TripLifecycleService;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripManagementController extends Controller
{
    public function __construct(
        private TripService $tripService,
        private TripLifecycleService $lifecycleService
    ) {}

    public function index(Request $request)
    {
        $query = Trip::with(['route', 'bus', 'timetable']);

        // Filters
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('departure_date', $request->date);
        }

        if ($request->filled('bus_assignment')) {
            if ($request->bus_assignment === 'assigned') {
                $query->whereNotNull('bus_id');
            } else {
                $query->whereNull('bus_id');
            }
        }

        $trips = $query->orderBy('departure_datetime', 'desc')->paginate(20);

        $routes = Route::where('status', 'active')->get();
        $buses = Bus::where('status', 'active')->get();
        $statuses = TripStatusEnum::cases();

        // Statistics
        $stats = [
            'total_trips' => Trip::count(),
            'pending_trips' => Trip::where('status', TripStatusEnum::Pending)->count(),
            'scheduled_trips' => Trip::where('status', TripStatusEnum::Scheduled)->count(),
            'ongoing_trips' => Trip::where('status', TripStatusEnum::Ongoing)->count(),
            'without_bus' => Trip::whereNull('bus_id')->count(),
        ];

        return view('admin.trips.index', compact('trips', 'routes', 'buses', 'statuses', 'stats'));
    }

    public function show(int $id)
    {
        $trip = Trip::with([
            'route.routeStops.terminal',
            'bus.busLayout',
            'timetable',
            'bookings.bookingSeats',
            'bookings.user',
            'expenses',
        ])->findOrFail($id);

        $statistics = $this->tripService->getTripStatistics($id);
        $availableBuses = Bus::where('status', 'active')->get();

        return view('admin.trips.show', compact('trip', 'statistics', 'availableBuses'));
    }

    public function assignBus(AssignBusToTripRequest $request, int $id)
    {
        try {
            $this->tripService->assignBus($id, $request->bus_id);

            return redirect()->back()->with('success', 'Bus assigned successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        try {
            $status = TripStatusEnum::from($request->status);
            $this->tripService->updateStatus($id, $status);

            return redirect()->back()->with('success', 'Trip status updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function start(int $id)
    {
        try {
            $this->tripService->startTrip($id);

            return redirect()->back()->with('success', 'Trip started successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function complete(int $id)
    {
        try {
            $this->tripService->completeTrip($id);

            return redirect()->back()->with('success', 'Trip completed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->tripService->cancelTrip($id, $request->reason);

            return redirect()->back()->with('success', 'Trip cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function generateTrips(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $count = $this->tripService->generateTripsFromTimetables(
                $request->start_date,
                $request->end_date
            );

            return redirect()->back()->with('success', "{$count} trips generated successfully");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function requiresBusAssignment()
    {
        $trips = $this->tripService->getTripsRequiringBusAssignment(7);
        $availableBuses = Bus::where('status', 'active')->get();

        return view('admin.trips.requires-bus', compact('trips', 'availableBuses'));
    }

    public function dashboard()
    {
        $stats = $this->lifecycleService->getLifecycleStatistics();
        $attention = $this->lifecycleService->getTripsRequiringAttention();

        return view('admin.trips.dashboard', compact('stats', 'attention'));
    }
}

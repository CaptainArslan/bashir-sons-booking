<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Route;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingManagementController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    public function index(Request $request)
    {
        $query = Booking::with(['trip.route', 'user', 'fromStop.terminal', 'toStop.terminal', 'bookingSeats']);

        // Filters
        if ($request->filled('booking_number')) {
            $query->where('booking_number', 'like', '%'.$request->booking_number.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('route_id')) {
            $query->whereHas('trip', function ($q) use ($request) {
                $q->where('route_id', $request->route_id);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date')) {
            $query->whereHas('trip', function ($q) use ($request) {
                $q->whereDate('departure_date', $request->date);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(20);

        $routes = Route::where('status', 'active')->get();
        $statuses = BookingStatusEnum::cases();
        $types = BookingTypeEnum::cases();

        // Statistics
        $stats = [
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::where('status', BookingStatusEnum::Confirmed)->count(),
            'pending_bookings' => Booking::where('status', BookingStatusEnum::Pending)->count(),
            'cancelled_bookings' => Booking::where('status', BookingStatusEnum::Cancelled)->count(),
            'total_revenue' => Booking::where('status', BookingStatusEnum::Confirmed)->sum('final_amount'),
        ];

        return view('admin.bookings.index', compact('bookings', 'routes', 'statuses', 'types', 'stats'));
    }

    public function show(string $id)
    {
        $booking = $this->bookingService->getBookingDetails($id);

        return view('admin.bookings.show', compact('booking'));
    }

    public function confirm(string $id)
    {
        try {
            $this->bookingService->confirmBooking($id);

            return redirect()->back()->with('success', 'Booking confirmed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->bookingService->cancelBooking($id, $request->reason);

            return redirect()->back()->with('success', 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reports(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $typeFilter = $request->input('type');

        $filters = [
            'start_date' => \Carbon\Carbon::parse($startDate),
            'end_date' => \Carbon\Carbon::parse($endDate),
            'type' => $typeFilter,
        ];

        // Base query
        $query = Booking::with(['trip.route', 'user'])
            ->whereHas('trip', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('departure_date', [$startDate, $endDate]);
            });

        // Apply type filter if provided
        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $bookings = $query->get();

        // Calculate statistics
        $confirmedBookings = $bookings->where('status', BookingStatusEnum::Confirmed);
        $totalBookings = $bookings->count();

        $stats = [
            'total_bookings' => $totalBookings,
            'confirmed_bookings' => $confirmedBookings->count(),
            'pending_bookings' => $bookings->where('status', BookingStatusEnum::Pending)->count(),
            'cancelled_bookings' => $bookings->where('status', BookingStatusEnum::Cancelled)->count(),
            'total_passengers' => $confirmedBookings->sum('total_passengers'),
            'total_revenue' => $confirmedBookings->sum('final_amount'),
            'avg_booking_value' => $confirmedBookings->count() > 0
                ? $confirmedBookings->sum('final_amount') / $confirmedBookings->count()
                : 0,
            'cancellation_rate' => $totalBookings > 0
                ? ($bookings->where('status', BookingStatusEnum::Cancelled)->count() / $totalBookings) * 100
                : 0,
            'by_type' => [
                'online' => $bookings->where('type', BookingTypeEnum::Online)->count(),
                'counter' => $bookings->where('type', BookingTypeEnum::Counter)->count(),
                'phone' => $bookings->where('type', BookingTypeEnum::Phone)->count(),
            ],
            'by_status' => [
                'confirmed' => $confirmedBookings->count(),
                'pending' => $bookings->where('status', BookingStatusEnum::Pending)->count(),
                'cancelled' => $bookings->where('status', BookingStatusEnum::Cancelled)->count(),
            ],
            'revenue_by_type' => [
                'online' => $bookings->where('type', BookingTypeEnum::Online)
                    ->where('status', BookingStatusEnum::Confirmed)
                    ->sum('final_amount'),
                'counter' => $bookings->where('type', BookingTypeEnum::Counter)
                    ->where('status', BookingStatusEnum::Confirmed)
                    ->sum('final_amount'),
                'phone' => $bookings->where('type', BookingTypeEnum::Phone)
                    ->where('status', BookingStatusEnum::Confirmed)
                    ->sum('final_amount'),
            ],
            'top_routes_by_bookings' => Route::with(['trips.bookings'])
                ->whereHas('trips.bookings', function ($q) use ($startDate, $endDate) {
                    $q->whereHas('trip', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('departure_date', [$startDate, $endDate]);
                    });
                })
                ->withCount(['trips as bookings_count' => function ($q) use ($startDate, $endDate) {
                    $q->whereHas('bookings', function ($query) use ($startDate, $endDate) {
                        $query->whereHas('trip', function ($q2) use ($startDate, $endDate) {
                            $q2->whereBetween('departure_date', [$startDate, $endDate]);
                        });
                    });
                }])
                ->get()
                ->map(function ($route) use ($startDate, $endDate) {
                    $routeBookings = Booking::whereHas('trip', function ($q) use ($route, $startDate, $endDate) {
                        $q->where('route_id', $route->id)
                            ->whereBetween('departure_date', [$startDate, $endDate]);
                    })->where('status', BookingStatusEnum::Confirmed)->get();

                    $route->bookings_count = $routeBookings->count();
                    $route->total_passengers = $routeBookings->sum('total_passengers');
                    $route->total_revenue = $routeBookings->sum('final_amount');

                    return $route;
                })
                ->where('bookings_count', '>', 0)
                ->sortByDesc('bookings_count')
                ->take(10),
            'top_routes_by_revenue' => Route::with(['trips.bookings'])
                ->whereHas('trips.bookings', function ($q) use ($startDate, $endDate) {
                    $q->whereHas('trip', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('departure_date', [$startDate, $endDate]);
                    });
                })
                ->get()
                ->map(function ($route) use ($startDate, $endDate) {
                    $routeBookings = Booking::whereHas('trip', function ($q) use ($route, $startDate, $endDate) {
                        $q->where('route_id', $route->id)
                            ->whereBetween('departure_date', [$startDate, $endDate]);
                    })->where('status', BookingStatusEnum::Confirmed)->get();

                    $route->bookings_count = $routeBookings->count();
                    $route->total_passengers = $routeBookings->sum('total_passengers');
                    $route->total_revenue = $routeBookings->sum('final_amount');

                    return $route;
                })
                ->where('total_revenue', '>', 0)
                ->sortByDesc('total_revenue')
                ->take(10),
            'daily_breakdown' => Booking::selectRaw('
                    DATE(trips.departure_date) as date,
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN bookings.status = ? THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN bookings.status = ? THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN bookings.status = ? THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN bookings.status = ? THEN bookings.total_passengers ELSE 0 END) as total_passengers,
                    SUM(CASE WHEN bookings.status = ? THEN bookings.final_amount ELSE 0 END) as total_revenue
                ', [
                BookingStatusEnum::Confirmed->value,
                BookingStatusEnum::Pending->value,
                BookingStatusEnum::Cancelled->value,
                BookingStatusEnum::Confirmed->value,
                BookingStatusEnum::Confirmed->value,
            ])
                ->join('trips', 'bookings.trip_id', '=', 'trips.id')
                ->whereBetween('trips.departure_date', [$startDate, $endDate])
                ->when($typeFilter, function ($q) use ($typeFilter) {
                    $q->where('bookings.type', $typeFilter);
                })
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get(),
        ];

        return view('admin.bookings.reports', compact('filters', 'stats'));
    }
}

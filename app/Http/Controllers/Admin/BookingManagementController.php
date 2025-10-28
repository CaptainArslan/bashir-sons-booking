<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatusEnum;
use App\Enums\BookingTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Route;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BookingManagementController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    public function index()
    {
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

        return view('admin.bookings.index', compact('routes', 'statuses', 'types', 'stats'));
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $bookings = Booking::query()
                ->with(['trip.route', 'trip.bus', 'user', 'fromStop.terminal', 'toStop.terminal', 'bookingSeats', 'terminal'])
                ->select('bookings.*');

            // Apply filters
            if ($request->filled('booking_number')) {
                $bookings->where('booking_number', 'like', '%'.$request->booking_number.'%');
            }

            if ($request->filled('status')) {
                $bookings->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $bookings->where('type', $request->type);
            }

            if ($request->filled('route_id')) {
                $bookings->whereHas('trip', function ($q) use ($request) {
                    $q->where('route_id', $request->route_id);
                });
            }

            if ($request->filled('date')) {
                $bookings->whereHas('trip', function ($q) use ($request) {
                    $q->whereDate('departure_date', $request->date);
                });
            }

            return DataTables::eloquent($bookings)
                ->addColumn('booking_info', function ($booking) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">'.e($booking->booking_number).'</span>
                                <small class="text-muted">'.e($booking->created_at->format('M d, Y H:i')).'</small>
                            </div>';
                })
                ->addColumn('passenger_info', function ($booking) {
                    $name = $booking->passenger_contact_name ?? $booking->user?->name ?? 'N/A';
                    $phone = $booking->passenger_contact_phone ?? 'N/A';

                    return '<div class="d-flex flex-column">
                                <span class="fw-bold">'.e($name).'</span>
                                <small class="text-muted"><i class="bx bx-phone me-1"></i>'.e($phone).'</small>
                            </div>';
                })
                ->addColumn('trip_info', function ($booking) {
                    $routeCode = $booking->trip?->route?->code ?? 'N/A';
                    $departureDate = $booking->trip?->departure_datetime?->format('M d, Y') ?? 'N/A';
                    $busName = $booking->trip?->bus?->name ?? 'Not Assigned';

                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">'.e($routeCode).'</span>
                                <small class="text-muted">'.e($departureDate).'</small>
                                <small class="badge bg-secondary mt-1">'.e($busName).'</small>
                            </div>';
                })
                ->addColumn('route_info', function ($booking) {
                    $from = $booking->fromStop?->terminal?->code ?? 'N/A';
                    $to = $booking->toStop?->terminal?->code ?? 'N/A';

                    return '<div class="d-flex align-items-center">
                                <span class="badge bg-info">'.e($from).'</span>
                                <i class="bx bx-right-arrow-alt mx-1"></i>
                                <span class="badge bg-info">'.e($to).'</span>
                            </div>';
                })
                ->addColumn('type_badge', function ($booking) {
                    $color = match ($booking->type->value) {
                        'online' => 'primary',
                        'counter' => 'success',
                        'phone' => 'warning',
                        default => 'secondary',
                    };

                    return '<span class="badge bg-'.$color.'">'.e($booking->type->label()).'</span>';
                })
                ->addColumn('seats_count', function ($booking) {
                    $count = $booking->bookingSeats->count();

                    return '<span class="badge bg-dark">'.$count.' '.($count === 1 ? 'Seat' : 'Seats').'</span>';
                })
                ->addColumn('amount_info', function ($booking) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-success">₨'.number_format($booking->final_amount, 0).'</span>
                                '.($booking->discount_amount > 0 ? '<small class="text-muted"><s>₨'.number_format($booking->total_fare, 0).'</s></small>' : '').'
                            </div>';
                })
                ->addColumn('status_badge', function ($booking) {
                    $color = match ($booking->status->value) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    };

                    return '<span class="badge bg-'.$color.'">'.e($booking->status->label()).'</span>';
                })
                ->addColumn('terminal_info', function ($booking) {
                    if ($booking->terminal) {
                        return '<span class="badge bg-info">'.e($booking->terminal->code).'</span>';
                    }

                    return '<span class="text-muted">Online</span>';
                })
                ->addColumn('actions', function ($booking) {
                    $actions = '
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <i class="bx bx-dots-horizontal-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="'.route('admin.bookings.show', $booking->id).'">
                                        <i class="bx bx-show me-2"></i>View Details
                                    </a>
                                </li>';

                    if ($booking->status === BookingStatusEnum::Pending) {
                        $actions .= '
                                <li>
                                    <form action="'.route('admin.bookings.confirm', $booking->id).'" method="POST" class="d-inline">
                                        '.csrf_field().'
                                        <button type="submit" class="dropdown-item text-success">
                                            <i class="bx bx-check me-2"></i>Confirm Booking
                                        </button>
                                    </form>
                                </li>';
                    }

                    if ($booking->canBeCancelled()) {
                        $actions .= '
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="cancelBooking('.$booking->id.')">
                                        <i class="bx bx-x me-2"></i>Cancel Booking
                                    </a>
                                </li>';
                    }

                    $actions .= '
                            </ul>
                        </div>';

                    return $actions;
                })
                ->editColumn('created_at', fn ($booking) => $booking->created_at->format('M d, Y H:i'))
                ->orderColumn('booking_number', 'booking_number $1')
                ->filterColumn('booking_number', function ($query, $keyword) {
                    $query->where('booking_number', 'like', "%{$keyword}%");
                })
                ->rawColumns(['booking_info', 'passenger_info', 'trip_info', 'route_info', 'type_badge', 'seats_count', 'amount_info', 'status_badge', 'terminal_info', 'actions'])
                ->make(true);
        }
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

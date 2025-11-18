<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatusEnum;
use App\Enums\ChannelEnum;
use App\Enums\ExpenseTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\TerminalEnum;
use App\Events\SeatConfirmed;
use App\Events\SeatLocked;
use App\Events\SeatUnlocked;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Expense;
use App\Models\Fare;
use App\Models\GeneralSetting;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Terminal;
use App\Models\Timetable;
use App\Models\TimetableStop;
use App\Models\Trip;
use App\Models\TripStop;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\TripFactoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected TripFactoryService $tripFactory,
        protected AvailabilityService $availabilityService,
    ) {}

    public function index(): View
    {
        $this->authorize('view all booking reports');

        $bookingStatuses = BookingStatusEnum::cases();
        $paymentStatuses = PaymentStatusEnum::cases();
        $channels = ChannelEnum::cases();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $terminals = Terminal::where('status', TerminalEnum::ACTIVE->value)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get employees who have created bookings (booked_by_user_id) - exclude Customer role
        $bookedByUserIds = Booking::whereNotNull('booked_by_user_id')->distinct()->pluck('booked_by_user_id');
        $employees = User::whereIn('id', $bookedByUserIds)
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Customer');
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Get all users who have bookings (user_id) - includes customers
        $customerUserIds = Booking::whereNotNull('user_id')->distinct()->pluck('user_id');
        $customers = User::whereIn('id', $customerUserIds)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('admin.bookings.index', compact(
            'bookingStatuses',
            'paymentStatuses',
            'channels',
            'terminals',
            'employees',
            'customers'
        ));
    }

    public function getData(Request $request)
    {
        $this->authorize('view reports');

        $query = Booking::query()
            ->with(['trip.route', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers', 'user', 'cancelledByUser'])
            ->where('status', BookingStatusEnum::CONFIRMED->value)
            ->latest();

        // Filter by date range with time
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $startDate = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
            $endDate = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::now();

            // Apply time filters if provided
            if ($request->filled('start_time')) {
                $startDate->setTimeFromTimeString($request->start_time);
            } else {
                $startDate->startOfDay();
            }

            if ($request->filled('end_time')) {
                $endDate->setTimeFromTimeString($request->end_time);
            } else {
                $endDate->endOfDay();
            }

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Status filter is not needed as we only show confirmed bookings
        // But allow override if explicitly requested
        if ($request->filled('status') && $request->status !== BookingStatusEnum::CONFIRMED->value) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by booking number
        if ($request->filled('booking_number')) {
            $query->where('booking_number', 'like', '%'.$request->booking_number.'%');
        }

        // Filter by from terminal
        if ($request->filled('from_terminal_id')) {
            $query->whereHas('fromStop', function ($q) use ($request) {
                $q->where('terminal_id', $request->from_terminal_id);
            });
        }

        // Filter by to terminal
        if ($request->filled('to_terminal_id')) {
            $query->whereHas('toStop', function ($q) use ($request) {
                $q->where('terminal_id', $request->to_terminal_id);
            });
        }

        // Filter by employee (booked by)
        if ($request->filled('employee_id')) {
            $query->where('booked_by_user_id', $request->employee_id);
        }

        // Filter by customer/user (booking owner)
        if ($request->filled('customer_id')) {
            $query->where('user_id', $request->customer_id);
        }

        // Filter by advance booking
        if ($request->filled('is_advance')) {
            $query->where('is_advance', $request->is_advance === '1');
        }

        return datatables()
            ->eloquent($query)
            ->addColumn('booking_number', function (Booking $booking) {
                return '<span class="badge bg-primary">#'.$booking->booking_number.'</span>';
            })
            ->addColumn('created_at', function (Booking $booking) {
                return $booking->created_at->format('d M Y, H:i');
            })
            ->addColumn('route', function (Booking $booking) {
                $from = $booking->fromStop?->terminal?->code ?? 'N/A';
                $to = $booking->toStop?->terminal?->code ?? 'N/A';

                return '<strong>'.$from.' → '.$to.'</strong>';
            })
            ->addColumn('seats', function (Booking $booking) {
                // Only show active (non-cancelled) seats
                $seatNumbers = $booking->seats->whereNull('cancelled_at')->pluck('seat_number')->join(', ');

                return '<span class="badge bg-info">'.$seatNumbers.'</span>';
            })
            ->addColumn('passengers', function (Booking $booking) {
                $passengerNames = $booking->passengers->pluck('name')->join(', ');
                if (empty($passengerNames)) {
                    return '<span class="text-muted small">No passengers</span>';
                }

                return '<div class="small">'.$passengerNames.'</div>';
            })
            ->addColumn('booking_type', function (Booking $booking) {
                $type = $booking->booking_type ?? 'regular';
                $badgeClass = $type === 'advance' ? 'bg-success' : 'bg-info';

                return '<span class="badge '.$badgeClass.'">'.ucfirst($type).'</span>';
            })
            ->addColumn('is_advance', function (Booking $booking) {
                $isAdvance = $booking->is_advance ?? false;
                $badgeClass = $isAdvance ? 'bg-success' : 'bg-secondary';
                $text = $isAdvance ? 'Yes' : 'No';

                return '<span class="badge '.$badgeClass.'">'.$text.'</span>';
            })
            ->addColumn('amount', function (Booking $booking) {
                return '<strong>PKR '.number_format($booking->final_amount, 0).'</strong>';
            })
            ->addColumn('channel', function (Booking $booking) {
                try {
                    $channel = ChannelEnum::from($booking->channel ?? '');

                    return '<span class="badge '.$channel->getBadge().'"><i class="'.$channel->getIcon().'"></i> '.$channel->getLabel().'</span>';
                } catch (\ValueError $e) {
                    return '<span class="badge bg-secondary">'.($booking->channel ?? 'N/A').'</span>';
                }
            })
            ->addColumn('employee', function (Booking $booking) {
                $employee = $booking->bookedByUser;
                if ($employee) {
                    return '<div class="text-nowrap">
                        <div class="fw-semibold small">'.$employee->name.'</div>
                        <small class="text-muted">'.$employee->email.'</small>
                    </div>';
                }

                return '<span class="text-muted small">N/A</span>';
            })
            ->addColumn('status', function (Booking $booking) {
                try {
                    $status = BookingStatusEnum::from($booking->status ?? '');

                    return '<span class="badge '.$status->getBadge().'"><i class="'.$status->getIcon().'"></i> '.$status->getLabel().'</span>';
                } catch (\ValueError $e) {
                    // Handle non-enum statuses like 'checked_in', 'boarded'
                    $badgeClass = match ($booking->status) {
                        'checked_in' => 'bg-info',
                        'boarded' => 'bg-primary',
                        default => 'bg-secondary',
                    };

                    return '<span class="badge '.$badgeClass.'">'.ucfirst($booking->status ?? 'Unknown').'</span>';
                }
            })
            ->addColumn('payment_status', function (Booking $booking) {
                try {
                    $paymentStatus = PaymentStatusEnum::from($booking->payment_status ?? '');

                    return '<span class="badge '.$paymentStatus->getBadge().'"><i class="'.$paymentStatus->getIcon().'"></i> '.$paymentStatus->getLabel().'</span>';
                } catch (\ValueError $e) {
                    // Handle non-enum statuses like 'partial'
                    if ($booking->payment_status === 'partial') {
                        return '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Partial</span>';
                    }

                    return '<span class="badge bg-secondary">'.ucfirst($booking->payment_status ?? 'Unknown').'</span>';
                }
            })
            ->addColumn('actions', function (Booking $booking) {
                $actions = '<div class="d-flex gap-1 flex-wrap justify-content-center">';

                if (auth()->user()->can('view bookings')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary" onclick="viewBookingDetails('.$booking->id.')" title="View Details">
                        <i class="bx bx-show"></i>
                    </button>';
                }

                if (auth()->user()->can('view bookings')) {
                    // Check if payment is complete before allowing print
                    $paymentReceived = $booking->payment_received_from_customer ?? 0;
                    $finalAmount = $booking->final_amount ?? 0;
                    $isPaymentComplete = $paymentReceived >= $finalAmount;
                    
                    if ($isPaymentComplete) {
                        $actions .= '<a href="'.route('admin.bookings.print', $booking->id).'" target="_blank" class="btn btn-sm btn-info" title="Print Ticket">
                            <i class="bx bx-printer"></i>
                        </a>';
                    } else {
                        $actions .= '<button type="button" class="btn btn-sm btn-info" disabled title="Payment incomplete - Cannot print ticket">
                            <i class="bx bx-printer"></i>
                        </button>';
                    }
                }

                if (auth()->user()->can('edit bookings')) {
                    $actions .= '<a href="'.route('admin.bookings.edit', $booking->id).'" class="btn btn-sm btn-warning" title="Edit Booking">
                        <i class="bx bx-edit"></i>
                    </a>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['booking_number', 'route', 'seats', 'passengers', 'amount', 'channel', 'employee', 'status', 'payment_status', 'booking_type', 'is_advance', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        return view('admin.bookings.create');
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view bookings');

        $booking->load(['trip.route', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers', 'user', 'cancelledByUser']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function printTicket(Booking $booking, ?string $type = 'customer', ?string $size = '80mm'): View
    {
        $this->authorize('view bookings');

        $booking->load([
            'trip.route',
            'trip.bus.busLayout',
            'trip.bus.busType',
            'trip.stops',
            'fromStop.terminal.city',
            'toStop.terminal.city',
            'seats',
            'passengers',
            'user',
            'bookedByUser',
            'cancelledByUser',
        ]);

        // Always use 80mm ticket size
        $size = '80mm';

        // If type is 'both', print both customer and host tickets
        if ($type === 'both') {
            return view('admin.bookings.tickets.print-both', [
                'booking' => $booking,
                'size' => '80mm',
            ]);
        }

        // Validate type
        if (! in_array($type, ['customer', 'host'])) {
            $type = 'customer';
        }

        // Get active (non-cancelled) seats
        $activeSeats = $booking->seats()->whereNull('cancelled_at')->get();
        $passengers = $booking->passengers;

        // If there are seats, generate one ticket per seat
        if ($activeSeats->count() > 0) {
            return view('admin.bookings.tickets.print-multiple', [
                'booking' => $booking,
                'ticketType' => $type,
                'seats' => $activeSeats,
                'passengers' => $passengers,
            ]);
        }

        // Fallback to single ticket if no active seats
        $template = 'admin.bookings.tickets.ticket-80mm';

        return view($template, [
            'booking' => $booking,
            'ticketType' => $type,
        ]);
    }

    public function edit(Booking $booking): View
    {
        $this->authorize('edit bookings');

        $booking->load(['trip.stops', 'seats', 'passengers', 'fromStop.terminal', 'toStop.terminal', 'cancelledByUser']);
        $bookingStatuses = BookingStatusEnum::cases();
        $paymentStatuses = PaymentStatusEnum::cases();
        $channels = ChannelEnum::cases();
        $paymentMethods = PaymentMethodEnum::options();

        return view('admin.bookings.edit', [
            'booking' => $booking,
            'bookingStatuses' => $bookingStatuses,
            'paymentStatuses' => $paymentStatuses,
            'channels' => $channels,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorize('edit bookings');

        // Check if departure time has passed - prevent status update if it has
        $departureTime = $booking->trip?->departure_datetime;
        $departurePassed = $departureTime && $departureTime->isPast();

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', array_merge(
                array_column(\App\Enums\BookingStatusEnum::cases(), 'value'),
                ['checked_in', 'boarded']
            )),
            'payment_status' => 'required|in:'.implode(',', array_merge(
                array_column(\App\Enums\PaymentStatusEnum::cases(), 'value')
            )),
            'payment_method' => 'nullable|in:'.implode(',', array_column(\App\Enums\PaymentMethodEnum::cases(), 'value')),
            'online_transaction_id' => 'nullable|string|max:100',
            'amount_received' => 'nullable|numeric|min:0',
            'reserved_until' => 'nullable|date_format:Y-m-d\TH:i',
            'notes' => 'nullable|string|max:500',
            'cancellation_reason' => 'nullable|string|max:500',
            'passengers' => 'nullable|array',
            'passengers.*.name' => 'nullable|string|max:100',
            'passengers.*.gender' => 'nullable|in:'.implode(',', \App\Enums\GenderEnum::getGenders()),
            'passengers.*.age' => 'nullable|integer|min:1|max:120',
            'passengers.*.cnic' => 'nullable|string|max:20',
            'passengers.*.phone' => 'nullable|string|max:20',
            'passengers.*.email' => 'nullable|email|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Check if booking is being cancelled (check the validated status, not the computed one)
            $isBeingCancelled = ($validated['status'] === 'cancelled' || $validated['status'] === BookingStatusEnum::CANCELLED->value);
            $wasCancelled = ($booking->status === 'cancelled' || $booking->status === BookingStatusEnum::CANCELLED->value);
            $shouldCancelSeats = $isBeingCancelled && ! $wasCancelled;

            // If departure has passed, keep the original status UNLESS it's being cancelled (allow cancellation for record-keeping)
            $statusToUpdate = ($departurePassed && ! $isBeingCancelled) ? $booking->status : $validated['status'];

            // Validate transaction ID for non-cash payments
            $paymentMethod = $validated['payment_method'] ?? $booking->payment_method ?? 'cash';
            if ($paymentMethod !== 'cash' && empty($validated['online_transaction_id'])) {
                throw new \Exception('Transaction ID is required for non-cash payments');
            }

            // Calculate return amount for cash payments
            $amountReceived = $validated['amount_received'] ?? 0;
            $returnAmount = 0;
            if ($paymentMethod === 'cash' && $amountReceived > 0) {
                $returnAmount = max(0, $amountReceived - $booking->final_amount);
            }

            // Update basic booking information
            $updateData = [
                'status' => $statusToUpdate,
                'payment_status' => $validated['payment_status'],
                'payment_method' => $paymentMethod,
                'online_transaction_id' => $validated['online_transaction_id'] ?? ($paymentMethod === 'cash' ? null : $booking->online_transaction_id),
                'reserved_until' => $validated['status'] === 'hold' ? ($validated['reserved_until'] ?? now()->addMinutes(15)) : null,
                'notes' => $validated['notes'] ?? null,
            ];

            // If booking is being cancelled, set cancellation fields
            if ($shouldCancelSeats) {
                $updateData['cancelled_at'] = now();
                $updateData['cancellation_reason'] = $validated['cancellation_reason'] ?? null;
                if (! $booking->cancelled_by_user_id) {
                    $updateData['cancelled_by_user_id'] = Auth::id();
                }
                if (! $booking->cancelled_by_type) {
                    // Determine cancelled_by_type based on user role
                    $user = Auth::user();
                    if ($user->hasRole('admin') || $user->hasRole('Admin') || $user->hasRole('super_admin')) {
                        $updateData['cancelled_by_type'] = 'admin';
                    } elseif ($user->hasRole('employee') || $user->hasRole('Employee')) {
                        $updateData['cancelled_by_type'] = 'employee';
                    } else {
                        // Default to admin if role cannot be determined
                        $updateData['cancelled_by_type'] = 'admin';
                    }
                }
            }

            // Add payment received and return amount if cash payment
            if ($paymentMethod === 'cash') {
                $updateData['payment_received_from_customer'] = $amountReceived;
                $updateData['return_after_deduction_from_customer'] = $returnAmount;
            }

            $booking->update($updateData);

            // If booking is being cancelled, cancel all active seats
            if ($shouldCancelSeats) {
                $cancellationReason = $validated['cancellation_reason'] ?? null;
                $booking->seats()
                    ->whereNull('cancelled_at')
                    ->update([
                        'cancelled_at' => now(),
                        'cancellation_reason' => $cancellationReason,
                    ]);

                // Recalculate booking totals (will be 0 since all seats are cancelled)
                $this->recalculateBookingTotals($booking);
            }

            // Update passengers if provided
            if ($validated['passengers'] ?? false) {
                $passengers = $booking->passengers()->get();

                foreach ($validated['passengers'] as $index => $passengerData) {
                    if (isset($passengers[$index])) {
                        $passengers[$index]->update([
                            'name' => $passengerData['name'] ?? $passengers[$index]->name,
                            'gender' => $passengerData['gender'] ?? $passengers[$index]->gender,
                            'age' => $passengerData['age'] ?? $passengers[$index]->age,
                            'cnic' => $passengerData['cnic'] ?? $passengers[$index]->cnic,
                            'phone' => $passengerData['phone'] ?? $passengers[$index]->phone,
                            'email' => $passengerData['email'] ?? $passengers[$index]->email,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Booking updated successfully',
                'booking' => $booking->load(['trip.route', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update booking',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete bookings');

        // TODO: Implement destroy
        return response()->json(['message' => 'Booking deleted']);
    }

    public function cancelSeat(Booking $booking, BookingSeat $seat, Request $request)
    {
        $this->authorize('edit bookings');

        // Load booking relationships for stop-wise validation
        $booking->load(['trip.stops', 'fromStop.terminal']);

        // Check if seat belongs to this booking
        if ($seat->booking_id !== $booking->id) {
            return response()->json([
                'message' => 'Seat does not belong to this booking',
            ], 400);
        }

        // Check if departure time has passed - use stop-wise departure time
        $fromTripStop = $booking->trip?->stops?->firstWhere('terminal_id', $booking->fromStop?->terminal_id);
        $departureTime = $fromTripStop?->departure_at ?? $booking->trip?->departure_datetime;
        $departurePassed = $departureTime && $departureTime->isPast();
        if ($departurePassed) {
            return response()->json([
                'message' => 'Cannot cancel seat as trip has already departed from the origin stop',
            ], 400);
        }

        // Check if seat is already cancelled
        if ($seat->cancelled_at) {
            return response()->json([
                'message' => 'Seat is already cancelled',
            ], 400);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:5|max:500',
        ], [
            'cancellation_reason.required' => 'Cancellation reason is required.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 5 characters.',
            'cancellation_reason.max' => 'Cancellation reason cannot exceed 500 characters.',
        ]);

        try {
            DB::beginTransaction();

            // Cancel the seat with cancellation reason
            $seat->update([
                'cancelled_at' => now(),
                'cancellation_reason' => trim($validated['cancellation_reason']),
            ]);

            // Recalculate booking totals from active seats only
            $this->recalculateBookingTotals($booking);

            // Refresh booking to get updated seat counts
            $booking->refresh();
            $booking->load('seats');

            // Check if all seats are now cancelled - if so, cancel the booking
            $activeSeatsCount = $booking->seats()->whereNull('cancelled_at')->count();
            $isBookingCancelled = ($booking->status === 'cancelled' || $booking->status === BookingStatusEnum::CANCELLED->value);
            $bookingCancelled = false;

            if ($activeSeatsCount === 0 && ! $isBookingCancelled) {
                // All seats are cancelled, so cancel the booking
                $cancellationReason = trim($validated['cancellation_reason']);

                // Determine cancelled_by_type based on user role
                $user = Auth::user();
                $cancelledByType = 'admin';
                if (method_exists($user, 'hasRole')) {
                    if ($user->hasRole('admin') || $user->hasRole('Admin') || $user->hasRole('super_admin')) {
                        $cancelledByType = 'admin';
                    } elseif ($user->hasRole('employee') || $user->hasRole('Employee')) {
                        $cancelledByType = 'employee';
                    }
                }

                $booking->update([
                    'status' => BookingStatusEnum::CANCELLED->value,
                    'cancelled_at' => now(),
                    'cancellation_reason' => $cancellationReason,
                    'cancelled_by_user_id' => Auth::id(),
                    'cancelled_by_type' => $cancelledByType,
                ]);

                $bookingCancelled = true;
            }

            DB::commit();

            $message = 'Seat cancelled successfully';
            if ($bookingCancelled) {
                $message .= '. All seats are cancelled, so the booking has been cancelled.';
            }

            return response()->json([
                'message' => $message,
                'booking' => $booking->fresh(['seats', 'trip.route', 'fromStop.terminal', 'toStop.terminal']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to cancel seat',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function restoreSeat(Booking $booking, BookingSeat $seat)
    {
        $this->authorize('edit bookings');

        // Load booking relationships for stop-wise validation
        $booking->load(['trip.stops', 'fromStop.terminal']);

        // Check if seat belongs to this booking
        if ($seat->booking_id !== $booking->id) {
            return response()->json([
                'message' => 'Seat does not belong to this booking',
            ], 400);
        }

        // Check if departure time has passed - use stop-wise departure time
        $fromTripStop = $booking->trip?->stops?->firstWhere('terminal_id', $booking->fromStop?->terminal_id);
        $departureTime = $fromTripStop?->departure_at ?? $booking->trip?->departure_datetime;
        $departurePassed = $departureTime && $departureTime->isPast();
        if ($departurePassed) {
            return response()->json([
                'message' => 'Cannot restore seat as trip has already departed from the origin stop',
            ], 400);
        }

        // Check if seat is not cancelled
        if (! $seat->cancelled_at) {
            return response()->json([
                'message' => 'Seat is not cancelled',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Check if booking is cancelled before restoring seat
            $isBookingCancelled = ($booking->status === 'cancelled' || $booking->status === BookingStatusEnum::CANCELLED->value);

            // Restore the seat (clear cancellation reason as well)
            $seat->update([
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);

            // Recalculate booking totals from active seats only
            $this->recalculateBookingTotals($booking);

            // Refresh booking to get updated seat counts
            $booking->refresh();
            $booking->load('seats');

            // If booking was cancelled and now has active seats, clear cancellation fields but don't auto-update status
            $requiresStatusUpdate = false;
            if ($isBookingCancelled) {
                $activeSeatsCount = $booking->seats()->whereNull('cancelled_at')->count();

                if ($activeSeatsCount > 0) {
                    // Clear cancellation fields but don't auto-update status - let user decide
                    $booking->update([
                        'cancelled_at' => null,
                        'cancellation_reason' => null,
                        'cancelled_by_user_id' => null,
                        'cancelled_by_type' => null,
                    ]);

                    // Keep booking status as cancelled - user needs to update it manually
                    // Also need to update payment status and payment method
                    $requiresStatusUpdate = true;
                }
            }

            DB::commit();

            $message = 'Seat restored successfully';
            if ($requiresStatusUpdate) {
                $message .= '. Please update the booking status, payment status, and payment method.';
            }

            return response()->json([
                'message' => $message,
                'requires_status_update' => $requiresStatusUpdate,
                'booking' => $booking->fresh(['seats', 'trip.route', 'fromStop.terminal', 'toStop.terminal']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to restore seat',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Recalculate booking totals from active (non-cancelled) seats
     */
    private function recalculateBookingTotals(Booking $booking): void
    {
        // Get all active (non-cancelled) seats
        $activeSeats = $booking->seats()->whereNull('cancelled_at')->get();

        // Calculate totals from active seats
        $totalFare = $activeSeats->sum('fare');
        $taxAmount = $activeSeats->sum('tax_amount');
        $finalAmount = $totalFare + $taxAmount;

        // Update booking totals
        // Note: We preserve discount_amount as it was set during booking creation
        $booking->update([
            'total_fare' => $totalFare,
            'tax_amount' => $taxAmount,
            'final_amount' => $finalAmount,
            'total_passengers' => $activeSeats->count(),
        ]);
    }

    public function getTripPassengers(int $tripId): JsonResponse
    {
        try {
            $user = Auth::user();

            // ✅ Load ALL bookings for the trip (except cancelled)
            // ✅ No terminal-based filtering — all passengers will be visible
            $bookings = Booking::query()
                ->where('trip_id', $tripId)
                ->where('status', '!=', 'cancelled')
                ->with([
                    'passengers' => fn ($q) => $q->orderBy('id'),
                    'seats' => fn ($q) => $q->whereNull('cancelled_at')->orderBy('seat_number'),
                    'fromStop.terminal',
                    'toStop.terminal',
                ])
                ->get();

            Log::info('getTripPassengers loaded bookings', [
                'trip_id' => $tripId,
                'user_id' => $user->id,
                'bookings_count' => $bookings->count(),
            ]);

            $passengers = [];

            foreach ($bookings as $booking) {

                // ✅ Even if passengers[] is empty, we do NOT skip the booking
                foreach ($booking->passengers as $passenger) {

                    $seatNumbers = $booking->seats
                        ->pluck('seat_number')
                        ->sort()
                        ->values()
                        ->toArray();

                    $passengers[] = [
                        'id' => $passenger->id,
                        'booking_id' => $booking->id,
                        'name' => $passenger->name ?? 'N/A',
                        'gender' => $passenger->gender?->value ?? $passenger->gender,
                        'age' => $passenger->age,
                        'cnic' => $passenger->cnic,
                        'phone' => $passenger->phone,
                        'email' => $passenger->email,
                        'seat_numbers' => $seatNumbers,
                        'seats_display' => implode(', ', $seatNumbers),

                        'from_stop' => $booking->fromStop?->terminal?->name ?? 'N/A',
                        'from_code' => $booking->fromStop?->terminal?->code ?? 'N/A',

                        'to_stop' => $booking->toStop?->terminal?->name ?? 'N/A',
                        'to_code' => $booking->toStop?->terminal?->code ?? 'N/A',

                        'status' => $booking->status,
                        'payment_status' => $booking->payment_status,
                        'payment_method' => $booking->payment_method,
                        'booking_number' => $booking->booking_number,
                        'channel' => $booking->channel,
                    ];
                }
            }

            Log::info('getTripPassengers returning passengers', [
                'trip_id' => $tripId,
                'passenger_count' => count($passengers),
            ]);

            return response()->json($passengers);
        } catch (\Exception $e) {
            Log::error('getTripPassengers exception', [
                'trip_id' => $tripId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getBookingDetailsForConsole(int $bookingId): JsonResponse
    {
        try {
            $booking = Booking::query()
                ->where('id', $bookingId)
                ->with(['passengers', 'seats', 'fromStop.terminal', 'toStop.terminal', 'user'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'channel' => $booking->channel,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->payment_status,
                    'total_fare' => $booking->total_fare,
                    'discount_amount' => $booking->discount_amount,
                    'tax_amount' => $booking->tax_amount,
                    'final_amount' => $booking->final_amount,
                    'notes' => $booking->notes,
                    'transaction_id' => $booking->online_transaction_id,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                    'from_stop' => $booking->fromStop?->terminal?->name,
                    'to_stop' => $booking->toStop?->terminal?->name,
                    'passengers' => $booking->passengers->map(fn ($p) => [
                        'name' => $p->name,
                        'age' => $p->age,
                        'gender' => $p->gender,
                        'cnic' => $p->cnic,
                        'phone' => $p->phone,
                        'email' => $p->email,
                        'seat_number' => $booking->seats->where('id', '!=', null)->first()?->seat_number,
                    ])->toArray(),
                    'seats' => $booking->seats->map(fn ($s) => [
                        'seat_number' => $s->seat_number,
                        'gender' => $s->gender,
                        'fare' => $s->fare,
                        'tax_amount' => $s->tax_amount,
                        'final_amount' => $s->final_amount,
                    ])->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function getTerminals(Request $request): JsonResponse
    {
        $user = Auth::user();
        $terminals = Terminal::query()
            ->where('status', 'active')
            ->when($user->terminal_id, function ($query) use ($user) {
                $query->where('id', $user->terminal_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'city_id', 'code']);

        return response()->json(['terminals' => $terminals]);
    }

    public function getRoutes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:terminals,id',
        ]);

        $routes = Route::query()
            ->whereHas('routeStops', fn ($q) => $q->where('terminal_id', $validated['terminal_id']))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'direction', 'base_currency']);

        return response()->json(['routes' => $routes]);
    }

    public function getStops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
        ]);

        $stops = RouteStop::query()
            ->where('route_id', $validated['route_id'])
            ->with('terminal:id,name,code')
            ->orderBy('sequence')
            ->get(['id', 'terminal_id', 'sequence', 'route_id']);

        return response()->json(['stops' => $stops]);
    }

    // public function getRouteStops(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'from_terminal_id' => 'required|exists:terminals,id',
    //     ]);

    //     try {
    //         // Find all routes that have this terminal, then get forward stops
    //         $routes = Route::query()
    //             ->whereHas('routeStops', fn($q) => $q->where('terminal_id', $validated['from_terminal_id']))
    //             ->where('status', 'active')
    //             ->get();

    //         $routeStops = [];
    //         foreach ($routes as $route) {
    //             // Get all stops for this route
    //             $stops = RouteStop::where('route_id', $route->id)
    //                 ->with('terminal:id,name,code')
    //                 ->orderBy('sequence')
    //                 ->get();

    //             // Find the from terminal sequence
    //             $fromTerminalSequence = $stops->firstWhere('terminal_id', $validated['from_terminal_id'])?->sequence;

    //             if ($fromTerminalSequence === null) {
    //                 continue;
    //             }

    //             // Get only forward stops (sequence > from_terminal sequence)
    //             foreach ($stops as $stop) {
    //                 if ($stop->sequence > $fromTerminalSequence) {
    //                     $routeStops[] = $stop;
    //                 }
    //             }
    //         }

    //         return response()->json(['route_stops' => $routeStops]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }
    public function getRouteStops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
        ]);

        try {
            $fromTerminalId = $validated['from_terminal_id'];
            $user = Auth::user();

            // ✅ If user is restricted to routes
            if ($user->routes()->exists()) {
                $routes = $user->routes()->where('status', 'active')->get();
            } else {
                // ✅ Otherwise: get routes that include this terminal
                $routes = Route::whereHas('routeStops', function ($q) use ($fromTerminalId) {
                    $q->where('terminal_id', $fromTerminalId);
                })
                    ->where('status', 'active')
                    ->get();
            }

            $terminals = collect();

            foreach ($routes as $route) {

                $stops = RouteStop::where('route_id', $route->id)
                    ->with('terminal:id,name,code')
                    ->orderBy('sequence')
                    ->get();

                // ✅ Get the origin stop (starting point)
                $origin = $stops->firstWhere('terminal_id', $fromTerminalId);

                if (! $origin) {
                    continue;
                }

                // ✅ Only include stops AFTER the origin
                $filtered = $stops->filter(function ($stop) use ($origin) {
                    return $stop->sequence > $origin->sequence;
                });

                // ✅ Push only TERMINAL INFO (not routeStop IDs)
                foreach ($filtered as $stop) {
                    $terminals->push([
                        'terminal_id' => $stop->terminal_id,
                        'name' => $stop->terminal->name,
                        'code' => $stop->terminal->code,
                        'sequence' => $stop->sequence,
                        'route_id' => $route->id,
                    ]);
                }
            }

            // ✅ DISTINCT BY terminal_id
            $unique = $terminals->unique('terminal_id')->values();

            return response()->json(['route_stops' => $unique]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getFare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id|different:from_terminal_id',
        ], [
            'from_terminal_id.required' => 'From terminal is required',
            'from_terminal_id.exists' => 'From terminal is invalid',
            'to_terminal_id.required' => 'To terminal is required',
            'to_terminal_id.exists' => 'To terminal is invalid',
            'to_terminal_id.different' => 'To terminal must be different from from terminal',
        ]);

        try {
            if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
                throw new \Exception('From and To terminals must be different');
            }

            $fare = Fare::active()
                ->where('from_terminal_id', $validated['from_terminal_id'])
                ->where('to_terminal_id', $validated['to_terminal_id'])
                ->first();

            if (! $fare) {
                throw new \Exception('No fare found for this route segment');
            }

            return response()->json([
                'success' => true,
                'fare' => [
                    'id' => $fare->id,
                    'base_fare' => (float) $fare->base_fare,
                    'final_fare' => (float) $fare->final_fare,
                    'discount_type' => $fare->discount_type?->value,
                    'discount_value' => (float) $fare->discount_value,
                    'discount_amount' => $fare->getDiscountAmount(),
                    'currency' => $fare->currency,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    // public function getDepartureTimes(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'from_terminal_id' => 'required|exists:terminals,id',
    //         'to_terminal_id' => 'required|exists:terminals,id',
    //         'date' => 'required|date_format:Y-m-d|after_or_equal:today',
    //     ]);

    //     try {
    //         // Validate terminals are different
    //         if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
    //             throw new \Exception('From and To terminals must be different');
    //         }

    //         // Get timetable stops for the FROM terminal only on the given date
    //         $timetableStops = [];

    //         $timetableStopsQuery = TimetableStop::where('terminal_id', $validated['from_terminal_id'])
    //             ->where('is_active', true)
    //             ->with('timetable.route')
    //             ->get();

    //         foreach ($timetableStopsQuery as $ts) {
    //             // Verify timetable and route exist
    //             if (! $ts->timetable || ! $ts->timetable->route) {
    //                 continue;
    //             }

    //             // Check if route has the to_terminal in forward sequence
    //             $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
    //                 ->orderBy('sequence')
    //                 ->get();

    //             $fromStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
    //             $toStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

    //             // Skip if destination is not in forward sequence or doesn't exist
    //             if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
    //                 continue;
    //             }

    //             // Combine date with departure_time to create full datetime
    //             if ($ts->departure_time) {
    //                 // Only include times that are in future
    //                 if (strtotime($ts->departure_time) >= time()) {
    //                     $timetableStops[] = [
    //                         'id' => $ts->id,
    //                         'departure_at' => $ts->departure_time,
    //                         'arrival_at' => $ts->arrival_time,
    //                         'terminal_id' => $ts->terminal_id,
    //                         'timetable_id' => $ts->timetable_id,
    //                         'route_id' => $ts->timetable->route->id,
    //                         'route_name' => $ts->timetable->route->name,
    //                     ];
    //                 }
    //             }
    //         }

    //         // Remove duplicates and sort by departure time
    //         $timetableStops = collect($timetableStops)
    //             ->unique('departure_at')
    //             ->sortBy('departure_at')
    //             ->values();

    //         return response()->json(['timetable_stops' => $timetableStops]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }

    public function getDepartureTimes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        try {
            if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
                throw new \Exception('From and To terminals must be different');
            }

            $selectedDate = $validated['date'];
            $now = now(); // current datetime

            $timetableStops = [];

            $timetableStopsQuery = TimetableStop::where('terminal_id', $validated['from_terminal_id'])
                ->where('is_active', true)
                ->with('timetable.route')
                ->get();

            foreach ($timetableStopsQuery as $ts) {

                if (! $ts->timetable || ! $ts->timetable->route) {
                    continue;
                }

                $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
                    ->orderBy('sequence')
                    ->get();

                $fromStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
                $toStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

                if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
                    continue;
                }

                if ($ts->departure_time) {

                    // ✅ Combine selected date WITH departure time
                    $fullDeparture = Carbon::parse(
                        $selectedDate.' '.$ts->departure_time
                    );

                    // ✅ Only allow future trips
                    if ($fullDeparture->greaterThanOrEqualTo($now)) {
                        $timetableStops[] = [
                            'id' => $ts->id,
                            'departure_at' => $ts->departure_time,
                            'arrival_at' => $ts->arrival_time,
                            'terminal_id' => $ts->terminal_id,
                            'timetable_id' => $ts->timetable_id,
                            'route_id' => $ts->timetable->route->id,
                            'route_name' => $ts->timetable->route->name,
                            'full_departure' => $fullDeparture->toDateTimeString(),
                        ];
                    }
                }
            }

            $timetableStops = collect($timetableStops)
                ->sortBy('full_departure')
                ->values();

            return response()->json(['timetable_stops' => $timetableStops]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function loadTripUpdated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'timetable_id' => 'required|exists:timetables,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        try {
            // Get the timetable stop
            $timetable = Timetable::findOrFail($validated['timetable_id']);
            $route = $timetable->route;

            if (! $route) {
                throw new \Exception('Route not found for selected timetable');
            }

            // Check for existing trip - load stops separately
            $trip = Trip::where('timetable_id', $timetable->id)
                ->whereDate('departure_date', $validated['date'])
                ->first();

            // Create trip if not exists
            if (! $trip) {
                $trip = $this->tripFactory->createFromTimetable($timetable->id, $validated['date']);
            }

            // Load the stops relationship
            $trip->load('stops');

            // Get route stops for the segment
            $routeStops = RouteStop::where('route_id', $route->id)
                ->with('terminal:id,name,code')
                ->orderBy('sequence')
                ->get();

            $fromRouteStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
            $toRouteStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

            if (! $fromRouteStop || ! $toRouteStop || $fromRouteStop->sequence >= $toRouteStop->sequence) {
                throw new \Exception('Invalid segment selection');
            }

            // Map to trip stops
            $tripFromStop = $trip->stops->firstWhere('terminal_id', $validated['from_terminal_id']);
            $tripToStop = $trip->stops->firstWhere('terminal_id', $validated['to_terminal_id']);

            if (! $tripFromStop || ! $tripToStop) {
                throw new \Exception('Selected terminals not found in trip');
            }

            // Load terminal information for trip stops
            $tripFromStop->load('terminal:id,name,code');
            $tripToStop->load('terminal:id,name,code');

            // Get seat map
            $seatCount = $this->availabilityService->seatCount($trip);
            $availableSeats = $this->availabilityService->availableSeats(
                $trip->id,
                $tripFromStop->id,
                $tripToStop->id
            );

            $seatMap = $this->buildSeatMap($trip, $tripFromStop, $tripToStop, $seatCount, $availableSeats);

            return response()->json([
                'trip' => $trip->load('bus.busLayout'),
                'route' => [
                    'id' => $route->id ?? null,
                    'name' => $route->name ?? null,
                    'code' => $route->code ?? null,
                ],
                'route_stops' => $routeStops->map(function ($routeStop) {
                    return [
                        'id' => $routeStop->id,
                        'terminal_id' => $routeStop->terminal_id,
                        'terminal_name' => $routeStop->terminal->name ?? null,
                        'terminal_code' => $routeStop->terminal->code ?? null,
                        'sequence' => $routeStop->sequence,
                    ];
                })->values(),
                'from_stop' => [
                    'trip_stop_id' => $tripFromStop->id ?? null,
                    'route_stop_id' => $fromRouteStop->id ?? null,
                    'terminal_id' => $tripFromStop->terminal_id ?? null,
                    'terminal_name' => $tripFromStop->terminal->name ?? null,
                    'terminal_code' => $tripFromStop->terminal->code ?? null,
                    'departure_at' => $tripFromStop->departure_at?->format('Y-m-d H:i:s') ?? null,
                    'sequence' => $tripFromStop->sequence ?? null,
                ],
                'to_stop' => [
                    'trip_stop_id' => $tripToStop->id ?? null,
                    'route_stop_id' => $toRouteStop->id ?? null,
                    'terminal_id' => $tripToStop->terminal_id ?? null,
                    'terminal_name' => $tripToStop->terminal->name ?? null,
                    'terminal_code' => $tripToStop->terminal->code ?? null,
                    'arrival_at' => $tripToStop->arrival_at?->format('Y-m-d H:i:s') ?? null,
                    'sequence' => $tripToStop->sequence ?? null,
                ],
                'seat_map' => $seatMap,
                'available_count' => count($availableSeats),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 400);
        }
    }

    public function lockSeats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer|min:1|max:44',
            'from_stop_id' => 'required|exists:trip_stops,id',
            'to_stop_id' => 'required|exists:trip_stops,id',
        ]);

        try {
            // Wrap in transaction so lockForUpdate() works correctly
            // Row-level locks only work within database transactions
            return DB::transaction(function () use ($validated) {
                // Lock the row for the specified trip to prevent race conditions when booking/locking seats
                $trip = Trip::lockForUpdate()->findOrFail($validated['trip_id']);
                $availableSeats = $this->availabilityService->availableSeats(
                    $trip->id,
                    $validated['from_stop_id'],
                    $validated['to_stop_id']
                );

                // Check all seats are available
                $availableSet = array_flip($availableSeats);
                foreach ($validated['seat_numbers'] as $seat) {
                    if (! isset($availableSet[$seat])) {
                        throw ValidationException::withMessages([
                            'seats' => "Seat {$seat} is not available.",
                        ]);
                    }
                }

                // Broadcast seat locked event
                SeatLocked::dispatch(
                    $trip->id,
                    $validated['seat_numbers'],
                    Auth::user()
                );

                return response()->json([
                    'message' => 'Seats locked successfully',
                    'locked_seats' => $validated['seat_numbers'],
                    'trip_id' => $trip->id,
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function unlockSeats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer',
        ]);

        SeatUnlocked::dispatch(
            $validated['trip_id'],
            $validated['seat_numbers'],
            Auth::user()
        );

        return response()->json([
            'message' => 'Seats unlocked successfully',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create bookings');

        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'terminal_id' => 'nullable|exists:terminals,id', // Terminal where booking was made
            'seat_numbers' => 'required|array|min:1',
            'seat_numbers.*' => 'integer',
            'seats_data' => 'nullable|json', // Optional: seats with gender information
            'passengers' => 'required|json',
            'channel' => 'required|in:'.implode(',', array_column(\App\Enums\ChannelEnum::cases(), 'value')),
            'payment_method' => 'nullable|in:'.implode(',', array_column(\App\Enums\PaymentMethodEnum::cases(), 'value')),
            'transaction_id' => 'nullable|string|max:100',
            'amount_received' => 'nullable|numeric|min:0',
            'fare_per_seat' => 'required|numeric|min:0',
            'total_fare' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Validate transaction ID for non-cash counter payments
            if ($validated['channel'] === 'counter' && $validated['payment_method'] !== 'cash' && empty($validated['transaction_id'])) {
                throw new \Exception('Transaction ID is required for non-cash payments');
            }

            // Parse passengers JSON
            $passengers = json_decode($validated['passengers'], true);
            if (! is_array($passengers) || count($passengers) === 0) {
                throw new \Exception('Invalid passengers data');
            }

            // Parse seats data JSON (optional - contains seat_number and gender for each seat)
            $seatsData = [];
            if (! empty($validated['seats_data'])) {
                $seatsData = json_decode($validated['seats_data'], true);
                if (! is_array($seatsData)) {
                    $seatsData = [];
                }
            }

            // Get trip stops
            $trip = Trip::with('route')->findOrFail($validated['trip_id']);
            $fromTerminalId = $validated['from_terminal_id'];
            $toTerminalId = $validated['to_terminal_id'];

            $tripFromStop = $trip->stops()->where('terminal_id', $fromTerminalId)->firstOrFail();
            $tripToStop = $trip->stops()->where('terminal_id', $toTerminalId)->firstOrFail();

            if ($tripFromStop->sequence >= $tripToStop->sequence) {
                throw new \Exception('Invalid segment selection');
            }

            // Find RouteStop IDs based on route_id, terminal_id, and sequence
            $fromRouteStop = RouteStop::where('route_id', $trip->route_id)
                ->where('terminal_id', $fromTerminalId)
                ->where('sequence', $tripFromStop->sequence)
                ->firstOrFail();

            $toRouteStop = RouteStop::where('route_id', $trip->route_id)
                ->where('terminal_id', $toTerminalId)
                ->where('sequence', $tripToStop->sequence)
                ->firstOrFail();

            // Determine terminal_id: use provided terminal_id or fallback to user's terminal_id or from_terminal_id
            $terminalId = $validated['terminal_id'] ?? Auth::user()->terminal_id ?? $validated['from_terminal_id'];

            $data = [
                'trip_id' => $validated['trip_id'],
                'from_stop_id' => $fromRouteStop->id, // RouteStop ID, not TripStop ID
                'to_stop_id' => $toRouteStop->id, // RouteStop ID, not TripStop ID
                'from_trip_stop_id' => $tripFromStop->id, // For BookingService to use with AvailabilityService
                'to_trip_stop_id' => $tripToStop->id, // For BookingService to use with AvailabilityService
                'terminal_id' => $terminalId, // Terminal where booking was made
                'seat_numbers' => $validated['seat_numbers'],
                'seats_data' => $seatsData, // Seats with gender information
                'passengers' => $passengers, // Passenger information (without seat_number)
                'channel' => $validated['channel'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'online_transaction_id' => $validated['transaction_id'] ?? null,
                'total_fare' => $validated['total_fare'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'final_amount' => $validated['final_amount'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::user()->id,
            ];

            // Handle payment for counter bookings
            if ($validated['channel'] === 'counter') {
                $amountReceived = $validated['amount_received'] ?? 0;
                $returnAmount = max(0, $amountReceived - $validated['final_amount']);

                $data['payment_received_from_customer'] = $amountReceived;
                $data['return_after_deduction_from_customer'] = $returnAmount;
            } else {
                // For phone and online bookings, set default payment values
                $data['payment_received_from_customer'] = 0;
                $data['return_after_deduction_from_customer'] = 0;
            }

            // Create booking
            $booking = $this->bookingService->create($data, Auth::user());

            // Broadcast seat confirmed event
            foreach ($validated['seat_numbers'] as $seat) {
                SeatConfirmed::dispatch($validated['trip_id'], [$seat], Auth::user());
            }

            return response()->json([
                'booking' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'total_fare' => $booking->total_fare,
                    'discount_amount' => $booking->discount_amount,
                    'tax_amount' => $booking->tax_amount,
                    'final_amount' => $booking->final_amount,
                    'payment_method' => $booking->payment_method,
                    'transaction_id' => $booking->online_transaction_id,
                    'seats' => $booking->seats->pluck('seat_number')->toArray(),
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    private function buildSeatMap(Trip $trip, TripStop $fromStop, TripStop $toStop, int $total, array $available): array
    {
        $seatMap = [];
        $bookedSeats = $this->getBookedSeats($trip, $fromStop, $toStop);

        for ($i = 1; $i <= $total; $i++) {
            $seatMap[$i] = [
                'number' => $i,
                'status' => 'available', // available, booked, held
            ];

            if (isset($bookedSeats[$i])) {
                $seatMap[$i]['status'] = $bookedSeats[$i]['status'];
                $seatMap[$i]['booking_id'] = $bookedSeats[$i]['booking_id'];
                $seatMap[$i]['gender'] = $bookedSeats[$i]['gender'];
            } elseif (! in_array($i, $available)) {
                $seatMap[$i]['status'] = 'held';
            }
        }

        return $seatMap;
    }

    private function getBookedSeats(Trip $trip, TripStop $fromStop, TripStop $toStop): array
    {
        // Load bookings with RouteStop relationships
        $bookings = Booking::with(['seats', 'passengers', 'fromStop:id,sequence', 'toStop:id,sequence'])
            ->where('trip_id', $trip->id)
            ->whereIn('status', array_merge(
                array_column(\App\Enums\BookingStatusEnum::cases(), 'value'),
                ['checked_in', 'boarded']
            ))
            ->get();

        $bookedSeats = [];

        // Get query segment sequences (TripStop sequences)
        $queryFrom = $fromStop->sequence ?? null;
        $queryTo = $toStop->sequence ?? null;

        if ($queryFrom === null || $queryTo === null) {
            return $bookedSeats;
        }

        foreach ($bookings as $booking) {
            // Skip if RouteStop relationships are missing
            if (! $booking->fromStop || ! $booking->toStop) {
                continue;
            }

            // Check if segment overlaps - use RouteStop sequences
            $bookingFrom = $booking->fromStop->sequence ?? null;
            $bookingTo = $booking->toStop->sequence ?? null;

            // Skip if sequences are missing
            if ($bookingFrom === null || $bookingTo === null) {
                continue;
            }

            // Check for overlap: bookingFrom < queryTo AND queryFrom < bookingTo
            if ($bookingFrom < $queryTo && $queryFrom < $bookingTo) {
                // Only process active (non-cancelled) seats
                foreach ($booking->seats->whereNull('cancelled_at') as $seat) {
                    // Get gender from seat first (primary source), fallback to passenger if seat doesn't have it
                    $gender = null;

                    // Check if seat has gender (it's stored as enum)
                    if ($seat->gender) {
                        // Gender is stored as enum, get its string value
                        if ($seat->gender instanceof \App\Enums\GenderEnum) {
                            $gender = $seat->gender->value;
                        } elseif (is_string($seat->gender)) {
                            $gender = $seat->gender;
                        }
                    }

                    // Fallback to first passenger's gender if seat doesn't have gender
                    if (! $gender && $booking->passengers->isNotEmpty()) {
                        $passenger = $booking->passengers->first();
                        if ($passenger && $passenger->gender) {
                            if ($passenger->gender instanceof \App\Enums\GenderEnum) {
                                $gender = $passenger->gender->value;
                            } elseif (is_string($passenger->gender)) {
                                $gender = $passenger->gender;
                            }
                        }
                    }

                    $bookedSeats[$seat->seat_number] = [
                        'status' => $booking->status === 'hold' ? 'held' : 'booked',
                        'booking_id' => $booking->id,
                        'gender' => $gender, // This will be 'male', 'female', or null
                    ];
                }
            }
        }

        return $bookedSeats;
    }

    public function listBuses(): JsonResponse
    {
        try {
            $buses = \App\Models\Bus::where('status', 'active')
                ->select('id', 'name', 'registration_number', 'model', 'color')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'buses' => $buses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function getExpenseTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'expense_types' => ExpenseTypeEnum::options(),
        ]);
    }

    public function assignBusDriver(Request $request, $tripId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bus_id' => 'required|exists:buses,id',
                'driver_name' => 'required|string|max:255',
                'driver_phone' => 'required|string|max:20',
                'driver_cnic' => 'required|string|max:50',
                'driver_license' => 'required|string|max:100',
                'driver_address' => 'nullable|string|max:500',
                'expenses' => 'nullable|array',
                'expenses.*.expense_type' => 'required|in:'.implode(',', array_column(ExpenseTypeEnum::cases(), 'value')),
                'expenses.*.amount' => 'required|numeric|min:0',
                'expenses.*.from_terminal_id' => 'nullable|exists:terminals,id',
                'expenses.*.to_terminal_id' => 'nullable|exists:terminals,id',
                'expenses.*.description' => 'nullable|string|max:500',
                'expenses.*.expense_date' => 'nullable|date',
            ]);

            $trip = Trip::findOrFail($tripId);

            DB::transaction(function () use ($trip, $validated) {
                // Update trip with bus and driver information
                $trip->update([
                    'bus_id' => $validated['bus_id'],
                    'driver_name' => $validated['driver_name'],
                    'driver_phone' => $validated['driver_phone'],
                    'driver_cnic' => $validated['driver_cnic'],
                    'driver_license' => $validated['driver_license'],
                    'driver_address' => $validated['driver_address'] ?? null,
                ]);

                // Create expenses if provided
                if (! empty($validated['expenses'])) {
                    foreach ($validated['expenses'] as $expenseData) {
                        Expense::create([
                            'trip_id' => $trip->id,
                            'user_id' => Auth::id(),
                            'expense_type' => $expenseData['expense_type'],
                            'amount' => $expenseData['amount'],
                            'from_terminal_id' => $expenseData['from_terminal_id'] ?? null,
                            'to_terminal_id' => $expenseData['to_terminal_id'] ?? null,
                            'description' => $expenseData['description'] ?? null,
                            'expense_date' => $expenseData['expense_date'] ?? $trip->departure_date,
                        ]);
                    }
                }
            });

            // Load relationships
            $trip->load(['bus.busLayout', 'expenses.fromTerminal', 'expenses.toTerminal', 'stops']);

            // If trip is loaded in console, regenerate seat map with new bus seat count
            $seatMap = null;
            $seatCount = null;
            if ($trip->bus) {
                $seatCount = $this->availabilityService->seatCount($trip);

                // Get current from/to stops from request if available (for console context)
                $fromTerminalId = $request->input('from_terminal_id');
                $toTerminalId = $request->input('to_terminal_id');

                if ($fromTerminalId && $toTerminalId && $trip->stops->isNotEmpty()) {
                    $tripFromStop = $trip->stops->firstWhere('terminal_id', $fromTerminalId);
                    $tripToStop = $trip->stops->firstWhere('terminal_id', $toTerminalId);

                    if ($tripFromStop && $tripToStop) {
                        $availableSeats = $this->availabilityService->availableSeats(
                            $trip->id,
                            $tripFromStop->id,
                            $tripToStop->id
                        );
                        $seatMap = $this->buildSeatMap($trip, $tripFromStop, $tripToStop, $seatCount, $availableSeats);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bus, driver, and expenses assigned successfully!',
                'trip' => $trip,
                'seat_map' => $seatMap,
                'seat_count' => $seatCount,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function addTripExpenses(Request $request, $tripId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'expenses' => 'required|array|min:1',
                'expenses.*.expense_type' => 'required|in:'.implode(',', array_column(ExpenseTypeEnum::cases(), 'value')),
                'expenses.*.amount' => 'required|numeric|min:0',
                'expenses.*.from_terminal_id' => 'nullable|exists:terminals,id',
                'expenses.*.to_terminal_id' => 'nullable|exists:terminals,id',
                'expenses.*.description' => 'nullable|string|max:500',
                'expenses.*.expense_date' => 'nullable|date',
            ]);

            $trip = Trip::findOrFail($tripId);

            DB::transaction(function () use ($trip, $validated) {
                // Create expenses
                foreach ($validated['expenses'] as $expenseData) {
                    Expense::create([
                        'trip_id' => $trip->id,
                        'user_id' => Auth::id(),
                        'expense_type' => $expenseData['expense_type'],
                        'amount' => $expenseData['amount'],
                        'from_terminal_id' => $expenseData['from_terminal_id'] ?? null,
                        'to_terminal_id' => $expenseData['to_terminal_id'] ?? null,
                        'description' => $expenseData['description'] ?? null,
                        'expense_date' => $expenseData['expense_date'] ?? $trip->departure_date,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Expenses added successfully!',
                'expenses_count' => count($validated['expenses']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('view reports');

        // Build date range with time filters
        $startDate = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::now();

        $startTime = $request->filled('start_time') ? $request->start_time : null;
        $endTime = $request->filled('end_time') ? $request->end_time : null;

        if ($startTime) {
            $startDate->setTimeFromTimeString($startTime);
        } else {
            $startDate->startOfDay();
        }

        if ($endTime) {
            $endDate->setTimeFromTimeString($endTime);
        } else {
            $endDate->endOfDay();
        }

        $query = Booking::query()
            ->with(['trip.route', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers', 'user', 'bookedByUser'])
            ->where('status', BookingStatusEnum::CONFIRMED->value)
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply all filters
        // Status filter is not needed as we only show confirmed bookings
        // But allow override if explicitly requested
        if ($request->filled('status') && $request->status !== BookingStatusEnum::CONFIRMED->value) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('booking_number')) {
            $query->where('booking_number', 'like', '%'.$request->booking_number.'%');
        }

        if ($request->filled('from_terminal_id')) {
            $query->whereHas('fromStop', function ($q) use ($request) {
                $q->where('terminal_id', $request->from_terminal_id);
            });
        }

        if ($request->filled('to_terminal_id')) {
            $query->whereHas('toStop', function ($q) use ($request) {
                $q->where('terminal_id', $request->to_terminal_id);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('booked_by_user_id', $request->employee_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('user_id', $request->customer_id);
        }

        if ($request->filled('is_advance')) {
            $query->where('is_advance', $request->is_advance === '1');
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        $generalSettings = GeneralSetting::first();
        $companyName = $generalSettings?->company_name ?? 'Bashir Sons Travel';

        // Prepare filter information for display
        $filters = [
            'date_from' => $request->filled('date_from') ? $request->date_from : null,
            'date_to' => $request->filled('date_to') ? $request->date_to : null,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $request->filled('status') ? $request->status : null,
            'payment_status' => $request->filled('payment_status') ? $request->payment_status : null,
            'channel' => $request->filled('channel') ? $request->channel : null,
            'booking_number' => $request->filled('booking_number') ? $request->booking_number : null,
            'from_terminal_id' => $request->filled('from_terminal_id') ? $request->from_terminal_id : null,
            'to_terminal_id' => $request->filled('to_terminal_id') ? $request->to_terminal_id : null,
            'employee_id' => $request->filled('employee_id') ? $request->employee_id : null,
            'customer_id' => $request->filled('customer_id') ? $request->customer_id : null,
            'is_advance' => $request->filled('is_advance') ? $request->is_advance : null,
        ];

        // Get terminal and user names for display
        if ($filters['from_terminal_id']) {
            $filters['from_terminal_name'] = Terminal::find($filters['from_terminal_id'])?->name ?? 'N/A';
        }
        if ($filters['to_terminal_id']) {
            $filters['to_terminal_name'] = Terminal::find($filters['to_terminal_id'])?->name ?? 'N/A';
        }
        if ($filters['employee_id']) {
            $filters['employee_name'] = User::find($filters['employee_id'])?->name ?? 'N/A';
        }
        if ($filters['customer_id']) {
            $filters['customer_name'] = User::find($filters['customer_id'])?->name ?? 'N/A';
        }

        // Check if passenger info export is requested
        if ($request->get('type') === 'passenger_info') {
            return $this->exportPassengerInfo($bookings, $startDate, $endDate, $companyName, $filters);
        }

        return $this->exportBookings($bookings, $startDate, $endDate, $companyName, $filters);
    }

    private function exportBookings($bookings, $startDate, $endDate, $companyName, $filters = [])
    {
        $filename = 'bookings-report-'.$startDate->format('Y-m-d').'-to-'.$endDate->format('Y-m-d').'.pdf';

        $data = [
            'bookings' => $bookings,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company_name' => $companyName,
            'filters' => $filters,
            'generated_at' => Carbon::now()->format('d M Y, H:i'),
        ];

        $pdf = Pdf::loadView('admin.bookings.export', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('enable-local-file-access', true);

        return $pdf->download($filename);
    }

    private function exportPassengerInfo($bookings, $startDate, $endDate, $companyName, $filters = [])
    {
        $filename = 'passenger-info-'.$startDate->format('Y-m-d').'-to-'.$endDate->format('Y-m-d').'.pdf';

        // Extract all passenger information
        $passengerData = [];
        foreach ($bookings as $booking) {
            foreach ($booking->passengers as $passenger) {
                $passengerData[] = [
                    'booking_number' => $booking->booking_number,
                    'name' => $passenger->name,
                    'cnic' => $passenger->cnic ?? 'N/A',
                    'phone' => $passenger->phone ?? 'N/A',
                    'email' => $passenger->email ?? 'N/A',
                    'from_terminal' => $booking->fromStop?->terminal?->code ?? 'N/A',
                    'to_terminal' => $booking->toStop?->terminal?->code ?? 'N/A',
                    'booking_date' => $booking->created_at->format('Y-m-d'),
                    'amount' => $booking->final_amount,
                    'status' => $booking->status,
                    'channel' => $booking->channel,
                ];
            }
        }

        $data = [
            'passengers' => $passengerData,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company_name' => $companyName,
            'filters' => $filters,
            'generated_at' => Carbon::now()->format('d M Y, H:i'),
        ];

        $pdf = Pdf::loadView('admin.bookings.export-passenger-info', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('enable-local-file-access', true);

        return $pdf->download($filename);
    }
}

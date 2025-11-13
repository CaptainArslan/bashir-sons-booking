<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatusEnum;
use App\Enums\ChannelEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\TerminalEnum;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Terminal;
use App\Models\Trip;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class TerminalReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('view terminal reports');

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $canViewAllReports = $user->can('view all booking reports');
        $hasTerminalAssigned = (bool) $user->terminal_id;

        if ($canViewAllReports) {
            $terminals = Terminal::where('status', TerminalEnum::ACTIVE->value)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
            $canSelectTerminal = true;
        } else {
            abort_if(! $hasTerminalAssigned, 403, 'You do not have access to any terminal reports.');

            $terminals = Terminal::where('id', $user->terminal_id)
                ->where('status', TerminalEnum::ACTIVE->value)
                ->get(['id', 'name', 'code']);
            $canSelectTerminal = false;
        }

        if ($canViewAllReports) {
            $bookedByUserIds = Booking::whereNotNull('booked_by_user_id')->distinct()->pluck('booked_by_user_id');
            $userIds = Booking::whereNotNull('user_id')->distinct()->pluck('user_id');
            $allUserIds = $bookedByUserIds->merge($userIds)->unique();

            $users = User::whereIn('id', $allUserIds)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'Customer');
                })
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        } else {
            $users = User::query()
                ->where('id', $user->id)
                ->select('id', 'name', 'email')
                ->get();
        }

        $bookingStatuses = BookingStatusEnum::cases();
        $paymentStatuses = PaymentStatusEnum::cases();
        $channels = ChannelEnum::cases();
        $paymentMethods = PaymentMethodEnum::options();

        return view('admin.terminal-reports.index', [
            'terminals' => $terminals,
            'users' => $users,
            'canSelectTerminal' => $canSelectTerminal,
            'canViewAllReports' => $canViewAllReports,
            'selectedUserId' => $canViewAllReports ? null : $user->id,
            'bookingStatuses' => $bookingStatuses,
            'paymentStatuses' => $paymentStatuses,
            'channels' => $channels,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        $this->authorize('view terminal reports');

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $canViewAllReports = $user->can('view all booking reports');
        $hasTerminalAssigned = (bool) $user->terminal_id;

        $validationRules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        if ($canViewAllReports) {
            $validationRules['terminal_id'] = 'required|exists:terminals,id';
            $validationRules['user_id'] = 'nullable|exists:users,id';
        } else {
            $validationRules['terminal_id'] = 'nullable';
            $validationRules['user_id'] = 'nullable';
        }

        $validated = $request->validate($validationRules);

        if ($canViewAllReports) {
            $terminalId = $validated['terminal_id'];
            if (! $terminalId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Terminal ID is required',
                ], 400);
            }
        } else {
            abort_if(! $hasTerminalAssigned, 403, 'You do not have access to any terminal reports.');

            if ($request->filled('terminal_id') && (int) $request->input('terminal_id') !== $user->terminal_id) {
                abort(403, 'You are not allowed to access this terminal.');
            }

            if ($request->filled('user_id') && (int) $request->input('user_id') !== $user->id) {
                abort(403, 'You are not allowed to view other user reports.');
            }

            $terminalId = $user->terminal_id;
        }

        $terminal = Terminal::findOrFail($terminalId);
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // Get bookings where from_stop or to_stop is at this terminal
        $bookings = $this->getBookingsForTerminal(
            $terminalId,
            $startDate,
            $endDate,
            $canViewAllReports ? ($validated['user_id'] ?? null) : $user->id
        );

        // Get trips departing from or arriving at this terminal
        $trips = $this->getTripsForTerminal($terminalId, $startDate, $endDate);

        // Get expenses for trips from/to this terminal
        $expenses = $this->getExpensesForTerminal($terminalId, $startDate, $endDate);

        // Calculate statistics
        $stats = $this->calculateStats($bookings, $expenses, $trips);

        // Get summary stats for quick display
        $summary = [
            'cash_in_hand' => $stats['cash']['cash_in_hand'],
            'total_expenses' => $stats['expenses']['total_expenses'],
            'net_balance' => $stats['cash']['net_balance'],
            'total_revenue' => $stats['revenue']['total_revenue'],
        ];

        return response()->json([
            'success' => true,
            'terminal' => [
                'id' => $terminal->id,
                'name' => $terminal->name,
                'code' => $terminal->code,
            ],
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'stats' => $stats,
            'summary' => $summary,
            'bookings' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'from_terminal' => $booking->fromStop?->terminal?->name ?? 'N/A',
                    'to_terminal' => $booking->toStop?->terminal?->name ?? 'N/A',
                    'channel' => $booking->channel,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'payment_method' => $booking->payment_method,
                    'total_fare' => (float) $booking->total_fare,
                    'discount_amount' => (float) $booking->discount_amount,
                    'tax_amount' => (float) $booking->tax_amount,
                    'final_amount' => (float) $booking->final_amount,
                    'passengers_count' => $booking->passengers->count(),
                    'seats_count' => $booking->seats->count(),
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'user' => $booking->user?->name ?? 'N/A',
                ];
            }),
            'expenses' => $expenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'expense_type' => $expense->expense_type->getLabel(),
                    'amount' => (float) $expense->amount,
                    'from_terminal' => $expense->fromTerminal?->name ?? 'N/A',
                    'to_terminal' => $expense->toTerminal?->name ?? 'N/A',
                    'description' => $expense->description,
                    'expense_date' => $expense->expense_date?->format('Y-m-d') ?? 'N/A',
                    'trip_id' => $expense->trip_id,
                    'user' => $expense->user?->name ?? 'N/A',
                    'created_at' => $expense->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'trips' => $trips->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'route' => $trip->route?->name ?? 'N/A',
                    'departure_datetime' => $trip->departure_datetime?->format('Y-m-d H:i:s') ?? 'N/A',
                    'bus' => $trip->bus?->name ?? 'N/A',
                    'driver_name' => $trip->driver_name ?? 'N/A',
                    'status' => $trip->status ?? 'N/A',
                ];
            }),
        ]);
    }

    private function getBookingsForTerminal(int $terminalId, Carbon $startDate, Carbon $endDate, ?int $userId = null)
    {
        // ✅ Only get bookings that START from this terminal (from_terminal_id)
        // This matches the passenger filtering logic - terminal staff sees bookings from their terminal
        $query = Booking::query()
            ->whereHas('fromStop', function ($query) use ($terminalId) {
                $query->where('terminal_id', $terminalId);
            })
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter by user if provided
        if ($userId) {
            $query->where('booked_by_user_id', $userId);
        }

        return $query->with([
            'fromStop.terminal',
            'toStop.terminal',
            'seats',
            'passengers',
            'user',
            'bookedByUser',
            'trip.route',
        ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function getTripsForTerminal(int $terminalId, Carbon $startDate, Carbon $endDate)
    {
        return Trip::query()
            ->whereHas('stops', function ($query) use ($terminalId) {
                $query->where('terminal_id', $terminalId);
            })
            ->whereBetween('departure_datetime', [$startDate, $endDate])
            ->with(['route', 'bus', 'stops'])
            ->orderBy('departure_datetime', 'desc')
            ->get();
    }

    private function getExpensesForTerminal(int $terminalId, Carbon $startDate, Carbon $endDate)
    {
        // ✅ Get expenses where FROM terminal matches (terminal-wise expense tracking)
        // This ensures expenses are tracked terminal-wise as requested
        return Expense::query()
            ->where('from_terminal_id', $terminalId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with(['fromTerminal', 'toTerminal', 'trip', 'user'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBookingsData(Request $request)
    {
        $this->authorize('view terminal reports');

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $canViewAllReports = $user->can('view all booking reports');
        $hasTerminalAssigned = (bool) $user->terminal_id;

        $validationRules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        if ($canViewAllReports) {
            $validationRules['terminal_id'] = 'required|exists:terminals,id';
        } else {
            $validationRules['terminal_id'] = 'nullable';
        }

        $validated = $request->validate($validationRules);

        if ($canViewAllReports) {
            $terminalId = $validated['terminal_id'];
            if (! $terminalId) {
                return response()->json(['error' => 'Terminal ID is required'], 400);
            }
        } else {
            abort_if(! $hasTerminalAssigned, 403, 'You do not have access to any terminal reports.');
            $terminalId = $user->terminal_id;
        }

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $query = Booking::query()
            ->whereHas('fromStop', function ($q) use ($terminalId) {
                $q->where('terminal_id', $terminalId);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with([
                'fromStop.terminal',
                'toStop.terminal',
                'seats',
                'passengers',
                'user',
                'bookedByUser',
                'trip.route',
            ]);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('booked_by_user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        return DataTables::of($query)
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
                $seatNumbers = $booking->seats->whereNull('cancelled_at')->pluck('seat_number')->join(', ');

                return '<span class="badge bg-info">'.$seatNumbers.'</span>';
            })
            ->addColumn('channel', function (Booking $booking) {
                try {
                    $channel = ChannelEnum::from($booking->channel ?? '');

                    return '<span class="badge '.$channel->getBadge().'"><i class="'.$channel->getIcon().'"></i> '.$channel->getLabel().'</span>';
                } catch (\ValueError $e) {
                    return '<span class="badge bg-secondary">'.($booking->channel ?? 'N/A').'</span>';
                }
            })
            ->addColumn('status', function (Booking $booking) {
                try {
                    $status = BookingStatusEnum::from($booking->status ?? '');

                    return '<span class="badge '.$status->getBadge().'"><i class="'.$status->getIcon().'"></i> '.$status->getLabel().'</span>';
                } catch (\ValueError $e) {
                    return '<span class="badge bg-secondary">'.ucfirst($booking->status ?? 'Unknown').'</span>';
                }
            })
            ->addColumn('payment_method', function (Booking $booking) {
                try {
                    $method = PaymentMethodEnum::from($booking->payment_method ?? '');

                    return '<span class="badge '.$method->getBadge().'"><i class="'.$method->getIcon().'"></i> '.$method->getLabel().'</span>';
                } catch (\ValueError $e) {
                    return '<span class="badge bg-secondary">'.ucfirst($booking->payment_method ?? 'N/A').'</span>';
                }
            })
            ->addColumn('payment_status', function (Booking $booking) {
                try {
                    $paymentStatus = PaymentStatusEnum::from($booking->payment_status ?? '');

                    return '<span class="badge '.$paymentStatus->getBadge().'"><i class="'.$paymentStatus->getIcon().'"></i> '.$paymentStatus->getLabel().'</span>';
                } catch (\ValueError $e) {
                    return '<span class="badge bg-secondary">'.ucfirst($booking->payment_status ?? 'Unknown').'</span>';
                }
            })
            ->addColumn('amount', function (Booking $booking) {
                return '<strong>PKR '.number_format($booking->final_amount, 0).'</strong>';
            })
            ->addColumn('booked_by', function (Booking $booking) {
                $employee = $booking->bookedByUser;
                if ($employee) {
                    return '<div class="text-nowrap">
                        <div class="fw-semibold small">'.$employee->name.'</div>
                        <small class="text-muted">'.$employee->email.'</small>
                    </div>';
                }

                return '<span class="text-muted small">N/A</span>';
            })
            ->rawColumns(['booking_number', 'route', 'seats', 'channel', 'status', 'payment_method', 'payment_status', 'amount', 'booked_by'])
            ->make(true);
    }

    public function export(Request $request)
    {
        $this->authorize('view terminal reports');

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $canViewAllReports = $user->can('view all booking reports');
        $hasTerminalAssigned = (bool) $user->terminal_id;

        $validationRules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        if ($canViewAllReports) {
            $validationRules['terminal_id'] = 'required|exists:terminals,id';
        }

        $validated = $request->validate($validationRules);

        if ($canViewAllReports) {
            $terminalId = $validated['terminal_id'];
        } else {
            abort_if(! $hasTerminalAssigned, 403);
            $terminalId = $user->terminal_id;
        }

        $terminal = Terminal::findOrFail($terminalId);
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $query = Booking::query()
            ->whereHas('fromStop', function ($q) use ($terminalId) {
                $q->where('terminal_id', $terminalId);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with([
                'fromStop.terminal',
                'toStop.terminal',
                'seats',
                'passengers',
                'user',
                'bookedByUser',
                'trip.route',
            ]);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('booked_by_user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        $expenses = $this->getExpensesForTerminal($terminalId, $startDate, $endDate);
        $trips = $this->getTripsForTerminal($terminalId, $startDate, $endDate);
        $stats = $this->calculateStats($bookings, $expenses, $trips);

        $format = $request->get('format', 'pdf');

        if ($format === 'excel') {
            // Excel export would go here - for now return PDF
            return $this->exportPdf($terminal, $startDate, $endDate, $bookings, $expenses, $stats);
        }

        return $this->exportPdf($terminal, $startDate, $endDate, $bookings, $expenses, $stats);
    }

    private function exportPdf($terminal, $startDate, $endDate, $bookings, $expenses, $stats)
    {
        $filename = 'terminal-report-'.$terminal->code.'-'.$startDate->format('Y-m-d').'-to-'.$endDate->format('Y-m-d').'.pdf';

        $data = [
            'terminal' => $terminal,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'bookings' => $bookings,
            'expenses' => $expenses,
            'stats' => $stats,
            'generated_at' => Carbon::now()->format('d M Y, H:i'),
        ];

        $pdf = Pdf::loadView('admin.terminal-reports.export-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('enable-local-file-access', true);

        return $pdf->download($filename);
    }

    private function calculateStats($bookings, $expenses, $trips): array
    {
        $totalBookings = $bookings->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();
        $holdBookings = $bookings->where('status', 'hold')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();

        $totalRevenue = $bookings->sum('final_amount');
        $totalFare = $bookings->sum('total_fare');
        $totalDiscount = $bookings->sum('discount_amount');
        $totalTax = $bookings->sum('tax_amount');

        // Calculate cash in hand (only cash payments that are paid and not cancelled)
        // Includes both regular bookings and advance bookings created within the date range
        // For advance bookings: if created today and paid in cash, the cash is in hand today
        // The whereBetween('created_at') filter ensures we only count bookings created in the period
        $cashInHand = $bookings
            ->where('payment_method', PaymentMethodEnum::CASH->value)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->sum('payment_received_from_customer') ?? 0;

        $totalExpenses = $expenses->sum('amount');
        $netBalance = $cashInHand - $totalExpenses;
        $totalProfit = $totalRevenue - $totalExpenses;

        $totalPassengers = $bookings->sum(function ($booking) {
            return $booking->passengers->count();
        });
        $totalSeats = $bookings->sum(function ($booking) {
            return $booking->seats->count();
        });

        $totalTrips = $trips->count();

        // Payment method breakdown
        $paymentMethods = $bookings->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'amount' => $group->sum('final_amount'),
            ];
        });

        // Channel breakdown
        $channels = $bookings->groupBy('channel')->map(function ($group) {
            return [
                'count' => $group->count(),
                'amount' => $group->sum('final_amount'),
            ];
        });

        // Expense type breakdown
        $expenseTypes = $expenses->groupBy('expense_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'amount' => $group->sum('amount'),
            ];
        });

        return [
            'bookings' => [
                'total' => $totalBookings,
                'confirmed' => $confirmedBookings,
                'hold' => $holdBookings,
                'cancelled' => $cancelledBookings,
            ],
            'revenue' => [
                'total_revenue' => (float) $totalRevenue,
                'total_fare' => (float) $totalFare,
                'total_discount' => (float) $totalDiscount,
                'total_tax' => (float) $totalTax,
            ],
            'cash' => [
                'cash_in_hand' => (float) $cashInHand,
                'net_balance' => (float) $netBalance,
            ],
            'expenses' => [
                'total_expenses' => (float) $totalExpenses,
                'by_type' => $expenseTypes,
            ],
            'profit' => [
                'total_profit' => (float) $totalProfit,
                'profit_margin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0,
            ],
            'passengers' => [
                'total_passengers' => $totalPassengers,
                'total_seats' => $totalSeats,
            ],
            'trips' => [
                'total_trips' => $totalTrips,
            ],
            'payment_methods' => $paymentMethods,
            'channels' => $channels,
        ];
    }
}

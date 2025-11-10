<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TerminalEnum;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Terminal;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('admin.terminal-reports.index', [
            'terminals' => $terminals,
            'users' => $users,
            'canSelectTerminal' => $canSelectTerminal,
            'canViewAllReports' => $canViewAllReports,
            'selectedUserId' => $canViewAllReports ? null : $user->id,
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

        $totalExpenses = $expenses->sum('amount');
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

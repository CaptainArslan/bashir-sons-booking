<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Bus;
use App\Models\Terminal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('access admin panel');

        $terminals = Terminal::where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']);
        $buses = Bus::where('status', 'active')->orderBy('name')->get(['id', 'name', 'registration_number']);
        $employees = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Employee', 'Manager']);
        })->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.reports.index', compact('terminals', 'buses', 'employees'));
    }

    public function sales(Request $request)
    {
        $this->authorize('access admin panel');

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'terminal_id' => 'nullable|exists:terminals,id',
            'bus_id' => 'nullable|exists:buses,id',
            'employee_id' => 'nullable|exists:users,id',
            'is_advance' => 'nullable|boolean',
            'payment_method' => 'nullable|string',
            'channel' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $query = Booking::with([
            'trip.bus',
            'trip.route',
            'fromStop.terminal',
            'toStop.terminal',
            'bookedByUser',
            'seats',
            'passengers',
        ])
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters
        if (! empty($validated['terminal_id'])) {
            $query->whereHas('fromStop', function ($q) use ($validated) {
                $q->where('terminal_id', $validated['terminal_id']);
            });
        }

        if (! empty($validated['bus_id'])) {
            $query->whereHas('trip', function ($q) use ($validated) {
                $q->where('bus_id', $validated['bus_id']);
            });
        }

        if (! empty($validated['employee_id'])) {
            $query->where('booked_by_user_id', $validated['employee_id']);
        }

        if (isset($validated['is_advance'])) {
            $query->where('is_advance', $validated['is_advance']);
        }

        if (! empty($validated['payment_method'])) {
            $query->where('payment_method', $validated['payment_method']);
        }

        if (! empty($validated['channel'])) {
            $query->where('channel', $validated['channel']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        // Calculate summaries
        $summaries = $this->calculateSummaries($bookings, $validated);

        return view('admin.reports.sales', [
            'bookings' => $bookings,
            'summaries' => $summaries,
            'filters' => $validated,
            'terminals' => Terminal::where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']),
            'buses' => Bus::where('status', 'active')->orderBy('name')->get(['id', 'name', 'registration_number']),
            'employees' => User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Employee', 'Manager']);
            })->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    private function calculateSummaries($bookings, $filters): array
    {
        $totalBookings = $bookings->count();
        $totalRevenue = $bookings->sum('final_amount');
        $totalFare = $bookings->sum('total_fare');
        $totalDiscount = $bookings->sum('discount_amount');
        $totalTax = $bookings->sum('tax_amount');
        $totalPassengers = $bookings->sum('total_passengers');
        $advanceBookings = $bookings->where('is_advance', true)->count();
        $regularBookings = $bookings->where('is_advance', false)->count();

        // Group by terminal
        $byTerminal = $bookings->groupBy(function ($booking) {
            return $booking->fromStop?->terminal?->name ?? 'N/A';
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('final_amount'),
                'passengers' => $group->sum('total_passengers'),
            ];
        });

        // Group by bus
        $byBus = $bookings->groupBy(function ($booking) {
            return $booking->trip?->bus?->name ?? 'N/A';
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('final_amount'),
                'passengers' => $group->sum('total_passengers'),
            ];
        });

        // Group by employee
        $byEmployee = $bookings->groupBy(function ($booking) {
            return $booking->bookedByUser?->name ?? 'N/A';
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('final_amount'),
                'passengers' => $group->sum('total_passengers'),
            ];
        });

        // Group by payment method
        $byPaymentMethod = $bookings->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('final_amount'),
            ];
        });

        // Group by channel
        $byChannel = $bookings->groupBy('channel')->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('final_amount'),
            ];
        });

        // Group by status
        $byStatus = $bookings->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('final_amount'),
            ];
        });

        return [
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'total_fare' => $totalFare,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_passengers' => $totalPassengers,
            'advance_bookings' => $advanceBookings,
            'regular_bookings' => $regularBookings,
            'by_terminal' => $byTerminal,
            'by_bus' => $byBus,
            'by_employee' => $byEmployee,
            'by_payment_method' => $byPaymentMethod,
            'by_channel' => $byChannel,
            'by_status' => $byStatus,
        ];
    }
}

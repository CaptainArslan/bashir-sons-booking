<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('employee.reports.index');
    }

    public function sales(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_advance' => 'nullable|boolean',
            'payment_method' => 'nullable|string',
            'channel' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // Employees can only see their own bookings
        $query = Booking::with([
            'trip.bus',
            'trip.route',
            'fromStop.terminal',
            'toStop.terminal',
            'seats',
            'passengers',
        ])
            ->where('booked_by_user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply additional filters
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
        $summaries = $this->calculateSummaries($bookings);

        return view('employee.reports.sales', [
            'bookings' => $bookings,
            'summaries' => $summaries,
            'filters' => $validated,
        ]);
    }

    private function calculateSummaries($bookings): array
    {
        $totalBookings = $bookings->count();
        $totalRevenue = $bookings->sum('final_amount');
        $totalFare = $bookings->sum('total_fare');
        $totalDiscount = $bookings->sum('discount_amount');
        $totalTax = $bookings->sum('tax_amount');
        $totalPassengers = $bookings->sum('total_passengers');
        $advanceBookings = $bookings->where('is_advance', true)->count();
        $regularBookings = $bookings->where('is_advance', false)->count();

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
            'by_payment_method' => $byPaymentMethod,
            'by_channel' => $byChannel,
            'by_status' => $byStatus,
        ];
    }
}

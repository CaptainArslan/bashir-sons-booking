<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\Route;
use App\Models\Trip;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseManagementController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    public function index(Request $request)
    {
        $query = Expense::with(['trip.route', 'trip.bus', 'incurredByUser']);

        // Filters
        if ($request->filled('trip_id')) {
            $query->where('trip_id', $request->trip_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('route_id')) {
            $query->whereHas('trip', function ($q) use ($request) {
                $q->where('route_id', $request->route_id);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('incurred_date', [$request->start_date, $request->end_date]);
        }

        $expenses = $query->orderBy('incurred_date', 'desc')->paginate(20);

        $routes = Route::where('status', 'active')->get();
        $types = ExpenseTypeEnum::cases();

        // Statistics
        $stats = [
            'total_expenses' => Expense::sum('amount'),
            'total_count' => Expense::count(),
            'by_type' => [],
        ];

        foreach (ExpenseTypeEnum::cases() as $type) {
            $stats['by_type'][$type->value] = Expense::where('type', $type)->sum('amount');
        }

        return view('admin.expenses.index', compact('expenses', 'routes', 'types', 'stats'));
    }

    public function create(Request $request)
    {
        $tripId = $request->input('trip_id');
        $trip = $tripId ? Trip::with('route')->findOrFail($tripId) : null;

        $trips = Trip::with('route')
            ->whereNotNull('bus_id')
            ->whereDate('departure_date', '>=', now()->subDays(7))
            ->orderBy('departure_datetime', 'desc')
            ->get();

        $types = ExpenseTypeEnum::cases();

        return view('admin.expenses.create', compact('trips', 'types', 'trip'));
    }

    public function store(CreateExpenseRequest $request)
    {
        try {
            $this->expenseService->addExpense($request->validated());

            return redirect()->route('admin.expenses.index')->with('success', 'Expense added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $expense = Expense::with('trip.route')->findOrFail($id);
        $types = ExpenseTypeEnum::cases();

        return view('admin.expenses.edit', compact('expense', 'types'));
    }

    public function update(UpdateExpenseRequest $request, string $id)
    {
        try {
            $this->expenseService->updateExpense($id, $request->validated());

            return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->expenseService->deleteExpense($id);

            return redirect()->back()->with('success', 'Expense deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tripSummary(int $tripId)
    {
        $trip = Trip::with(['route', 'bus', 'expenses'])->findOrFail($tripId);
        $summary = $this->expenseService->getTripExpensesSummary($tripId);

        return view('admin.expenses.trip-summary', compact('trip', 'summary'));
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
        $query = Expense::with(['trip.route', 'trip.bus', 'incurredByUser'])
            ->whereBetween('incurred_date', [$startDate, $endDate]);

        // Apply type filter if provided
        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $expenses = $query->get();

        // Calculate statistics
        $totalExpenses = $expenses->sum('amount');
        $expenseCount = $expenses->count();

        $stats = [
            'total_expenses' => $totalExpenses,
            'expense_count' => $expenseCount,
            'avg_expense' => $expenseCount > 0 ? $totalExpenses / $expenseCount : 0,
            'trips_with_expenses' => $expenses->pluck('trip_id')->unique()->count(),
            'highest_expense' => $expenses->max('amount') ?? 0,
            'lowest_expense' => $expenses->min('amount') ?? 0,
            'by_type' => [
                'fuel' => $expenses->where('type', ExpenseTypeEnum::Fuel)->sum('amount'),
                'toll' => $expenses->where('type', ExpenseTypeEnum::Toll)->sum('amount'),
                'driver_pay' => $expenses->where('type', ExpenseTypeEnum::DriverPay)->sum('amount'),
                'maintenance' => $expenses->where('type', ExpenseTypeEnum::Maintenance)->sum('amount'),
                'misc' => $expenses->where('type', ExpenseTypeEnum::Miscellaneous)->sum('amount'),
            ],
            'count_by_type' => [
                'fuel' => $expenses->where('type', ExpenseTypeEnum::Fuel)->count(),
                'toll' => $expenses->where('type', ExpenseTypeEnum::Toll)->count(),
                'driver_pay' => $expenses->where('type', ExpenseTypeEnum::DriverPay)->count(),
                'maintenance' => $expenses->where('type', ExpenseTypeEnum::Maintenance)->count(),
                'misc' => $expenses->where('type', ExpenseTypeEnum::Miscellaneous)->count(),
            ],
            'top_routes' => Route::with(['trips.expenses'])
                ->whereHas('trips.expenses', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('incurred_date', [$startDate, $endDate]);
                })
                ->get()
                ->map(function ($route) use ($startDate, $endDate, $typeFilter) {
                    $query = Expense::whereHas('trip', function ($q) use ($route) {
                        $q->where('route_id', $route->id);
                    })->whereBetween('incurred_date', [$startDate, $endDate]);

                    if ($typeFilter) {
                        $query->where('type', $typeFilter);
                    }

                    $routeExpenses = $query->get();

                    $route->trips_count = $route->trips()->whereHas('expenses', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('incurred_date', [$startDate, $endDate]);
                    })->count();
                    $route->expense_count = $routeExpenses->count();
                    $route->total_expense = $routeExpenses->sum('amount');

                    return $route;
                })
                ->where('total_expense', '>', 0)
                ->sortByDesc('total_expense')
                ->take(10),
            'top_buses' => \App\Models\Bus::with(['trips.expenses'])
                ->whereHas('trips.expenses', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('incurred_date', [$startDate, $endDate]);
                })
                ->get()
                ->map(function ($bus) use ($startDate, $endDate, $typeFilter) {
                    $query = Expense::whereHas('trip', function ($q) use ($bus) {
                        $q->where('bus_id', $bus->id);
                    })->whereBetween('incurred_date', [$startDate, $endDate]);

                    if ($typeFilter) {
                        $query->where('type', $typeFilter);
                    }

                    $busExpenses = $query->get();

                    $bus->trips_count = $bus->trips()->whereHas('expenses', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('incurred_date', [$startDate, $endDate]);
                    })->count();
                    $bus->expense_count = $busExpenses->count();
                    $bus->total_expense = $busExpenses->sum('amount');

                    return $bus;
                })
                ->where('total_expense', '>', 0)
                ->sortByDesc('total_expense')
                ->take(10),
            'top_users' => \App\Models\User::with(['incurredExpenses'])
                ->whereHas('incurredExpenses', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('incurred_date', [$startDate, $endDate]);
                })
                ->get()
                ->map(function ($user) use ($startDate, $endDate, $typeFilter) {
                    $query = Expense::where('incurred_by', $user->id)
                        ->whereBetween('incurred_date', [$startDate, $endDate]);

                    if ($typeFilter) {
                        $query->where('type', $typeFilter);
                    }

                    $userExpenses = $query->get();

                    $user->expense_count = $userExpenses->count();
                    $user->total_expense = $userExpenses->sum('amount');

                    return $user;
                })
                ->where('total_expense', '>', 0)
                ->sortByDesc('total_expense')
                ->take(10),
            'daily_breakdown' => Expense::selectRaw('
                    DATE(incurred_date) as date,
                    COUNT(*) as expense_count,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as fuel,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as toll,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as driver_pay,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as maintenance,
                    SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as misc,
                    SUM(amount) as total_amount
                ', [
                ExpenseTypeEnum::Fuel->value,
                ExpenseTypeEnum::Toll->value,
                ExpenseTypeEnum::DriverPay->value,
                ExpenseTypeEnum::Maintenance->value,
                ExpenseTypeEnum::Miscellaneous->value,
            ])
                ->whereBetween('incurred_date', [$startDate, $endDate])
                ->when($typeFilter, function ($q) use ($typeFilter) {
                    $q->where('type', $typeFilter);
                })
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get(),
        ];

        return view('admin.expenses.reports', compact('filters', 'stats'));
    }
}

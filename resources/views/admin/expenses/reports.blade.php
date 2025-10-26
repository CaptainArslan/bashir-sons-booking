@extends('admin.layouts.app')

@section('title', 'Expense Reports')

@section('styles')
    <style>
        .report-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
            height: 100%;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.875rem;
        }

        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .table-responsive {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
    </style>
@endsection

@section('content')
    <!-- Header -->
    <div class="report-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-2">💰 Expense Reports & Analytics</h4>
                <p class="mb-0">Comprehensive expense tracking and financial insights</p>
            </div>
            <div>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-light">
                    <i class="ti ti-arrow-left"></i> Back to Expenses
                </a>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.expenses.reports') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ request('start_date', $filters['start_date']->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control"
                        value="{{ request('end_date', $filters['end_date']->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Expense Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="fuel" {{ request('type') === 'fuel' ? 'selected' : '' }}>Fuel</option>
                        <option value="toll" {{ request('type') === 'toll' ? 'selected' : '' }}>Toll</option>
                        <option value="driver_pay" {{ request('type') === 'driver_pay' ? 'selected' : '' }}>Driver Pay</option>
                        <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="misc" {{ request('type') === 'misc' ? 'selected' : '' }}>Miscellaneous</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-filter"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5 class="mb-3">Period: {{ $filters['start_date']->format('M d, Y') }} - {{ $filters['end_date']->format('M d, Y') }}</h5>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-danger">{{ config('app.currency', 'PKR') }} {{ number_format($stats['total_expenses'], 2) }}</h3>
                <p>Total Expenses</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-info">{{ $stats['expense_count'] }}</h3>
                <p>Total Records</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-purple">{{ config('app.currency', 'PKR') }} {{ number_format($stats['avg_expense'], 2) }}</h3>
                <p>Average Expense</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-warning">{{ $stats['trips_with_expenses'] }}</h3>
                <p>Trips with Expenses</p>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="chart-container">
                <h6 class="mb-3">Expenses by Type</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach ($stats['by_type'] as $type => $amount)
                            <tr>
                                <td>
                                    <span class="badge 
                                        @if($type === 'fuel') bg-danger
                                        @elseif($type === 'toll') bg-info
                                        @elseif($type === 'driver_pay') bg-success
                                        @elseif($type === 'maintenance') bg-warning
                                        @else bg-secondary
                                        @endif
                                    ">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($amount, 2) }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        ({{ $stats['total_expenses'] > 0 ? number_format(($amount / $stats['total_expenses']) * 100, 1) : 0 }}%)
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="chart-container">
                <h6 class="mb-3">Count by Type</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach ($stats['count_by_type'] as $type => $count)
                            <tr>
                                <td>
                                    <span class="badge 
                                        @if($type === 'fuel') bg-danger
                                        @elseif($type === 'toll') bg-info
                                        @elseif($type === 'driver_pay') bg-success
                                        @elseif($type === 'maintenance') bg-warning
                                        @else bg-secondary
                                        @endif
                                    ">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $count }}</strong> records
                                    <br>
                                    <small class="text-muted">
                                        Avg: {{ config('app.currency', 'PKR') }} {{ number_format($stats['by_type'][$type] / max($count, 1), 2) }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="chart-container">
                <h6 class="mb-3">Top Expense Categories</h6>
                <table class="table table-sm">
                    <tbody>
                        @php
                            $sortedExpenses = collect($stats['by_type'])->sortDesc()->take(5);
                        @endphp
                        @foreach ($sortedExpenses as $type => $amount)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($amount, 2) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Routes by Expenses -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="table-responsive">
                <h6 class="mb-3">Top Routes by Total Expenses</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th class="text-center">Trips</th>
                            <th class="text-center">Expenses</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['top_routes'] as $route)
                            <tr>
                                <td>
                                    <strong>{{ $route->code }}</strong><br>
                                    <small class="text-muted">{{ $route->name }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $route->trips_count }}</span>
                                </td>
                                <td class="text-center">
                                    {{ $route->expense_count }}
                                </td>
                                <td class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($route->total_expense, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="table-responsive">
                <h6 class="mb-3">Top Buses by Expenses</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Bus</th>
                            <th class="text-center">Trips</th>
                            <th class="text-center">Expenses</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['top_buses'] as $bus)
                            <tr>
                                <td>
                                    <strong>{{ $bus->name }}</strong><br>
                                    <small class="text-muted">{{ $bus->registration_number }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $bus->trips_count }}</span>
                                </td>
                                <td class="text-center">
                                    {{ $bus->expense_count }}
                                </td>
                                <td class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($bus->total_expense, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="table-responsive">
                <h6 class="mb-3">Daily Expense Breakdown</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Expense Count</th>
                            <th class="text-end">Fuel</th>
                            <th class="text-end">Toll</th>
                            <th class="text-end">Driver Pay</th>
                            <th class="text-end">Maintenance</th>
                            <th class="text-end">Misc</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['daily_breakdown'] as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y (D)') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $day->expense_count }}</span>
                                </td>
                                <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($day->fuel ?? 0, 2) }}</td>
                                <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($day->toll ?? 0, 2) }}</td>
                                <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($day->driver_pay ?? 0, 2) }}</td>
                                <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($day->maintenance ?? 0, 2) }}</td>
                                <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($day->misc ?? 0, 2) }}</td>
                                <td class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($day->total_amount, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No expenses in this period</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($stats['daily_breakdown']->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th class="text-center">{{ $stats['expense_count'] }}</th>
                                <th class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['by_type']['fuel'] ?? 0, 2) }}</th>
                                <th class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['by_type']['toll'] ?? 0, 2) }}</th>
                                <th class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['by_type']['driver_pay'] ?? 0, 2) }}</th>
                                <th class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['by_type']['maintenance'] ?? 0, 2) }}</th>
                                <th class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['by_type']['misc'] ?? 0, 2) }}</th>
                                <th class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($stats['total_expenses'], 2) }}</strong>
                                </th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Top Expense Users -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="table-responsive">
                <h6 class="mb-3">Top Employees by Expenses Recorded</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th class="text-center">Expense Count</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['top_users'] as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $user->expense_count }}</span>
                                </td>
                                <td class="text-end">
                                    {{ config('app.currency', 'PKR') }} {{ number_format($user->total_expense, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="chart-container">
                <h6 class="mb-3">Expense Summary</h6>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Total Expenses:</strong></td>
                            <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['total_expenses'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Number of Records:</strong></td>
                            <td class="text-end">{{ $stats['expense_count'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Average Expense:</strong></td>
                            <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['avg_expense'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Trips with Expenses:</strong></td>
                            <td class="text-end">{{ $stats['trips_with_expenses'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Highest Single Expense:</strong></td>
                            <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['highest_expense'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Lowest Single Expense:</strong></td>
                            <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['lowest_expense'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">Export Reports</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Download detailed expense reports for the selected period</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" onclick="alert('PDF export functionality to be implemented')">
                            <i class="ti ti-file-type-pdf"></i> Export to PDF
                        </button>
                        <button class="btn btn-primary" onclick="alert('Excel export functionality to be implemented')">
                            <i class="ti ti-file-spreadsheet"></i> Export to Excel
                        </button>
                        <button class="btn btn-info" onclick="alert('CSV export functionality to be implemented')">
                            <i class="ti ti-file-type-csv"></i> Export to CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


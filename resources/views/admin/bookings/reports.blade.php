@extends('admin.layouts.app')

@section('title', 'Booking Reports')

@section('styles')
    <style>
        .report-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
                <h4 class="mb-2">📊 Booking Reports & Analytics</h4>
                <p class="mb-0">Comprehensive booking statistics and performance metrics</p>
            </div>
            <div>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-light">
                    <i class="ti ti-arrow-left"></i> Back to Bookings
                </a>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bookings.reports') }}" class="row g-3">
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
                    <label class="form-label">Booking Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="online" {{ request('type') === 'online' ? 'selected' : '' }}>Online</option>
                        <option value="counter" {{ request('type') === 'counter' ? 'selected' : '' }}>Counter</option>
                        <option value="phone" {{ request('type') === 'phone' ? 'selected' : '' }}>Phone</option>
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
                <h3 class="text-primary">{{ $stats['total_bookings'] }}</h3>
                <p>Total Bookings</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-success">{{ $stats['confirmed_bookings'] }}</h3>
                <p>Confirmed</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-warning">{{ $stats['pending_bookings'] }}</h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-danger">{{ $stats['cancelled_bookings'] }}</h3>
                <p>Cancelled</p>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-info">{{ number_format($stats['total_passengers']) }}</h3>
                <p>Total Passengers</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-success">{{ config('app.currency', 'PKR') }} {{ number_format($stats['total_revenue'], 2) }}</h3>
                <p>Total Revenue</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-purple">{{ config('app.currency', 'PKR') }} {{ number_format($stats['avg_booking_value'], 2) }}</h3>
                <p>Avg Booking Value</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3 class="text-orange">{{ number_format($stats['cancellation_rate'], 1) }}%</h3>
                <p>Cancellation Rate</p>
            </div>
        </div>
    </div>

    <!-- Bookings by Type -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="chart-container">
                <h6 class="mb-3">Bookings by Type</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach ($stats['by_type'] as $type => $count)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($type) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $count }}</strong>
                                    <small class="text-muted">
                                        ({{ $stats['total_bookings'] > 0 ? number_format(($count / $stats['total_bookings']) * 100, 1) : 0 }}%)
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
                <h6 class="mb-3">Bookings by Status</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach ($stats['by_status'] as $status => $count)
                            <tr>
                                <td>
                                    <span class="badge 
                                        @if($status === 'confirmed') bg-success
                                        @elseif($status === 'pending') bg-warning
                                        @elseif($status === 'cancelled') bg-danger
                                        @else bg-secondary
                                        @endif
                                    ">{{ ucfirst($status) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $count }}</strong>
                                    <small class="text-muted">
                                        ({{ $stats['total_bookings'] > 0 ? number_format(($count / $stats['total_bookings']) * 100, 1) : 0 }}%)
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
                <h6 class="mb-3">Revenue by Type</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach ($stats['revenue_by_type'] as $type => $revenue)
                            <tr>
                                <td>
                                    <span class="badge bg-success">{{ ucfirst($type) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($revenue, 2) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Routes by Bookings -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="table-responsive">
                <h6 class="mb-3">Top Routes by Bookings</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th class="text-center">Bookings</th>
                            <th class="text-center">Passengers</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['top_routes_by_bookings'] as $route)
                            <tr>
                                <td>
                                    <strong>{{ $route->code }}</strong><br>
                                    <small class="text-muted">{{ $route->name }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $route->bookings_count }}</span>
                                </td>
                                <td class="text-center">
                                    {{ $route->total_passengers }}
                                </td>
                                <td class="text-end">
                                    {{ config('app.currency', 'PKR') }} {{ number_format($route->total_revenue, 2) }}
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
                <h6 class="mb-3">Top Routes by Revenue</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th class="text-center">Bookings</th>
                            <th class="text-center">Passengers</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['top_routes_by_revenue'] as $route)
                            <tr>
                                <td>
                                    <strong>{{ $route->code }}</strong><br>
                                    <small class="text-muted">{{ $route->name }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $route->bookings_count }}</span>
                                </td>
                                <td class="text-center">
                                    {{ $route->total_passengers }}
                                </td>
                                <td class="text-end">
                                    <strong>{{ config('app.currency', 'PKR') }} {{ number_format($route->total_revenue, 2) }}</strong>
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
                <h6 class="mb-3">Daily Breakdown</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Confirmed</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Cancelled</th>
                            <th class="text-center">Passengers</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['daily_breakdown'] as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y (D)') }}</td>
                                <td class="text-center">{{ $day->total_bookings }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $day->confirmed_bookings }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning">{{ $day->pending_bookings }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $day->cancelled_bookings }}</span>
                                </td>
                                <td class="text-center">{{ $day->total_passengers }}</td>
                                <td class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($day->total_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No bookings in this period</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($stats['daily_breakdown']->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th class="text-center">{{ $stats['total_bookings'] }}</th>
                                <th class="text-center">{{ $stats['confirmed_bookings'] }}</th>
                                <th class="text-center">{{ $stats['pending_bookings'] }}</th>
                                <th class="text-center">{{ $stats['cancelled_bookings'] }}</th>
                                <th class="text-center">{{ number_format($stats['total_passengers']) }}</th>
                                <th class="text-end">{{ config('app.currency', 'PKR') }} {{ number_format($stats['total_revenue'], 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Export Reports</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Download detailed reports for the selected period</p>
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


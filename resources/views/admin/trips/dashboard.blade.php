@extends('admin.layouts.app')

@section('title', 'Trip Dashboard')

@section('styles')
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.875rem;
        }

        .alert-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .alert-card h5 {
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .trip-item {
            padding: 1rem;
            border-left: 4px solid #3b82f6;
            background: #f9fafb;
            margin-bottom: 0.75rem;
            border-radius: 4px;
        }

        .trip-item:hover {
            background: #f3f4f6;
        }
    </style>
@endsection

@section('content')
    <!-- Header -->
    <div class="dashboard-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-2">📊 Trip Management Dashboard</h4>
                <p class="mb-0">Real-time overview of trip lifecycle and statistics</p>
            </div>
            <div>
                <a href="{{ route('admin.trips.index') }}" class="btn btn-light">
                    <i class="ti ti-list"></i> All Trips
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5 class="mb-3">Today's Statistics - {{ $stats['date'] }}</h5>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-primary">{{ $stats['total_trips'] }}</h3>
                <p>Total Trips</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-warning">{{ $stats['by_status']['pending'] }}</h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-info">{{ $stats['by_status']['scheduled'] }}</h3>
                <p>Scheduled</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-purple">{{ $stats['by_status']['boarding'] }}</h3>
                <p>Boarding</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-success">{{ $stats['by_status']['ongoing'] }}</h3>
                <p>Ongoing</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-teal">{{ $stats['by_status']['completed'] }}</h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-danger">{{ $stats['by_status']['cancelled'] }}</h3>
                <p>Cancelled</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-orange">{{ $stats['by_status']['delayed'] }}</h3>
                <p>Delayed</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-success">{{ $stats['with_bus'] }}</h3>
                <p>With Bus</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-danger">{{ $stats['without_bus'] }}</h3>
                <p>No Bus</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-info">{{ $stats['with_bookings'] }}</h3>
                <p>With Bookings</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-muted">{{ $stats['without_bookings'] }}</h3>
                <p>No Bookings</p>
            </div>
        </div>
    </div>

    <!-- Trips Requiring Attention -->
    <div class="row">
        <!-- No Bus Assigned -->
        <div class="col-md-6">
            <div class="alert-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="ti ti-alert-circle text-warning"></i> Trips Without Bus Assignment
                    </h5>
                    <span class="badge bg-warning">{{ $attention['no_bus_assigned']->count() }}</span>
                </div>
                @if ($attention['no_bus_assigned']->count() > 0)
                    @foreach ($attention['no_bus_assigned'] as $trip)
                        <div class="trip-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $trip->route->code }}</strong> - {{ $trip->route->name }}<br>
                                    <small class="text-muted">{{ $trip->departure_datetime->format('M d, Y h:i A') }}</small>
                                </div>
                                <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-sm btn-primary">
                                    Assign Bus
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">All trips have buses assigned ✓</p>
                @endif
            </div>
        </div>

        <!-- Boarding Soon -->
        <div class="col-md-6">
            <div class="alert-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="ti ti-clock text-info"></i> Boarding Soon (Next 60 min)
                    </h5>
                    <span class="badge bg-info">{{ $attention['boarding_soon']->count() }}</span>
                </div>
                @if ($attention['boarding_soon']->count() > 0)
                    @foreach ($attention['boarding_soon'] as $trip)
                        <div class="trip-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $trip->route->code }}</strong> - {{ $trip->bus->name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $trip->departure_datetime->format('h:i A') }} ({{ $trip->departure_datetime->diffForHumans() }})</small>
                                </div>
                                <div>
                                    <span class="badge bg-primary">{{ $trip->bookings()->count() }} bookings</span>
                                    <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-sm btn-info ms-2">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No trips boarding soon</p>
                @endif
            </div>
        </div>

        <!-- Delayed Trips -->
        <div class="col-md-6">
            <div class="alert-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="ti ti-alert-triangle text-danger"></i> Delayed Trips
                    </h5>
                    <span class="badge bg-danger">{{ $attention['delayed']->count() }}</span>
                </div>
                @if ($attention['delayed']->count() > 0)
                    @foreach ($attention['delayed'] as $trip)
                        <div class="trip-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $trip->route->code }}</strong> - {{ $trip->bus->name ?? 'N/A' }}<br>
                                    <small class="text-muted">Scheduled: {{ $trip->departure_datetime->format('h:i A') }}</small>
                                </div>
                                <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-sm btn-warning">
                                    Manage
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No delayed trips ✓</p>
                @endif
            </div>
        </div>

        <!-- Low Occupancy -->
        <div class="col-md-6">
            <div class="alert-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="ti ti-user-x text-warning"></i> Low Occupancy Trips (&lt;30%)
                    </h5>
                    <span class="badge bg-warning">{{ $attention['low_occupancy']->count() }}</span>
                </div>
                @if ($attention['low_occupancy']->count() > 0)
                    @foreach ($attention['low_occupancy'] as $trip)
                        <div class="trip-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $trip->route->code }}</strong> - {{ $trip->bus->name }}<br>
                                    <small class="text-muted">{{ $trip->departure_datetime->format('M d, h:i A') }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-warning">{{ number_format($trip->getOccupancyRate(), 0) }}% occupied</span>
                                    <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-sm btn-info ms-2">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">All trips have good occupancy ✓</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="ti ti-list"></i> View All Trips
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.trips.requires-bus') }}" class="btn btn-outline-warning w-100 mb-2">
                                <i class="ti ti-bus"></i> Assign Buses
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-success w-100 mb-2" data-bs-toggle="modal"
                                data-bs-target="#generateTripsModal">
                                <i class="ti ti-plus"></i> Generate Trips
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-info w-100 mb-2">
                                <i class="ti ti-receipt"></i> View Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Trips Modal -->
    <div class="modal fade" id="generateTripsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.trips.generate') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Trips from Timetables</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required
                                value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" required
                                value="{{ now()->addDays(7)->format('Y-m-d') }}">
                        </div>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i>
                            This will automatically create trips for all active timetables in the selected date range.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate Trips</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


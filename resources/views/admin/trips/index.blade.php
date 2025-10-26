@extends('admin.layouts.app')

@section('title', 'Trip Management')

@section('styles')
    <style>
        .trips-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.875rem;
        }

        .filter-card {
            background: white;
            border-radius: 8px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-scheduled {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-boarding {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-ongoing {
            background: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background: #cffafe;
            color: #155e75;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-delayed {
            background: #fed7aa;
            color: #9a3412;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            margin: 0 2px;
        }
    </style>
@endsection

@section('content')
    <!-- Header -->
    <div class="trips-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-2">🚍 Trip Management</h4>
                <p class="mb-0">Manage daily trip schedules, bus assignments, and status tracking</p>
            </div>
            <div>
                <a href="{{ route('admin.trips.dashboard') }}" class="btn btn-light me-2">
                    <i class="ti ti-dashboard"></i> Dashboard
                </a>
                <a href="{{ route('admin.trips.requires-bus') }}" class="btn btn-warning">
                    <i class="ti ti-alert-circle"></i> Requires Bus ({{ $stats['without_bus'] }})
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-primary">{{ $stats['total_trips'] }}</h3>
                <p>Total Trips</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-warning">{{ $stats['pending_trips'] }}</h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-info">{{ $stats['scheduled_trips'] }}</h3>
                <p>Scheduled</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-success">{{ $stats['ongoing_trips'] }}</h3>
                <p>Ongoing</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <h3 class="text-danger">{{ $stats['without_bus'] }}</h3>
                <p>No Bus</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal"
                    data-bs-target="#generateTripsModal">
                    <i class="ti ti-plus"></i> Generate Trips
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.trips.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Route</label>
                <select name="route_id" class="form-select">
                    <option value="">All Routes</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                            {{ $route->code }} - {{ $route->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Bus Assignment</label>
                <select name="bus_assignment" class="form-select">
                    <option value="">All</option>
                    <option value="assigned" {{ request('bus_assignment') == 'assigned' ? 'selected' : '' }}>Assigned
                    </option>
                    <option value="not_assigned" {{ request('bus_assignment') == 'not_assigned' ? 'selected' : '' }}>Not
                        Assigned</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">
                    <i class="ti ti-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Trips Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Bus</th>
                            <th>Status</th>
                            <th>Bookings</th>
                            <th>Occupancy</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $trip)
                            <tr>
                                <td><strong>#{{ $trip->id }}</strong></td>
                                <td>
                                    <strong>{{ $trip->route->code }}</strong><br>
                                    <small class="text-muted">{{ $trip->route->name }}</small>
                                </td>
                                <td>
                                    <strong>{{ $trip->departure_datetime->format('M d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $trip->departure_datetime->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if ($trip->bus)
                                        <span class="badge bg-success">{{ $trip->bus->name }}</span><br>
                                        <small class="text-muted">{{ $trip->bus->registration_number }}</small>
                                    @else
                                        <span class="badge bg-danger">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($trip->status->value) }}">
                                        {{ $trip->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $trip->bookings()->count() }}</strong> bookings<br>
                                    <small
                                        class="text-success">{{ $trip->confirmedBookings()->count() }} confirmed</small>
                                </td>
                                <td>
                                    @if ($trip->bus)
                                        @php
                                            $occupancy = $trip->getOccupancyRate();
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $occupancy > 80 ? 'success' : ($occupancy > 50 ? 'info' : 'warning') }}"
                                                style="width: {{ $occupancy }}%">
                                                {{ number_format($occupancy, 0) }}%
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-sm btn-info">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="ti ti-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mb-0">No trips found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $trips->firstItem() ?? 0 }} to {{ $trips->lastItem() ?? 0 }} of {{ $trips->total() }}
                    trips
                </div>
                <div>
                    {{ $trips->links() }}
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


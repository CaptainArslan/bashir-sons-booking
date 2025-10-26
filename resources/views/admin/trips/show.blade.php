@extends('admin.layouts.app')

@section('title', 'Trip Details #' . $trip->id)

@section('styles')
    <style>
        .trip-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            height: 100%;
        }

        .info-card h5 {
            margin-bottom: 1rem;
            font-weight: 600;
            color: #1f2937;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #6b7280;
        }

        .info-value {
            font-weight: 600;
            color: #111827;
        }

        .action-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .stat-box {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background: #f9fafb;
        }

        .stat-box h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-box p {
            color: #6b7280;
            margin: 0;
            font-size: 0.875rem;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #3b82f6;
        }

        .timeline-item:after {
            content: '';
            position: absolute;
            left: -1.25rem;
            top: 1rem;
            width: 2px;
            height: calc(100% - 1rem);
            background: #e5e7eb;
        }

        .timeline-item:last-child:after {
            display: none;
        }
    </style>
@endsection

@section('content')
    <!-- Back Button & Header -->
    <div class="mb-3">
        <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary btn-sm">
            <i class="ti ti-arrow-left"></i> Back to Trips
        </a>
    </div>

    <div class="trip-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2">Trip #{{ $trip->id }}</h4>
                <p class="mb-0">
                    <strong>{{ $trip->route->code }}</strong> - {{ $trip->route->name }}<br>
                    Departure: {{ $trip->departure_datetime->format('l, M d, Y \a\t h:i A') }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-light text-dark px-3 py-2" style="font-size: 1rem;">
                    {{ $trip->status->label() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-box">
                <h3 class="text-primary">{{ $statistics['total_bookings'] }}</h3>
                <p>Total Bookings</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h3 class="text-success">{{ $statistics['confirmed_bookings'] }}</h3>
                <p>Confirmed</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h3 class="text-info">{{ $statistics['booked_seats'] }}</h3>
                <p>Booked Seats</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h3 class="text-warning">{{ $statistics['available_seats'] }}</h3>
                <p>Available</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h3 class="text-success">₨{{ number_format($statistics['total_revenue'], 0) }}</h3>
                <p>Revenue</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-box">
                <h3 class="text-danger">₨{{ number_format($statistics['total_expenses'], 0) }}</h3>
                <p>Expenses</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Trip Information -->
            <div class="info-card">
                <h5><i class="ti ti-info-circle"></i> Trip Information</h5>
                <div class="info-row">
                    <span class="info-label">Route</span>
                    <span class="info-value">{{ $trip->route->code }} - {{ $trip->route->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Timetable</span>
                    <span class="info-value">{{ $trip->timetable->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Departure Date</span>
                    <span class="info-value">{{ $trip->departure_datetime->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Departure Time</span>
                    <span class="info-value">{{ $trip->departure_datetime->format('h:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estimated Arrival</span>
                    <span class="info-value">
                        {{ $trip->estimated_arrival_datetime ? $trip->estimated_arrival_datetime->format('h:i A') : 'N/A' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="badge bg-{{ $trip->status->color() }}">{{ $trip->status->label() }}</span>
                    </span>
                </div>
            </div>

            <!-- Bus Information -->
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="ti ti-bus"></i> Bus Assignment</h5>
                    @if (!$trip->bus && $trip->canAssignBus())
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#assignBusModal">
                            <i class="ti ti-plus"></i> Assign Bus
                        </button>
                    @endif
                </div>
                @if ($trip->bus)
                    <div class="info-row">
                        <span class="info-label">Bus Name</span>
                        <span class="info-value">{{ $trip->bus->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Registration</span>
                        <span class="info-value">{{ $trip->bus->registration_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Layout</span>
                        <span class="info-value">{{ $trip->bus->busLayout->name }} ({{ $trip->bus->busLayout->total_seats }}
                            seats)</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Occupancy Rate</span>
                        <span class="info-value">
                            <div class="progress" style="width: 200px; height: 24px;">
                                <div class="progress-bar bg-success" style="width: {{ $trip->getOccupancyRate() }}%">
                                    {{ number_format($trip->getOccupancyRate(), 0) }}%
                                </div>
                            </div>
                        </span>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-circle"></i> No bus assigned to this trip yet.
                    </div>
                @endif
            </div>

            <!-- Bookings -->
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="ti ti-ticket"></i> Bookings ({{ $trip->bookings->count() }})</h5>
                </div>
                @if ($trip->bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Booking #</th>
                                    <th>Passenger</th>
                                    <th>Seats</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trip->bookings as $booking)
                                    <tr>
                                        <td><strong>{{ $booking->booking_number }}</strong></td>
                                        <td>{{ $booking->passenger_contact_name ?? $booking->user->name }}</td>
                                        <td>{{ $booking->bookingSeats->count() }} seats</td>
                                        <td>₨{{ number_format($booking->final_amount, 0) }}</td>
                                        <td><span
                                                class="badge bg-{{ $booking->status->color() }}">{{ $booking->status->label() }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No bookings yet</p>
                @endif
            </div>

            <!-- Expenses -->
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="ti ti-receipt"></i> Expenses ({{ $trip->expenses->count() }})</h5>
                    @if ($trip->canAddExpenses())
                        <a href="{{ route('admin.expenses.create', ['trip_id' => $trip->id]) }}"
                            class="btn btn-primary btn-sm">
                            <i class="ti ti-plus"></i> Add Expense
                        </a>
                    @endif
                </div>
                @if ($trip->expenses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trip->expenses as $expense)
                                    <tr>
                                        <td><span
                                                class="badge bg-{{ $expense->type->color() }}">{{ $expense->type->label() }}</span>
                                        </td>
                                        <td>{{ $expense->description }}</td>
                                        <td><strong>₨{{ number_format($expense->amount, 0) }}</strong></td>
                                        <td>{{ $expense->incurred_date->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-active">
                                    <td colspan="2"><strong>Total Expenses</strong></td>
                                    <td colspan="2"><strong>₨{{ number_format($trip->getTotalExpenses(), 0) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No expenses recorded</p>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="action-card">
                <h5 class="mb-3"><i class="ti ti-bolt"></i> Quick Actions</h5>

                @if ($trip->status === \App\Enums\TripStatusEnum::Scheduled)
                    <form action="{{ route('admin.trips.start', $trip->id) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Start this trip?')">
                            <i class="ti ti-player-play"></i> Start Trip
                        </button>
                    </form>
                @endif

                @if ($trip->status === \App\Enums\TripStatusEnum::Ongoing)
                    <form action="{{ route('admin.trips.complete', $trip->id) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-info w-100"
                            onclick="return confirm('Mark trip as completed?')">
                            <i class="ti ti-check"></i> Complete Trip
                        </button>
                    </form>
                @endif

                @if ($trip->canAssignBus())
                    <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal"
                        data-bs-target="#assignBusModal">
                        <i class="ti ti-bus"></i> {{ $trip->bus ? 'Change Bus' : 'Assign Bus' }}
                    </button>
                @endif

                <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal"
                    data-bs-target="#changeStatusModal">
                    <i class="ti ti-arrow-right-circle"></i> Change Status
                </button>

                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                    data-bs-target="#cancelTripModal">
                    <i class="ti ti-x"></i> Cancel Trip
                </button>
            </div>

            <!-- Route Timeline -->
            <div class="info-card">
                <h5 class="mb-3"><i class="ti ti-route"></i> Route Stops</h5>
                <div class="timeline">
                    @foreach ($trip->route->routeStops as $stop)
                        <div class="timeline-item">
                            <strong>{{ $stop->terminal->name }}</strong><br>
                            <small class="text-muted">{{ $stop->terminal->city->name }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Bus Modal -->
    <div class="modal fade" id="assignBusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.trips.assign-bus', $trip->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Bus to Trip</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Bus</label>
                            <select name="bus_id" class="form-select" required>
                                <option value="">Choose a bus...</option>
                                @foreach ($availableBuses as $bus)
                                    <option value="{{ $bus->id }}"
                                        {{ $trip->bus_id == $bus->id ? 'selected' : '' }}>
                                        {{ $bus->name }} - {{ $bus->registration_number }}
                                        ({{ $bus->busLayout->total_seats }} seats)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Bus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.trips.update-status', $trip->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Change Trip Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Status</label>
                            <select name="status" class="form-select" required>
                                @foreach (\App\Enums\TripStatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        {{ $trip->status === $status ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Trip Modal -->
    <div class="modal fade" id="cancelTripModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.trips.cancel', $trip->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Trip</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="ti ti-alert-triangle"></i>
                            <strong>Warning!</strong> Cancelling this trip will also cancel all associated bookings.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cancellation Reason</label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Trip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


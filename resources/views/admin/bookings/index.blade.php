@extends('admin.layouts.app')

@section('title', 'Booking Management')

@section('styles')
    @include('admin.layouts.datatables')
@endsection

@section('content')
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">🎫 Booking Management</h4>
                <div>
                    <a href="{{ route('admin.bookings.reports') }}" class="btn btn-light btn-sm">
                        <i class="ti ti-chart-bar"></i> Reports
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h3 class="text-primary mb-1">{{ $stats['total_bookings'] }}</h3>
                        <p class="mb-0 text-muted">Total Bookings</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h3 class="text-success mb-1">{{ $stats['confirmed_bookings'] }}</h3>
                        <p class="mb-0 text-muted">Confirmed</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h3 class="text-warning mb-1">{{ $stats['pending_bookings'] }}</h3>
                        <p class="mb-0 text-muted">Pending</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h3 class="text-success mb-1">₨{{ number_format($stats['total_revenue'], 0) }}</h3>
                        <p class="mb-0 text-muted">Total Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" name="booking_number" class="form-control" placeholder="Booking Number"
                        value="{{ request('booking_number') }}">
                </div>
                <div class="col-md-2">
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
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="route_id" class="form-select">
                        <option value="">All Routes</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                {{ $route->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Passenger</th>
                            <th>Trip</th>
                            <th>Type</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td><strong>{{ $booking->booking_number }}</strong></td>
                                <td>
                                    {{ $booking->passenger_contact_name ?? $booking->user?->name }}<br>
                                    <small class="text-muted">{{ $booking->passenger_contact_phone }}</small>
                                </td>
                                <td>
                                    <strong>{{ $booking->trip->route->code }}</strong><br>
                                    <small class="text-muted">{{ $booking->trip->departure_datetime->format('M d, Y') }}</small>
                                </td>
                                <td><span class="badge bg-{{ $booking->type->color() }}">{{ $booking->type->label() }}</span>
                                </td>
                                <td>{{ $booking->bookingSeats->count() }}</td>
                                <td><strong>₨{{ number_format($booking->final_amount, 0) }}</strong></td>
                                <td><span
                                        class="badge bg-{{ $booking->status->color() }}">{{ $booking->status->label() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $bookings->links() }}
        </div>
    </div>
@endsection


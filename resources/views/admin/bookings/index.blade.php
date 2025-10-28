@extends('admin.layouts.app')

@section('title', 'Booking Management')

@section('styles')
    <style>
        .bookings-header {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .bookings-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
    <!-- Header -->
    <div class="bookings-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-book-bookmark me-2"></i>Booking Management</h4>
                <p class="mb-0 opacity-90" style="font-size: 0.875rem;">Manage all customer bookings and reservations</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.bookings.create') }}" class="btn btn-light btn-sm">
                    <i class="bx bx-plus-circle me-1"></i>Create Booking
                </a>
                <a href="{{ route('admin.bookings.reports') }}" class="btn btn-light btn-sm">
                    <i class="bx bx-bar-chart me-1"></i>Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="stats-card">
                <h3 class="text-primary mb-1">{{ $stats['total_bookings'] }}</h3>
                <p class="mb-0 text-muted">Total Bookings</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h3 class="text-success mb-1">{{ $stats['confirmed_bookings'] }}</h3>
                <p class="mb-0 text-muted">Confirmed</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h3 class="text-warning mb-1">{{ $stats['pending_bookings'] }}</h3>
                <p class="mb-0 text-muted">Pending</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h3 class="text-success mb-1">₨{{ number_format($stats['total_revenue'], 0) }}</h3>
                <p class="mb-0 text-muted">Total Revenue</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Booking Number</label>
                <input type="text" id="filter-booking-number" class="form-control form-control-sm"
                    placeholder="Search by booking number...">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Type</label>
                <select id="filter-type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Route</label>
                <select id="filter-route" class="form-select form-select-sm">
                    <option value="">All Routes</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route->id }}">{{ $route->code }} - {{ $route->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Date</label>
                <input type="date" id="filter-date" class="form-control form-control-sm">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="bookings-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Passenger</th>
                            <th>Trip Details</th>
                            <th>Route</th>
                            <th>Type</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Terminal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#bookings-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.bookings.data') }}",
                    data: function(d) {
                        d.booking_number = $('#filter-booking-number').val();
                        d.status = $('#filter-status').val();
                        d.type = $('#filter-type').val();
                        d.route_id = $('#filter-route').val();
                        d.date = $('#filter-date').val();
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'booking_info',
                        name: 'booking_number',
                    },
                    {
                        data: 'passenger_info',
                        name: 'passenger_contact_name',
                    },
                    {
                        data: 'trip_info',
                        name: 'trip_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'route_info',
                        name: 'route_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'type_badge',
                        name: 'type',
                    },
                    {
                        data: 'seats_count',
                        name: 'seats_count',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'amount_info',
                        name: 'final_amount',
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                    },
                    {
                        data: 'terminal_info',
                        name: 'terminal_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                    },
                ],
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<i class="bx bx-loader-alt bx-spin"></i> Loading...',
                    emptyTable: 'No bookings found',
                    zeroRecords: 'No matching bookings found',
                }
            });

            // Filter events
            $('#filter-booking-number, #filter-status, #filter-type, #filter-route, #filter-date').on(
                'change keyup',
                function() {
                    table.draw();
                });
        });

        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                const reason = prompt('Please enter cancellation reason (optional):');

                $.ajax({
                    url: `/admin/bookings/${bookingId}/cancel`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: reason
                    },
                    success: function(response) {
                        alert('Booking cancelled successfully');
                        $('#bookings-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        alert('Error cancelling booking: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    }
                });
            }
        }
    </script>
@endsection


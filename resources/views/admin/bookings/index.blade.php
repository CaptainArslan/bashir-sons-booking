@extends('admin.layouts.app')

@section('title', 'Booking Management')

@section('content')
<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-book"></i> Booking Management & Reports
                </h5>
                <a href="{{ route('admin.bookings.console') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus-circle"></i> New Booking
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card-body bg-light">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Booking Date</label>
                    <input type="date" class="form-control form-control-sm" id="filterDate">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select class="form-select form-select-sm" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="hold">On Hold</option>
                        <option value="checked_in">Checked In</option>
                        <option value="boarded">Boarded</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Payment Status</label>
                    <select class="form-select form-select-sm" id="filterPaymentStatus">
                        <option value="">All Payments</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Channel</label>
                    <select class="form-select form-select-sm" id="filterChannel">
                        <option value="">All Channels</option>
                        <option value="counter">Counter</option>
                        <option value="phone">Phone</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Booking Number</label>
                    <input type="text" class="form-control form-control-sm" id="filterBookingNumber" placeholder="e.g., 000123">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-sm" onclick="reloadTable()">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="bookingsTable">
                    <thead class="table-dark">
                        <tr>
                            <th><i class="fas fa-ticket-alt"></i> Booking #</th>
                            <th><i class="fas fa-calendar"></i> Date & Time</th>
                            <th><i class="fas fa-route"></i> Route</th>
                            <th><i class="fas fa-chair"></i> Seats</th>
                            <th><i class="fas fa-users"></i> Passengers</th>
                            <th><i class="fas fa-money-bill"></i> Amount</th>
                            <th><i class="fas fa-truck"></i> Channel</th>
                            <th><i class="fas fa-check-circle"></i> Status</th>
                            <th><i class="fas fa-credit-card"></i> Payment</th>
                            <th><i class="fas fa-cog"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-eye"></i> Booking Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bookingDetailsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let bookingsTable;

    document.addEventListener('DOMContentLoaded', function() {
        initializeDataTable();
    });

    function initializeDataTable() {
        bookingsTable = $('#bookingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.bookings.data") }}',
                data: function(d) {
                    d.date = document.getElementById('filterDate').value;
                    d.status = document.getElementById('filterStatus').value;
                    d.payment_status = document.getElementById('filterPaymentStatus').value;
                    d.channel = document.getElementById('filterChannel').value;
                    d.booking_number = document.getElementById('filterBookingNumber').value;
                }
            },
            columns: [
                { data: 'booking_number', name: 'booking_number' },
                { data: 'created_at', name: 'created_at' },
                { data: 'route', name: 'route' },
                { data: 'seats', name: 'seats' },
                { data: 'passengers_count', name: 'passengers_count' },
                { data: 'amount', name: 'final_amount' },
                { data: 'channel', name: 'channel' },
                { data: 'status', name: 'status' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']],
            pageLength: 25,
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search bookings...'
            },
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-download"></i> CSV',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-sm btn-info'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-sm btn-danger'
                }
            ]
        });
    }

    function reloadTable() {
        bookingsTable.ajax.reload();
    }

    function resetFilters() {
        document.getElementById('filterDate').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterPaymentStatus').value = '';
        document.getElementById('filterChannel').value = '';
        document.getElementById('filterBookingNumber').value = '';
        reloadTable();
    }

    function viewBookingDetails(bookingId) {
        $.ajax({
            url: `/admin/bookings/${bookingId}`,
            type: 'GET',
            success: function(html) {
                document.getElementById('bookingDetailsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('bookingDetailsModal')).show();
            },
            error: function() {
                alert('Failed to load booking details');
            }
        });
    }

    function editBooking(bookingId) {
        window.location.href = `/admin/bookings/${bookingId}/edit`;
    }

    function deleteBooking(bookingId) {
        if (confirm('Are you sure you want to delete this booking?')) {
            $.ajax({
                url: `/admin/bookings/${bookingId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                success: function() {
                    alert('Booking deleted successfully');
                    reloadTable();
                },
                error: function(error) {
                    alert('Failed to delete booking: ' + (error.responseJSON?.message || 'Unknown error'));
                }
            });
        }
    }

    function printBooking(bookingId) {
        window.open(`/admin/bookings/${bookingId}/print`, '_blank');
    }
</script>
@endsection

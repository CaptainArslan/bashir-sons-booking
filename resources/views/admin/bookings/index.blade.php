@extends('admin.layouts.app')

@section('title', 'Booking Management & Reports')

@section('content')
<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Booking Management</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Booking Reports</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        @can('create bookings')
            <a href="{{ route('admin.bookings.console') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus"></i> New Booking
            </a>
        @endcan
    </div>
</div>
<!--end breadcrumb-->

<div class="container-fluid">
    <!-- Filters Card -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-filter-alt text-primary"></i> Filters & Search
                </h6>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true">
                    <i class="bx bx-chevron-down"></i> Toggle Filters
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Date Range Section -->
                    <div class="col-lg-12 mb-3">
                        <h6 class="text-muted mb-3 fw-semibold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-calendar"></i> Date Range
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Date From</label>
                                <input type="date" class="form-control form-control-sm" id="filterDateFrom" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Date To</label>
                                <input type="date" class="form-control form-control-sm" id="filterDateTo" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Start Time (Shift Start)</label>
                                <input type="time" class="form-control form-control-sm" id="filterStartTime">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">End Time (Shift End)</label>
                                <input type="time" class="form-control form-control-sm" id="filterEndTime" value="{{ date('H:i') }}">
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Booking Number</label>
                                <input type="text" class="form-control form-control-sm" id="filterBookingNumber"
                                    placeholder="Search by booking number">
                            </div>
                        </div>
                    </div>

                    <!-- Terminal Section -->
                    <div class="col-lg-12 mb-3">
                        <h6 class="text-muted mb-3 fw-semibold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-map"></i> Terminal
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">From Terminal</label>
                                <select class="form-select form-select-sm" id="filterFromTerminal">
                                    <option value="">All From Terminals</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}">
                                            {{ $terminal->name }}{{ $terminal->code ? ' (' . $terminal->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">To Terminal</label>
                                <select class="form-select form-select-sm" id="filterToTerminal">
                                    <option value="">All To Terminals</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}">
                                            {{ $terminal->name }}{{ $terminal->code ? ' (' . $terminal->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status & User Section -->
                    <div class="col-lg-12 mb-3">
                        <h6 class="text-muted mb-3 fw-semibold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-user"></i> Status & User
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Employee (Booked By)</label>
                                <select class="form-select form-select-sm" id="filterEmployee">
                                    <option value="">All Employees</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }}{{ $employee->email ? ' (' . $employee->email . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Customer/User</label>
                                <select class="form-select form-select-sm" id="filterCustomer">
                                    <option value="">All Customers</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">
                                            {{ $customer->name }}{{ $customer->email ? ' (' . $customer->email . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Booking Status</label>
                                <select class="form-select form-select-sm" id="filterStatus">
                                    <option value="">All Status</option>
                                    @foreach ($bookingStatuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                    @endforeach
                                    <option value="checked_in">Checked In</option>
                                    <option value="boarded">Boarded</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Payment Status</label>
                                <select class="form-select form-select-sm" id="filterPaymentStatus">
                                    <option value="">All Payments</option>
                                    @foreach ($paymentStatuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                    @endforeach
                                    <option value="partial">Partial</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Channel</label>
                                <select class="form-select form-select-sm" id="filterChannel">
                                    <option value="">All Channels</option>
                                    @foreach ($channels as $channel)
                                        <option value="{{ $channel->value }}">{{ $channel->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Booking Type</label>
                                <select class="form-select form-select-sm" id="filterAdvance">
                                    <option value="">All Bookings</option>
                                    <option value="1">Advance Bookings Only</option>
                                    <option value="0">Regular Bookings Only</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-lg-12 mt-3 pt-3 border-top">
                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                            <button class="btn btn-primary btn-sm" onclick="reloadTable()">
                                <i class="bx bx-search"></i> Apply Filters
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                <i class="bx bx-refresh"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-table text-primary"></i> Bookings Report
                </h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="exportReport()">
                        <i class="bx bx-download"></i> Export Report
                    </button>
                    <button class="btn btn-info btn-sm" onclick="exportPassengerInfo()">
                        <i class="bx bx-user"></i> Export Passenger Info
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="bookingsTable">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bx bx-ticket"></i> Booking #</th>
                            <th><i class="bx bx-calendar"></i> Date & Time</th>
                            <th><i class="bx bx-route"></i> Route</th>
                            <th><i class="bx bx-user"></i> Passengers</th>
                            <th><i class="bx bx-chair"></i> Seats</th>
                            <th><i class="bx bx-store"></i> Channel</th>
                            <th><i class="bx bx-check-circle"></i> Status</th>
                            <th><i class="bx bx-calendar-check"></i> Type</th>
                            <th><i class="bx bx-check-square"></i> Is Advance</th>
                            <th><i class="bx bx-money"></i> Amount</th>
                            <th><i class="bx bx-credit-card"></i> Payment</th>
                            <th><i class="bx bx-user"></i> Employee</th>
                            <th class="text-center"><i class="bx bx-cog"></i> Actions</th>
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
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-eye"></i> Booking Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                    <div id="bookingDetailsContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .radius-10 {
            border-radius: 10px;
        }

        .border-start {
            border-left-width: 4px !important;
        }

        .bg-light-primary {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .bg-light-success {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-light-info {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .text-primary {
            color: #0d6efd !important;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-info {
            color: #0dcaf0 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .widgets-icons-2 {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #bookingDetailsModal .modal-body .row {
            margin-bottom: 1rem;
        }

        #bookingDetailsModal .modal-body .card {
            margin-bottom: 0.75rem;
            border: 1px solid #e0e0e0;
        }

        #bookingDetailsModal .modal-body .card-body {
            padding: 1rem;
        }

        #bookingDetailsModal .modal-body .table {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        #bookingDetailsModal .modal-body .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }

        #bookingDetailsModal .modal-body h6 {
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }

        #bookingDetailsModal .modal-body h5 {
            font-size: 1.1rem;
        }

        #bookingDetailsModal .modal-body p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        #bookingDetailsModal .modal-body .alert {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        /* Actions Column Styling - Simple Solid Colors */
        #bookingsTable tbody td:last-child {
            white-space: nowrap;
            min-width: 150px;
            text-align: center;
        }

        #bookingsTable tbody td:last-child .d-flex {
            justify-content: center;
        }

        #bookingsTable .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        #bookingsTable .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            opacity: 0.9;
        }

        #bookingsTable .btn i {
            font-size: 1rem;
        }

        .card {
            border-radius: 8px;
        }

        .table thead th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>

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
                    url: '{{ route('admin.bookings.data') }}',
                    data: function(d) {
                        d.date_from = document.getElementById('filterDateFrom').value;
                        d.date_to = document.getElementById('filterDateTo').value;
                        d.start_time = document.getElementById('filterStartTime').value;
                        d.end_time = document.getElementById('filterEndTime').value;
                        d.status = document.getElementById('filterStatus').value;
                        d.payment_status = document.getElementById('filterPaymentStatus').value;
                        d.channel = document.getElementById('filterChannel').value;
                        d.booking_number = document.getElementById('filterBookingNumber').value;
                        d.from_terminal_id = document.getElementById('filterFromTerminal').value;
                        d.to_terminal_id = document.getElementById('filterToTerminal').value;
                        d.employee_id = document.getElementById('filterEmployee').value;
                        d.customer_id = document.getElementById('filterCustomer').value;
                        d.is_advance = document.getElementById('filterAdvance').value;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', error, thrown);

                        let errorMessage = 'Failed to load bookings data.';

                        if (xhr.status === 0) {
                            errorMessage = 'Network error. Please check your internet connection.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'Bookings endpoint not found. Please refresh the page.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error occurred. Please try again later.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || errorMessage;
                            } catch (e) {
                                errorMessage = xhr.responseText.substring(0, 100);
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Loading Error',
                            text: errorMessage,
                            confirmButtonColor: '#d33',
                            footer: 'If this problem persists, please contact support.'
                        });
                    }
                },
                columns: [{
                        data: 'booking_number',
                        name: 'booking_number'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'route',
                        name: 'route'
                    },
                    {
                        data: 'passengers',
                        name: 'passengers',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'seats',
                        name: 'seats'
                    },
                    {
                        data: 'channel',
                        name: 'channel'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'booking_type',
                        name: 'booking_type',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'is_advance',
                        name: 'is_advance',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'amount',
                        name: 'final_amount'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'employee',
                        name: 'employee',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search bookings...',
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: 'No bookings found',
                    zeroRecords: 'No matching bookings found'
                },
                dom: 'lfrtip',
                // buttons: [{
                //         extend: 'csv',
                //         text: '<i class="fas fa-download"></i> CSV',
                //         className: 'btn btn-sm btn-success'
                //     },
                //     {
                //         extend: 'excel',
                //         text: '<i class="fas fa-file-excel"></i> Excel',
                //         className: 'btn btn-sm btn-info'
                //     },
                //     {
                //         extend: 'pdf',
                //         text: '<i class="fas fa-file-pdf"></i> PDF',
                //         className: 'btn btn-sm btn-danger'
                //     }
                // ]
            });
        }

        function reloadTable() {
            try {
                if (bookingsTable) {
                    bookingsTable.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Table Not Initialized',
                        text: 'Please refresh the page to reload the table.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            } catch (error) {
                console.error('Error reloading table:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Reload Error',
                    text: 'Failed to reload bookings table. Please refresh the page.',
                    confirmButtonColor: '#d33'
                });
            }
        }

        function resetFilters() {
            try {
                document.getElementById('filterDateFrom').value = '';
                document.getElementById('filterDateTo').value = '';
                document.getElementById('filterStartTime').value = '';
                document.getElementById('filterEndTime').value = '';
                document.getElementById('filterStatus').value = '';
                document.getElementById('filterPaymentStatus').value = '';
                document.getElementById('filterChannel').value = '';
                document.getElementById('filterAdvance').value = '';
                document.getElementById('filterBookingNumber').value = '';
                document.getElementById('filterFromTerminal').value = '';
                document.getElementById('filterToTerminal').value = '';
                document.getElementById('filterEmployee').value = '';
                document.getElementById('filterCustomer').value = '';
                reloadTable();
            } catch (error) {
                console.error('Error resetting filters:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Reset Error',
                    text: 'Failed to reset filters. Please refresh the page.',
                    confirmButtonColor: '#d33'
                });
            }
        }

        function exportReport() {
            const params = new URLSearchParams({
                date_from: document.getElementById('filterDateFrom').value || '',
                date_to: document.getElementById('filterDateTo').value || '',
                start_time: document.getElementById('filterStartTime').value || '',
                end_time: document.getElementById('filterEndTime').value || '',
                status: document.getElementById('filterStatus').value || '',
                payment_status: document.getElementById('filterPaymentStatus').value || '',
                channel: document.getElementById('filterChannel').value || '',
                booking_number: document.getElementById('filterBookingNumber').value || '',
                from_terminal_id: document.getElementById('filterFromTerminal').value || '',
                to_terminal_id: document.getElementById('filterToTerminal').value || '',
                employee_id: document.getElementById('filterEmployee').value || '',
                customer_id: document.getElementById('filterCustomer').value || '',
                is_advance: document.getElementById('filterAdvance').value || ''
            });

            window.open("{{ route('admin.bookings.export') }}?" + params.toString(), '_blank');
        }

        function exportPassengerInfo() {
            const params = new URLSearchParams({
                date_from: document.getElementById('filterDateFrom').value || '',
                date_to: document.getElementById('filterDateTo').value || '',
                start_time: document.getElementById('filterStartTime').value || '',
                end_time: document.getElementById('filterEndTime').value || '',
                status: document.getElementById('filterStatus').value || '',
                payment_status: document.getElementById('filterPaymentStatus').value || '',
                channel: document.getElementById('filterChannel').value || '',
                booking_number: document.getElementById('filterBookingNumber').value || '',
                from_terminal_id: document.getElementById('filterFromTerminal').value || '',
                to_terminal_id: document.getElementById('filterToTerminal').value || '',
                employee_id: document.getElementById('filterEmployee').value || '',
                customer_id: document.getElementById('filterCustomer').value || '',
                is_advance: document.getElementById('filterAdvance').value || '',
                type: 'passenger_info'
            });

            window.open("{{ route('admin.bookings.export') }}?" + params.toString(), '_blank');
        }

        function viewBookingDetails(bookingId) {
            if (!bookingId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Booking',
                    text: 'Booking ID is missing.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we fetch booking details.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/admin/bookings/${bookingId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                success: function(html) {
                    Swal.close();
                    document.getElementById('bookingDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('bookingDetailsModal')).show();
                },
                error: function(xhr, status, error) {
                    Swal.close();

                    let errorMessage = 'Failed to load booking details.';

                    if (xhr.status === 404) {
                        errorMessage = 'Booking not found. It may have been deleted.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to view this booking.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error occurred. Please try again later.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Loading Failed',
                        text: errorMessage,
                        confirmButtonColor: '#d33'
                    });
                }
            });
        }

        function editBooking(bookingId) {
            if (!bookingId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Booking',
                    text: 'Booking ID is missing.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            try {
                window.location.href = `/admin/bookings/${bookingId}/edit`;
            } catch (error) {
                console.error('Error navigating to edit page:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Navigation Error',
                    text: 'Failed to navigate to edit page. Please try again.',
                    confirmButtonColor: '#d33'
                });
            }
        }


        function printBooking(bookingId) {
            if (!bookingId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Booking',
                    text: 'Booking ID is missing.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            try {
                const printWindow = window.open(`/admin/bookings/${bookingId}/print`, '_blank');

                if (!printWindow) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Popup Blocked',
                        text: 'Please allow popups for this site to print the booking ticket.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            } catch (error) {
                console.error('Error opening print window:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Print Error',
                    text: 'Failed to open print window. Please try again.',
                    confirmButtonColor: '#d33'
                });
            }
        }
    </script>
@endsection

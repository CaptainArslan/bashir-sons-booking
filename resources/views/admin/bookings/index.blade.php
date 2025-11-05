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
            <div class="card-body bg-light">
                <!-- Quick Date Range and Terminal Filter -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-2">Quick Date Range:</label>
                        <select class="form-select form-select-sm" id="quickDateRange" onchange="handleQuickDateRange()">
                            <option value="">Select Date Range</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-2">Terminal:</label>
                        <select class="form-select form-select-sm" id="filterTerminal">
                            <option value="">All Terminals</option>
                            @foreach ($terminals as $terminal)
                                <option value="{{ $terminal->id }}">
                                    {{ $terminal->name }}{{ $terminal->code ? ' (' . $terminal->code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-2">From Terminal:</label>
                        <select class="form-select form-select-sm" id="filterFromTerminal">
                            <option value="">All From Terminals</option>
                            @foreach ($terminals as $terminal)
                                <option value="{{ $terminal->id }}">
                                    {{ $terminal->name }}{{ $terminal->code ? ' (' . $terminal->code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-2">To Terminal:</label>
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

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Date From</label>
                        <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Date To</label>
                        <input type="date" class="form-control form-control-sm" id="filterDateTo">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            @foreach ($bookingStatuses as $status)
                                <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                            @endforeach
                            <option value="checked_in">Checked In</option>
                            <option value="boarded">Boarded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Payment Status</label>
                        <select class="form-select form-select-sm" id="filterPaymentStatus">
                            <option value="">All Payments</option>
                            @foreach ($paymentStatuses as $status)
                                <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                            @endforeach
                            <option value="partial">Partial</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Channel</label>
                        <select class="form-select form-select-sm" id="filterChannel">
                            <option value="">All Channels</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel->value }}">{{ $channel->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Route</label>
                        <select class="form-select form-select-sm" id="filterRoute">
                            <option value="">All Routes</option>
                            @foreach ($routes as $route)
                                <option value="{{ $route->id }}">
                                    {{ $route->name }}{{ $route->code ? ' (' . $route->code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">User (Booked By)</label>
                        <select class="form-select form-select-sm" id="filterUser">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}{{ $user->email ? ' (' . $user->email . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Payment Method</label>
                        <select class="form-select form-select-sm" id="filterPaymentMethod">
                            <option value="">All Methods</option>
                            @foreach ($paymentMethods as $method)
                                @if($method['value'] !== 'other')
                                    <option value="{{ $method['value'] }}">{{ $method['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Booking Number</label>
                        <input type="text" class="form-control form-control-sm" id="filterBookingNumber"
                            placeholder="e.g., 000123">
                    </div>
                    <div class="col-md-12 d-flex align-items-end gap-2">
                        <button class="btn btn-primary btn-sm" onclick="reloadTable()">
                            <i class="bx bx-search"></i> Apply Filters
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
                            <i class="bx bx-refresh"></i> Reset
                        </button>
                        <button class="btn btn-success btn-sm" onclick="exportReport()">
                            <i class="bx bx-export"></i> Export
                        </button>
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
                            <th><i class="bx bx-chair"></i> Seats</th>
                            <th><i class="bx bx-group"></i> Passengers</th>
                            <th><i class="bx bx-money"></i> Amount</th>
                            <th><i class="bx bx-store"></i> Channel</th>
                            <th><i class="bx bx-check-circle"></i> Status</th>
                            <th><i class="bx bx-credit-card"></i> Payment</th>
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

        function handleQuickDateRange() {
            const quickRange = document.getElementById('quickDateRange').value;
            if (quickRange) {
                setDateRange(quickRange);
            }
        }

        function setDateRange(range) {
            const today = new Date();
            const dateFrom = document.getElementById('filterDateFrom');
            const dateTo = document.getElementById('filterDateTo');
            
            let fromDate, toDate;
            
            switch(range) {
                case 'today':
                    fromDate = today;
                    toDate = today;
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    fromDate = yesterday;
                    toDate = yesterday;
                    break;
                case 'this_week':
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    fromDate = startOfWeek;
                    toDate = today;
                    break;
                case 'last_week':
                    const lastWeekStart = new Date(today);
                    lastWeekStart.setDate(today.getDate() - today.getDay() - 7);
                    const lastWeekEnd = new Date(lastWeekStart);
                    lastWeekEnd.setDate(lastWeekStart.getDate() + 6);
                    fromDate = lastWeekStart;
                    toDate = lastWeekEnd;
                    break;
                case 'this_month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    toDate = today;
                    break;
                case 'last_month':
                    const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                    fromDate = lastMonthStart;
                    toDate = lastMonthEnd;
                    break;
                case 'this_year':
                    fromDate = new Date(today.getFullYear(), 0, 1);
                    toDate = today;
                    break;
                default:
                    return;
            }
            
            dateFrom.value = fromDate.toISOString().split('T')[0];
            dateTo.value = toDate.toISOString().split('T')[0];
            // Reset quick date range dropdown after setting dates
            document.getElementById('quickDateRange').value = '';
            reloadTable();
        }

        function exportReport() {
            Swal.fire({
                icon: 'info',
                title: 'Export Report',
                text: 'Export functionality will be available soon.',
                confirmButtonColor: '#3085d6'
            });
        }

        function initializeDataTable() {
            bookingsTable = $('#bookingsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.bookings.data') }}',
                    data: function(d) {
                        d.date_from = document.getElementById('filterDateFrom').value;
                        d.date_to = document.getElementById('filterDateTo').value;
                        d.status = document.getElementById('filterStatus').value;
                        d.payment_status = document.getElementById('filterPaymentStatus').value;
                        d.channel = document.getElementById('filterChannel').value;
                        d.booking_number = document.getElementById('filterBookingNumber').value;
                        d.terminal_id = document.getElementById('filterTerminal').value;
                        d.from_terminal_id = document.getElementById('filterFromTerminal').value;
                        d.to_terminal_id = document.getElementById('filterToTerminal').value;
                        d.route_id = document.getElementById('filterRoute').value;
                        d.user_id = document.getElementById('filterUser').value;
                        d.payment_method = document.getElementById('filterPaymentMethod').value;
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
                        data: 'seats',
                        name: 'seats'
                    },
                    {
                        data: 'passengers_count',
                        name: 'passengers_count'
                    },
                    {
                        data: 'amount',
                        name: 'final_amount'
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
                        data: 'payment_status',
                        name: 'payment_status'
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
                dom: 'lBfrtip',
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
                document.getElementById('filterStatus').value = '';
                document.getElementById('filterPaymentStatus').value = '';
                document.getElementById('filterChannel').value = '';
                document.getElementById('filterBookingNumber').value = '';
                document.getElementById('filterTerminal').value = '';
                document.getElementById('filterFromTerminal').value = '';
                document.getElementById('filterToTerminal').value = '';
                document.getElementById('filterRoute').value = '';
                document.getElementById('filterUser').value = '';
                document.getElementById('filterPaymentMethod').value = '';
                document.getElementById('quickDateRange').value = '';
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

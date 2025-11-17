@extends('admin.layouts.app')

@section('title', 'Terminal Reports')

@section('content')
<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Reports</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Terminal Reports</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container-fluid">
    <!-- Filter Card -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-filter-alt text-primary"></i> Report Filters
                </h6>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true">
                    <i class="bx bx-chevron-down"></i> Toggle Filters
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Date Range & Terminal Section -->
                    <div class="col-lg-12 mb-3">
                        <h6 class="text-muted mb-3 fw-semibold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-calendar"></i> Date Range & Terminal
                        </h6>
                        <div class="row g-2">
                            @if ($canSelectTerminal)
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">
                                        <i class="bx bx-building"></i> Terminal <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-sm" id="terminalSelect" required>
                                        <option value="">-- Select Terminal --</option>
                                        @foreach ($terminals as $terminal)
                                            <option value="{{ $terminal->id }}">{{ $terminal->name }}{{ $terminal->code ? ' (' . $terminal->code . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">
                                        <i class="bx bx-building"></i> Terminal
                                    </label>
                                    @php
                                        $terminal = $terminals->first();
                                    @endphp
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ ($terminal ? $terminal->name . ($terminal->code ? ' (' . $terminal->code . ')' : '') : 'N/A') }}"
                                        readonly>
                                    <input type="hidden" id="terminalSelect" value="{{ $terminal->id ?? '' }}">
                                </div>
                            @endif

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">
                                    <i class="bx bx-route"></i> Route
                                </label>
                                <select class="form-select form-select-sm" id="filterRoute">
                                    <option value="">All Routes</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">
                                    <i class="bx bx-calendar"></i> Start Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-sm" id="startDate"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">
                                    <i class="bx bx-calendar"></i> End Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-sm" id="endDate" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">
                                    <i class="bx bx-time"></i> Start Time (Shift Start)
                                </label>
                                <input type="time" class="form-control form-control-sm" id="startTime">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">
                                    <i class="bx bx-time"></i> End Time (Shift End)
                                </label>
                                <input type="time" class="form-control form-control-sm" id="endTime" value="{{ date('H:i') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">
                                    <i class="bx bx-user"></i> User (Booked By)
                                </label>
                                <select class="form-select form-select-sm" id="filterUser">
                                    @if ($canViewAllReports)
                                        <option value="">All Users</option>
                                    @endif
                                    @foreach ($users as $reportUser)
                                        <option value="{{ $reportUser->id }}" {{ isset($selectedUserId) && (string) $selectedUserId === (string) $reportUser->id ? 'selected' : '' }}>
                                            {{ $reportUser->name }}{{ $reportUser->email ? ' (' . $reportUser->email . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Payment Filters -->
                    <div class="col-lg-12 mb-3">
                        <h6 class="text-muted mb-3 fw-semibold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bx bx-filter"></i> Status & Payment Filters
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Booking Status</label>
                                <select class="form-select form-select-sm" id="filterStatus">
                                    <option value="">All Status</option>
                                    @foreach ($bookingStatuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Payment Status</label>
                                <select class="form-select form-select-sm" id="filterPaymentStatus">
                                    <option value="">All Payments</option>
                                    @foreach ($paymentStatuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Channel</label>
                                <select class="form-select form-select-sm" id="filterChannel">
                                    <option value="">All Channels</option>
                                    @foreach ($channels as $channel)
                                        <option value="{{ $channel->value }}">{{ $channel->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
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
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="loadReport()">
                                <i class="bx bx-search"></i> Generate Report
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

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Generating report...</p>
    </div>

    <!-- Report Content -->
    <div id="reportContent" style="display: none;">

        <!-- Bookings Table with DataTable -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bx bx-list-ul text-primary"></i> Bookings from This Terminal
                    </h6>
                    <button class="btn btn-success btn-sm" onclick="exportReport()">
                        <i class="bx bx-download"></i> Export Report
                    </button>
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
                                <th><i class="bx bx-credit-card"></i> Payment Method</th>
                                <th><i class="bx bx-credit-card"></i> Payment Status</th>
                                <th><i class="bx bx-money"></i> Amount</th>
                                <th><i class="bx bx-user"></i> Booked By</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cash Summary Table -->
        <div class="card shadow-sm mb-4 border-0 border-start border-4 border-success">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-money text-success"></i> Daily Cash Summary Report
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="cashSummaryTable">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center" style="width: 20%;">Employee / Terminal</th>
                                <th class="text-center" style="width: 20%;">Total Sales (PKR)</th>
                                <th class="text-center" style="width: 20%;">Cash in Hand (PKR)</th>
                                <th class="text-center" style="width: 20%;">Total Expenses (PKR)</th>
                                <th class="text-center" style="width: 20%;">Net Balance / Remaining Cash (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold" id="summaryEmployeeName">-</td>
                                <td class="text-end fw-bold text-primary" id="summaryTotalSales">PKR 0</td>
                                <td class="text-end fw-bold text-success" id="summaryCashInHand">PKR 0</td>
                                <td class="text-end fw-bold text-danger" id="summaryTotalExpenses">PKR 0</td>
                                <td class="text-end fw-bold text-info" id="summaryNetBalance">PKR 0</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td class="fw-bold">Summary:</td>
                                <td class="text-end fw-bold" id="footerTotalSales">PKR 0</td>
                                <td class="text-end fw-bold" id="footerCashInHand">PKR 0</td>
                                <td class="text-end fw-bold" id="footerTotalExpenses">PKR 0</td>
                                <td class="text-end fw-bold" id="footerNetBalance">PKR 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-body bg-light border-top">
                    <div class="row g-2">
                        <div class="col-md-12">
                            <small class="text-muted">
                                <strong>Note:</strong> Net Balance / Remaining Cash = Cash in Hand - Total Expenses. This is the amount that should be handed over to the company.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bx bx-receipt text-warning"></i> Expenses for This Terminal
                    </h6>
                    <span class="badge bg-light text-dark" id="expensesTableCount">0 records</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover table-striped table-sm mb-0" id="expensesTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="small">Date</th>
                                <th class="small">Expense Type</th>
                                <th class="small">From → To Terminal</th>
                                <th class="small text-end">Amount (PKR)</th>
                                <th class="small">Description</th>
                                <th class="small">Trip</th>
                                <th class="small">Added By</th>
                                <th class="small">Created At</th>
                            </tr>
                        </thead>
                        <tbody id="expensesTableBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3 small">
                                    No data available. Please generate a report.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="3" class="text-end small">Total Expenses:</td>
                                <td class="text-end text-danger small" id="expensesTableTotal">PKR 0.00</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Methods & Channels Breakdown -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-credit-card text-info"></i> Payment Methods Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="paymentMethodsBreakdown">
                            <p class="text-muted text-center mb-0 small">No data available</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-store text-success"></i> Booking Channels Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="channelsBreakdown">
                            <p class="text-muted text-center mb-0 small">No data available</p>
                        </div>
                    </div>
                </div>
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

        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-light-info {
            background-color: rgba(13, 202, 240, 0.1);
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

        .card {
            border-radius: 8px;
        }

        .table thead th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                font-size: 12px;
            }

            .card {
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .table {
                font-size: 11px;
            }

            .table th,
            .table td {
                padding: 4px 8px;
            }

            .bg-primary,
            .bg-success,
            .bg-warning,
            .bg-info,
            .bg-dark {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }

        /* Table enhancements */
        .table-sm th,
        .table-sm td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }

        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Cash Summary Table Styling */
        #cashSummaryTable {
            font-size: 0.9rem;
        }

        #cashSummaryTable thead th {
            background-color: #198754 !important;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }

        #cashSummaryTable tbody td {
            font-size: 1rem;
            padding: 1rem;
            vertical-align: middle;
        }

        #cashSummaryTable tfoot td {
            font-size: 1.1rem;
            padding: 1rem;
            background-color: #e9ecef;
            font-weight: bold;
        }

        #cashSummaryTable tbody tr {
            background-color: #ffffff;
        }

        #cashSummaryTable tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Load routes when terminal is selected
        $('#terminalSelect').on('change', function() {
            const terminalId = $(this).val();
            const routeSelect = $('#filterRoute');
            
            routeSelect.html('<option value="">All Routes</option>');
            
            if (terminalId) {
                $.ajax({
                    url: "{{ route('admin.terminal-reports.routes') }}",
                    type: 'GET',
                    data: { terminal_id: terminalId },
                    success: function(response) {
                        if (response.success && response.routes.length > 0) {
                            response.routes.forEach(function(route) {
                                routeSelect.append(
                                    $('<option></option>')
                                        .attr('value', route.id)
                                        .text(route.name + (route.code ? ' (' + route.code + ')' : ''))
                                );
                            });
                        }
                    },
                    error: function() {
                        console.error('Failed to load routes');
                    }
                });
            }
        });

        // Auto-load routes if terminal is pre-selected
        @if (!$canSelectTerminal && $terminals->isNotEmpty())
            $(document).ready(function() {
                const terminalId = $('#terminalSelect').val();
                if (terminalId) {
                    $('#terminalSelect').trigger('change');
                }
            });
        @endif

        function loadReport() {
            const terminalId = document.getElementById('terminalSelect').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!terminalId || !startDate || !endDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please select all required fields: Terminal, Start Date, and End Date.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'Start date cannot be greater than end date.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Show loading indicator
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('reportContent').style.display = 'none';

            $.ajax({
                url: "{{ route('admin.terminal-reports.data') }}",
                type: 'GET',
                data: {
                    terminal_id: terminalId,
                    start_date: startDate,
                    end_date: endDate,
                    start_time: document.getElementById('startTime').value || null,
                    end_time: document.getElementById('endTime').value || null,
                    route_id: document.getElementById('filterRoute').value || null,
                    user_id: document.getElementById('filterUser').value || null
                },
                success: function(response) {
                    if (response.success) {
                        renderReport(response);
                        document.getElementById('reportContent').style.display = 'block';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Load Report',
                            text: response.error || 'Unable to generate report. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function(error) {
                    const message = error.responseJSON?.error ||
                        'Unable to generate report. Please check your connection and try again.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Report',
                        text: message,
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function() {
                    document.getElementById('loadingIndicator').style.display = 'none';
                }
            });
        }

        let bookingsTable = null;

        function renderReport(data) {
            const stats = data.stats;
            const summary = data.summary || {};

            // Update Cash Summary Table
            const cashInHand = parseFloat(summary.cash_in_hand || stats.cash?.cash_in_hand || 0) || 0;
            const totalExpenses = parseFloat(summary.total_expenses || stats.expenses?.total_expenses || 0) || 0;
            const netBalance = parseFloat(summary.net_balance || stats.cash?.net_balance || 0) || 0;
            const totalSales = parseFloat(stats.revenue.total_revenue) || 0;

            // Get employee/terminal name
            const selectedUserId = document.getElementById('filterUser').value;
            let employeeName = 'All Employees';
            if (selectedUserId) {
                const selectedOption = document.getElementById('filterUser').options[document.getElementById('filterUser').selectedIndex];
                employeeName = selectedOption ? selectedOption.text.split(' (')[0] : 'Selected Employee';
            } else if (data.terminal) {
                employeeName = data.terminal.name + ' (' + data.terminal.code + ')';
            }

            // Update cash summary table
            document.getElementById('summaryEmployeeName').textContent = employeeName;
            document.getElementById('summaryTotalSales').textContent = 'PKR ' + totalSales.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            document.getElementById('summaryCashInHand').textContent = 'PKR ' + cashInHand.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            document.getElementById('summaryTotalExpenses').textContent = 'PKR ' + totalExpenses.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            document.getElementById('summaryNetBalance').textContent = 'PKR ' + netBalance.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            // Update footer (same values for now, can be expanded for multiple employees)
            document.getElementById('footerTotalSales').textContent = 'PKR ' + totalSales.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            document.getElementById('footerCashInHand').textContent = 'PKR ' + cashInHand.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            document.getElementById('footerTotalExpenses').textContent = 'PKR ' + totalExpenses.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            document.getElementById('footerNetBalance').textContent = 'PKR ' + netBalance.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            // Initialize DataTable for bookings
            initializeBookingsTable();

            // Render expenses table (detailed)
            const expensesBody = document.getElementById('expensesTableBody');
            document.getElementById('expensesTableCount').textContent = data.expenses.length;

            if (data.expenses.length === 0) {
                expensesBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No expenses found for this period.</td>
                    </tr>
                `;
                document.getElementById('expensesTableTotal').textContent = 'PKR 0';
            } else {
                let expensesHtml = '';
                let totalExpensesCalc = 0;
                data.expenses.forEach(expense => {
                    const amount = parseFloat(expense.amount) || 0;
                    totalExpensesCalc += amount;

                    expensesHtml += `
                        <tr>
                            <td><small>${expense.expense_date}</small></td>
                            <td><span class="badge bg-info">${expense.expense_type}</span></td>
                            <td><strong>${expense.from_terminal} → ${expense.to_terminal}</strong></td>
                            <td class="text-end text-danger fw-bold">PKR ${amount.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}</td>
                            <td><small>${expense.description || '-'}</small></td>
                            <td><small>${expense.trip_id ? 'Trip #' + expense.trip_id : 'N/A'}</small></td>
                            <td><small>${expense.user || 'N/A'}</small></td>
                            <td><small>${expense.created_at || '-'}</small></td>
                        </tr>
                    `;
                });
                expensesBody.innerHTML = expensesHtml;
                document.getElementById('expensesTableTotal').textContent =
                    'PKR ' + totalExpensesCalc.toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
            }

            // Render payment methods breakdown
            const paymentMethodsDiv = document.getElementById('paymentMethodsBreakdown');
            if (Object.keys(stats.payment_methods).length === 0) {
                paymentMethodsDiv.innerHTML = '<p class="text-muted text-center mb-0">No payment method data available</p>';
            } else {
                let paymentHtml = '';
                Object.entries(stats.payment_methods).forEach(([method, data]) => {
                    const methodLabel = method.charAt(0).toUpperCase() + method.slice(1).replace('_', ' ');
                    paymentHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <strong>${methodLabel}</strong>
                                <small class="d-block text-muted">${data.count} bookings</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">PKR ${parseFloat(data.amount).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}</strong>
                            </div>
                        </div>
                    `;
                });
                paymentMethodsDiv.innerHTML = paymentHtml;
            }

            // Render channels breakdown
            const channelsDiv = document.getElementById('channelsBreakdown');
            if (Object.keys(stats.channels).length === 0) {
                channelsDiv.innerHTML = '<p class="text-muted text-center mb-0">No channel data available</p>';
            } else {
                let channelsHtml = '';
                Object.entries(stats.channels).forEach(([channel, data]) => {
                    const channelLabel = channel.charAt(0).toUpperCase() + channel.slice(1);
                    channelsHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <strong>${channelLabel}</strong>
                                <small class="d-block text-muted">${data.count} bookings</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">PKR ${parseFloat(data.amount).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}</strong>
                            </div>
                        </div>
                    `;
                });
                channelsDiv.innerHTML = channelsHtml;
            }
        }


        function initializeBookingsTable() {
            // Destroy existing table if it exists
            if (bookingsTable) {
                bookingsTable.destroy();
                bookingsTable = null;
            }

            const terminalId = document.getElementById('terminalSelect').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!terminalId || !startDate || !endDate) {
                return;
            }

            bookingsTable = $('#bookingsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.terminal-reports.bookings-data') }}',
                    data: function(d) {
                        d.terminal_id = terminalId;
                        d.start_date = startDate;
                        d.end_date = endDate;
                        d.start_time = document.getElementById('startTime').value || null;
                        d.end_time = document.getElementById('endTime').value || null;
                        d.route_id = document.getElementById('filterRoute').value;
                        d.user_id = document.getElementById('filterUser').value;
                        d.status = document.getElementById('filterStatus').value;
                        d.payment_status = document.getElementById('filterPaymentStatus').value;
                        d.payment_method = document.getElementById('filterPaymentMethod').value;
                        d.channel = document.getElementById('filterChannel').value;
                        d.is_advance = document.getElementById('filterAdvance').value;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', error, thrown);
                        Swal.fire({
                            icon: 'error',
                            title: 'Loading Error',
                            text: 'Failed to load bookings data. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                columns: [
                    { data: 'booking_number', name: 'booking_number' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'route', name: 'route' },
                    { data: 'passengers', name: 'passengers', orderable: false, searchable: false },
                    { data: 'seats', name: 'seats', orderable: false, searchable: false },
                    { data: 'channel', name: 'channel' },
                    { data: 'status', name: 'status' },
                    { data: 'booking_type', name: 'booking_type', orderable: false, searchable: false },
                    { data: 'is_advance', name: 'is_advance', orderable: false, searchable: false },
                    { data: 'payment_method', name: 'payment_method' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'amount', name: 'final_amount' },
                    { data: 'booked_by', name: 'booked_by', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search bookings...',
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: 'No bookings found',
                    zeroRecords: 'No matching bookings found'
                },
                dom: 'lfrtip',
            });

            // Add event listeners for filter changes - reload DataTable only, not full report
            $('#filterRoute, #filterStatus, #filterPaymentStatus, #filterPaymentMethod, #filterChannel, #filterAdvance').on('change', function() {
                if (bookingsTable) {
                    bookingsTable.ajax.reload(null, false);
                }
            });

            // Reload full report when user filter changes (to update cash summary)
            $('#filterUser').on('change', function() {
                if (bookingsTable) {
                    bookingsTable.ajax.reload(null, false);
                    // Reload summary data when user filter changes
                    const terminalId = document.getElementById('terminalSelect').value;
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;
                    
                    if (terminalId && startDate && endDate) {
                        $.ajax({
                            url: "{{ route('admin.terminal-reports.data') }}",
                            type: 'GET',
                            data: {
                                terminal_id: terminalId,
                                start_date: startDate,
                                end_date: endDate,
                                user_id: document.getElementById('filterUser').value || null
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Update only the cash summary table
                                    const summary = response.summary || {};
                                    const stats = response.stats;
                                    const cashInHand = parseFloat(summary.cash_in_hand || stats.cash?.cash_in_hand || 0) || 0;
                                    const totalExpenses = parseFloat(summary.total_expenses || stats.expenses?.total_expenses || 0) || 0;
                                    const netBalance = parseFloat(summary.net_balance || stats.cash?.net_balance || 0) || 0;
                                    const totalSales = parseFloat(stats.revenue.total_revenue) || 0;

                                    const selectedUserId = document.getElementById('filterUser').value;
                                    let employeeName = 'All Employees';
                                    if (selectedUserId) {
                                        const selectedOption = document.getElementById('filterUser').options[document.getElementById('filterUser').selectedIndex];
                                        employeeName = selectedOption ? selectedOption.text.split(' (')[0] : 'Selected Employee';
                                    } else if (response.terminal) {
                                        employeeName = response.terminal.name + ' (' + response.terminal.code + ')';
                                    }

                                    document.getElementById('summaryEmployeeName').textContent = employeeName;
                                    document.getElementById('summaryTotalSales').textContent = 'PKR ' + totalSales.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                    document.getElementById('summaryCashInHand').textContent = 'PKR ' + cashInHand.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                    document.getElementById('summaryTotalExpenses').textContent = 'PKR ' + totalExpenses.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                    document.getElementById('summaryNetBalance').textContent = 'PKR ' + netBalance.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});

                                    document.getElementById('footerTotalSales').textContent = 'PKR ' + totalSales.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                    document.getElementById('footerCashInHand').textContent = 'PKR ' + cashInHand.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                    document.getElementById('footerTotalExpenses').textContent = 'PKR ' + totalExpenses.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                    document.getElementById('footerNetBalance').textContent = 'PKR ' + netBalance.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                }
                            }
                        });
                    }
                }
            });
        }

        function resetFilters() {
            document.getElementById('filterRoute').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterPaymentStatus').value = '';
            document.getElementById('filterPaymentMethod').value = '';
            document.getElementById('filterChannel').value = '';
            document.getElementById('filterAdvance').value = '';
            document.getElementById('filterUser').value = '';
            document.getElementById('startTime').value = '';
            document.getElementById('endTime').value = '';
            
            if (bookingsTable) {
                bookingsTable.ajax.reload(null, false);
            }
        }


        function exportReport() {
            const terminalId = document.getElementById('terminalSelect').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!terminalId || !startDate || !endDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please select all required fields: Terminal, Start Date, and End Date.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'Start date cannot be greater than end date.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Build query string with all filters
            const params = new URLSearchParams({
                terminal_id: terminalId,
                start_date: startDate,
                end_date: endDate,
                start_time: document.getElementById('startTime').value || '',
                end_time: document.getElementById('endTime').value || '',
                route_id: document.getElementById('filterRoute').value || '',
                user_id: document.getElementById('filterUser').value || '',
                status: document.getElementById('filterStatus').value || '',
                payment_status: document.getElementById('filterPaymentStatus').value || '',
                payment_method: document.getElementById('filterPaymentMethod').value || '',
                channel: document.getElementById('filterChannel').value || '',
                is_advance: document.getElementById('filterAdvance').value || ''
            });

            // Open export in new window
            window.open("{{ route('admin.terminal-reports.export') }}?" + params.toString(), '_blank');
        }

        // Auto-load report for users with assigned terminal (their terminal)
        @if (!$canSelectTerminal && $terminals->isNotEmpty())
            document.addEventListener('DOMContentLoaded', function() {
                loadReport();
            });
        @endif
    </script>
@endsection

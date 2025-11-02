@extends('admin.layouts.app')

@section('title', 'Terminal Reports')

@section('content')
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Terminal Reports
                    </h5>
                    @if ($isAdmin)
                        <span class="badge bg-info">Admin View - All Terminals</span>
                    @else
                        <span class="badge bg-warning">Employee View - My Terminal Only</span>
                    @endif
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card-body bg-light">
                <div class="row g-3">
                    @if ($isAdmin)
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-building"></i> Terminal <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="terminalSelect" required>
                                <option value="">-- Select Terminal --</option>
                                @foreach ($terminals as $terminal)
                                    <option value="{{ $terminal->id }}">{{ $terminal->name }} ({{ $terminal->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-building"></i> Terminal
                            </label>
                            <input type="text" class="form-control" value="{{ $terminals->first()->name ?? 'N/A' }} ({{ $terminals->first()->code ?? 'N/A' }})"
                                readonly>
                            <input type="hidden" id="terminalSelect" value="{{ $terminals->first()->id ?? '' }}">
                        </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar-alt"></i> Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="startDate" value="{{ date('Y-m-d', strtotime('-30 days')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar-alt"></i> End Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="endDate" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary flex-grow-1" onclick="loadReport()">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        <button class="btn btn-secondary no-print" onclick="printReport()" id="printBtn" disabled>
                            <i class="fas fa-print"></i> Print
                        </button>
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
            <!-- Summary Statistics -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="opacity-75">Total Revenue</small>
                                    <h4 class="mb-0 fw-bold" id="totalRevenue">PKR 0.00</h4>
                                </div>
                                <div style="font-size: 2.5rem; opacity: 0.5;">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-gradient-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="opacity-75">Total Bookings</small>
                                    <h4 class="mb-0 fw-bold" id="totalBookings">0</h4>
                                </div>
                                <div style="font-size: 2.5rem; opacity: 0.5;">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-gradient-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="opacity-75">Total Expenses</small>
                                    <h4 class="mb-0 fw-bold" id="totalExpenses">PKR 0.00</h4>
                                </div>
                                <div style="font-size: 2.5rem; opacity: 0.5;">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-gradient-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="opacity-75">Net Profit</small>
                                    <h4 class="mb-0 fw-bold" id="netProfit">PKR 0.00</h4>
                                </div>
                                <div style="font-size: 2.5rem; opacity: 0.5;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-pie"></i> Booking Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Confirmed</small>
                                    <h5 class="mb-0 text-success" id="confirmedBookings">0</h5>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">On Hold</small>
                                    <h5 class="mb-0 text-warning" id="holdBookings">0</h5>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Cancelled</small>
                                    <h5 class="mb-0 text-danger" id="cancelledBookings">0</h5>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Total Trips</small>
                                    <h5 class="mb-0 text-primary" id="totalTrips">0</h5>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Total Passengers</small>
                                            <h5 class="mb-0" id="totalPassengers">0</h5>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Total Seats</small>
                                            <h5 class="mb-0" id="totalSeats">0</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-money-bill"></i> Revenue Breakdown
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted d-block">Total Fare</small>
                                    <h5 class="mb-0" id="totalFare">PKR 0.00</h5>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Total Discount</small>
                                    <h5 class="mb-0 text-danger" id="totalDiscount">-PKR 0.00</h5>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Total Tax/Charge</small>
                                    <h5 class="mb-0 text-info" id="totalTax">+PKR 0.00</h5>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Final Revenue:</strong>
                                        <h5 class="mb-0 text-success" id="finalRevenue">PKR 0.00</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Terminal Report Summary -->
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-file-invoice-dollar"></i> Terminal Financial Summary
                        </h6>
                        <div id="reportTerminalInfo" class="text-white-50 small"></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1">Total Sales (Bookings)</small>
                                <h4 class="mb-0 text-success fw-bold" id="summaryTotalSales">PKR 0.00</h4>
                                <small class="text-muted" id="summaryBookingsCount">0 bookings</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1">Total Expenses</small>
                                <h4 class="mb-0 text-danger fw-bold" id="summaryTotalExpenses">PKR 0.00</h4>
                                <small class="text-muted" id="summaryExpensesCount">0 expenses</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border border-primary border-3">
                                <small class="text-muted d-block mb-1">Net Amount (Remaining)</small>
                                <h4 class="mb-0 text-primary fw-bold" id="summaryNetAmount">PKR 0.00</h4>
                                <small class="text-muted" id="summaryProfitMargin">0% margin</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i> Bookings from This Terminal
                        <span class="badge bg-light text-dark ms-2" id="bookingsTableCount">0</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover table-striped table-sm mb-0" id="bookingsTable">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 100px;">Booking #</th>
                                    <th style="width: 120px;">Date & Time</th>
                                    <th style="width: 150px;">From → To</th>
                                    <th style="width: 80px;">Channel</th>
                                    <th style="width: 90px;">Status</th>
                                    <th style="width: 100px;">Payment Method</th>
                                    <th style="width: 100px;">Payment Status</th>
                                    <th style="width: 110px;" class="text-end">Fare</th>
                                    <th style="width: 110px;" class="text-end">Discount</th>
                                    <th style="width: 100px;" class="text-end">Tax</th>
                                    <th style="width: 120px;" class="text-end">Final Amount</th>
                                    <th style="width: 100px;">Passengers</th>
                                    <th style="width: 120px;">Booked By</th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody">
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-3">
                                        No data available. Please generate a report.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expenses Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-receipt"></i> Expenses for This Terminal
                        <span class="badge bg-dark ms-2" id="expensesTableCount">0</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover table-striped table-sm mb-0" id="expensesTable">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 110px;">Date</th>
                                    <th style="width: 120px;">Expense Type</th>
                                    <th style="width: 150px;">From → To Terminal</th>
                                    <th style="width: 120px;" class="text-end">Amount (PKR)</th>
                                    <th style="width: 200px;">Description</th>
                                    <th style="width: 150px;">Trip</th>
                                    <th style="width: 120px;">Added By</th>
                                    <th style="width: 130px;">Created At</th>
                                </tr>
                            </thead>
                            <tbody id="expensesTableBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">
                                        No data available. Please generate a report.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Total Expenses:</td>
                                    <td class="text-end text-danger" id="expensesTableTotal">PKR 0.00</td>
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
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-credit-card"></i> Payment Methods
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="paymentMethodsBreakdown">
                                <p class="text-muted text-center mb-0">No data available</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-sitemap"></i> Booking Channels
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="channelsBreakdown">
                                <p class="text-muted text-center mb-0">No data available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
    </style>
@endsection

@section('scripts')
    <script>
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
            document.getElementById('printBtn').disabled = true;

            $.ajax({
                url: "{{ route('admin.terminal-reports.data') }}",
                type: 'GET',
                data: {
                    terminal_id: terminalId,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    if (response.success) {
                        renderReport(response);
                        document.getElementById('reportContent').style.display = 'block';
                        document.getElementById('printBtn').disabled = false;
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
                    const message = error.responseJSON?.error || 'Unable to generate report. Please check your connection and try again.';
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

        function renderReport(data) {
            const stats = data.stats;

            // Update terminal info
            if (data.terminal) {
                document.getElementById('reportTerminalInfo').textContent = 
                    `${data.terminal.name} (${data.terminal.code}) | ${data.date_range.start} to ${data.date_range.end}`;
            }

            // Update financial summary section
            const totalSales = parseFloat(stats.revenue.total_revenue) || 0;
            const totalExpenses = parseFloat(stats.expenses.total_expenses) || 0;
            const netAmount = totalSales - totalExpenses;
            const profitMargin = totalSales > 0 ? ((netAmount / totalSales) * 100).toFixed(2) : 0;

            document.getElementById('summaryTotalSales').textContent = 'PKR ' + totalSales.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('summaryBookingsCount').textContent = `${stats.bookings.total} bookings`;

            document.getElementById('summaryTotalExpenses').textContent = 'PKR ' + totalExpenses.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('summaryExpensesCount').textContent = `${data.expenses.length} expenses`;

            document.getElementById('summaryNetAmount').textContent = 'PKR ' + netAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('summaryProfitMargin').textContent = `${profitMargin}% margin`;

            // Update summary cards (existing)
            document.getElementById('totalRevenue').textContent = 'PKR ' + totalSales.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('totalBookings').textContent = stats.bookings.total;
            document.getElementById('totalExpenses').textContent = 'PKR ' + totalExpenses.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('netProfit').textContent = 'PKR ' + netAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Update booking statistics
            document.getElementById('confirmedBookings').textContent = stats.bookings.confirmed;
            document.getElementById('holdBookings').textContent = stats.bookings.hold;
            document.getElementById('cancelledBookings').textContent = stats.bookings.cancelled;
            document.getElementById('totalTrips').textContent = stats.trips.total_trips;
            document.getElementById('totalPassengers').textContent = stats.passengers.total_passengers;
            document.getElementById('totalSeats').textContent = stats.passengers.total_seats;

            // Update revenue breakdown
            document.getElementById('totalFare').textContent = 'PKR ' + parseFloat(stats.revenue.total_fare).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('totalDiscount').textContent = '-PKR ' + parseFloat(stats.revenue.total_discount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('totalTax').textContent = '+PKR ' + parseFloat(stats.revenue.total_tax).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('finalRevenue').textContent = 'PKR ' + parseFloat(stats.revenue.total_revenue).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Render bookings table (detailed)
            const bookingsBody = document.getElementById('bookingsTableBody');
            document.getElementById('bookingsTableCount').textContent = data.bookings.length;
            
            if (data.bookings.length === 0) {
                bookingsBody.innerHTML = `
                    <tr>
                        <td colspan="13" class="text-center text-muted py-3">No bookings found for this period.</td>
                    </tr>
                `;
            } else {
                let bookingsHtml = '';
                let totalSalesCalc = 0;
                data.bookings.forEach(booking => {
                    const statusBadge = booking.status === 'confirmed' ? 'bg-success' :
                        booking.status === 'hold' ? 'bg-warning' :
                        booking.status === 'cancelled' ? 'bg-danger' : 'bg-secondary';
                    const paymentBadge = booking.payment_status === 'paid' ? 'bg-success' :
                        booking.payment_status === 'unpaid' ? 'bg-danger' :
                        booking.payment_status === 'partial' ? 'bg-warning' : 'bg-secondary';
                    const channelBadge = booking.channel === 'counter' ? 'bg-info' :
                        booking.channel === 'phone' ? 'bg-warning' :
                        booking.channel === 'online' ? 'bg-success' : 'bg-secondary';
                    
                    const finalAmount = parseFloat(booking.final_amount) || 0;
                    totalSalesCalc += finalAmount;

                    bookingsHtml += `
                        <tr>
                            <td><span class="badge bg-primary">#${booking.booking_number}</span></td>
                            <td><small>${booking.created_at}</small></td>
                            <td><strong>${booking.from_terminal} → ${booking.to_terminal}</strong></td>
                            <td><span class="badge ${channelBadge}">${booking.channel}</span></td>
                            <td><span class="badge ${statusBadge}">${booking.status}</span></td>
                            <td><small>${booking.payment_method || 'N/A'}</small></td>
                            <td><span class="badge ${paymentBadge}">${booking.payment_status}</span></td>
                            <td class="text-end">${(parseFloat(booking.total_fare) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end text-danger">-${(parseFloat(booking.discount_amount) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end text-info">+${(parseFloat(booking.tax_amount) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end fw-bold text-success">PKR ${finalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td><span class="badge bg-info">${booking.passengers_count || 0} pax</span></td>
                            <td><small>${booking.user || 'N/A'}</small></td>
                        </tr>
                    `;
                });
                // Add totals row
                bookingsHtml += `
                    <tr class="table-secondary fw-bold">
                        <td colspan="7" class="text-end">Total Sales:</td>
                        <td class="text-end">${stats.revenue.total_fare.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end text-danger">-${stats.revenue.total_discount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end text-info">+${stats.revenue.total_tax.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end text-success">PKR ${totalSalesCalc.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td colspan="2"></td>
                    </tr>
                `;
                bookingsBody.innerHTML = bookingsHtml;
            }

            // Render expenses table (detailed)
            const expensesBody = document.getElementById('expensesTableBody');
            document.getElementById('expensesTableCount').textContent = data.expenses.length;
            
            if (data.expenses.length === 0) {
                expensesBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No expenses found for this period.</td>
                    </tr>
                `;
                document.getElementById('expensesTableTotal').textContent = 'PKR 0.00';
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
                            <td class="text-end text-danger fw-bold">PKR ${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td><small>${expense.description || '-'}</small></td>
                            <td><small>${expense.trip_id ? 'Trip #' + expense.trip_id : 'N/A'}</small></td>
                            <td><small>${expense.user || 'N/A'}</small></td>
                            <td><small>${expense.created_at || '-'}</small></td>
                        </tr>
                    `;
                });
                expensesBody.innerHTML = expensesHtml;
                document.getElementById('expensesTableTotal').textContent = 
                    'PKR ' + totalExpensesCalc.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
                                <strong class="text-success">PKR ${parseFloat(data.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
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
                                <strong class="text-success">PKR ${parseFloat(data.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                            </div>
                        </div>
                    `;
                });
                channelsDiv.innerHTML = channelsHtml;
            }
        }

        function printReport() {
            window.print();
        }

        // Auto-load report for employees (their terminal)
        @if (!$isAdmin && $terminals->isNotEmpty())
            document.addEventListener('DOMContentLoaded', function() {
                loadReport();
            });
        @endif
    </script>
@endsection

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
                        <button class="btn btn-secondary" onclick="printReport()" id="printBtn" disabled>
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

            <!-- Bookings Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i> Bookings List
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>Booking #</th>
                                    <th>Date</th>
                                    <th>From → To</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Amount</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">
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
                        <i class="fas fa-receipt"></i> Expenses List
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>From → To</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody id="expensesTableBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        No data available. Please generate a report.
                                    </td>
                                </tr>
                            </tbody>
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

            // Update summary cards
            document.getElementById('totalRevenue').textContent = 'PKR ' + parseFloat(stats.revenue.total_revenue).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('totalBookings').textContent = stats.bookings.total;
            document.getElementById('totalExpenses').textContent = 'PKR ' + parseFloat(stats.expenses.total_expenses).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('netProfit').textContent = 'PKR ' + parseFloat(stats.profit.total_profit).toLocaleString('en-US', {
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

            // Render bookings table
            const bookingsBody = document.getElementById('bookingsTableBody');
            if (data.bookings.length === 0) {
                bookingsBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No bookings found for this period.</td>
                    </tr>
                `;
            } else {
                let bookingsHtml = '';
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

                    bookingsHtml += `
                        <tr>
                            <td><span class="badge bg-primary">#${booking.booking_number}</span></td>
                            <td><small>${booking.created_at}</small></td>
                            <td><strong>${booking.from_terminal} → ${booking.to_terminal}</strong></td>
                            <td><span class="badge ${channelBadge}">${booking.channel}</span></td>
                            <td><span class="badge ${statusBadge}">${booking.status}</span></td>
                            <td><span class="badge ${paymentBadge}">${booking.payment_status}</span></td>
                            <td><strong>PKR ${parseFloat(booking.final_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                            <td><small>${booking.user}</small></td>
                        </tr>
                    `;
                });
                bookingsBody.innerHTML = bookingsHtml;
            }

            // Render expenses table
            const expensesBody = document.getElementById('expensesTableBody');
            if (data.expenses.length === 0) {
                expensesBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No expenses found for this period.</td>
                    </tr>
                `;
            } else {
                let expensesHtml = '';
                data.expenses.forEach(expense => {
                    expensesHtml += `
                        <tr>
                            <td><small>${expense.expense_date}</small></td>
                            <td><span class="badge bg-info">${expense.expense_type}</span></td>
                            <td><strong>${expense.from_terminal} → ${expense.to_terminal}</strong></td>
                            <td><strong class="text-danger">PKR ${parseFloat(expense.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                            <td><small>${expense.description || '-'}</small></td>
                            <td><small>${expense.user}</small></td>
                        </tr>
                    `;
                });
                expensesBody.innerHTML = expensesHtml;
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

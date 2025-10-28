@extends('admin.layouts.app')

@section('title', 'Create Booking')

@section('styles')
    <style>
        /* Step Container */
        .booking-wizard {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            transform: translateY(-50%);
            z-index: 0;
        }

        .progress-line {
            position: absolute;
            top: 50%;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: translateY(-50%);
            transition: width 0.3s ease;
            z-index: 0;
        }

        .step-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step-item.active .step-circle {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.1);
        }

        .step-item.completed .step-circle {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .step-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
            text-align: center;
        }

        .step-item.active .step-label {
            color: #667eea;
            font-weight: 600;
        }

        /* Step Content */
        .step-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .step-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Employee Info Badge */
        .employee-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .employee-info .badge {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* Seat Layout */
        .seat-layout {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
        }

        .bus-layout {
            max-width: 600px;
            margin: 0 auto;
        }

        .seat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            gap: 8px;
        }

        .seat-group {
            display: flex;
            gap: 8px;
        }

        .aisle {
            width: 40px;
        }

        .seat {
            width: 45px;
            height: 45px;
            border: 2px solid #6c757d;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            background: white;
            position: relative;
        }

        .seat:hover:not(.booked):not(.sold):not(.locked):not(.driver-seat) {
            transform: scale(1.08);
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }

        .seat.available {
            border-color: #28a745;
            background: #f0fff4;
            color: #28a745;
        }

        .seat.available:hover {
            background: #28a745;
            color: white;
        }

        .seat.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            font-weight: bold;
        }

        .seat.selected .gender-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 2px solid #667eea;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .seat.selected .gender-badge.male {
            color: #0d6efd;
        }

        .seat.selected .gender-badge.female {
            color: #ec4899;
        }

        .seat.booked {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
            cursor: not-allowed;
        }

        .seat.booked::before {
            content: '⏳';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 14px;
        }

        .seat.sold {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }

        .seat.sold::before {
            content: '✓';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 14px;
            background: white;
            color: #dc3545;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .seat.locked {
            background: #e9ecef;
            border: 2px dashed #6c757d;
            color: #6c757d;
            cursor: not-allowed;
        }

        .seat.locked::after {
            content: '🔒';
            position: absolute;
            top: -12px;
            right: -12px;
            font-size: 16px;
        }

        .seat.locked .gender-icon {
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .seat.driver-seat {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
            cursor: not-allowed;
        }

        /* Calculator */
        .calculator-card {
            position: sticky;
            top: 20px;
            background: white;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .calculator-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0;
        }

        .fare-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .fare-row:last-child {
            border-bottom: none;
        }

        .fare-row.total {
            border-top: 2px solid #667eea;
            margin-top: 0.5rem;
            padding-top: 1rem;
            font-size: 1.25rem;
            font-weight: bold;
            color: #667eea;
        }

        .payment-inputs {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        /* Info Cards */
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        /* Passenger Cards */
        .passenger-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .passenger-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }

        .passenger-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 6px 6px 0 0;
            margin: -1.5rem -1.5rem 1rem -1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Action Buttons */
        .btn-wizard {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-wizard:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-next {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-next:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        /* Selection Summary */
        .selection-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .seat-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.25rem;
            font-weight: 600;
        }

        .seat-badge.male {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .seat-badge.female {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
        }

        /* Permission Alert */
        .permission-alert {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .bg-pink {
            background-color: #ec4899 !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Booking Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active">Create Booking</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Employee Info -->
    <div class="employee-info">
        <div>
            <h6 class="mb-1"><i class="bx bx-user me-2"></i>Booking Agent</h6>
            <div><strong>{{ auth()->user()->name }}</strong></div>
            <small>{{ auth()->user()->email }}</small>
        </div>
        <div class="text-end">
            <div class="badge mb-1" id="terminal-badge">
                <i class="bx bx-map-pin me-1"></i>Terminal: <span id="current-terminal">Not Selected</span>
            </div>
            <div class="badge" id="role-badge">
                <i class="bx bx-shield me-1"></i>{{ auth()->user()->roles->first()?->name ?? 'Employee' }}
            </div>
        </div>
    </div>

    @if(!($employeeRoutes['all'] ?? false))
        @if(empty($employeeRoutes['employee_routes'] ?? []))
            <div class="permission-alert">
                <i class="bx bx-error-circle" style="font-size: 24px;"></i>
                <div>
                    <strong>No Route Assignments</strong>
                    <p class="mb-0">You don't have any route assignments. Please contact your administrator to assign you to routes.</p>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i>
                <strong>Your Authorized Routes:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($employeeRoutes['employee_routes'] as $empRoute)
                        <li>{{ $empRoute->route->name }} ({{ $empRoute->route->code }}) - Starting from {{ $empRoute->startingTerminal->name }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    <div class="booking-wizard p-4">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="progress-line" id="progress-line" style="width: 0%"></div>
            <div class="step-item active" data-step="1">
                <div class="step-circle"><i class="bx bx-search"></i></div>
                <div class="step-label">Search Trip</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-circle"><i class="bx bx-chair"></i></div>
                <div class="step-label">Select Seats</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-circle"><i class="bx bx-user"></i></div>
                <div class="step-label">Passengers</div>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-circle"><i class="bx bx-calculator"></i></div>
                <div class="step-label">Payment</div>
            </div>
        </div>

        <!-- Step 1: Search Trip -->
        <div class="step-content active" id="step-1">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h4 class="mb-4"><i class="bx bx-search me-2"></i>Search Available Trips</h4>
                    
                    <form id="search-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Terminal *</label>
                                <select name="from_terminal_id" id="from_terminal_id" class="form-select select2" required>
                                    <option value="">Select Departure Terminal</option>
                                    @foreach($terminals as $terminal)
                                        <option value="{{ $terminal->id }}" data-city="{{ $terminal->city->name }}">
                                            {{ $terminal->name }} ({{ $terminal->city->name }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">This is your booking terminal</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">To Terminal *</label>
                                <select name="to_terminal_id" id="to_terminal_id" class="form-select select2" required>
                                    <option value="">Select Destination Terminal</option>
                                    @foreach($terminals as $terminal)
                                        <option value="{{ $terminal->id }}" data-city="{{ $terminal->city->name }}">
                                            {{ $terminal->name }} ({{ $terminal->city->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Departure Date *</label>
                                <input type="date" name="departure_date" id="departure_date" class="form-control" 
                                       min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Number of Passengers *</label>
                                <select name="passenger_count" id="passenger_count" class="form-select" required>
                                    <option value="1">1 Passenger</option>
                                    <option value="2">2 Passengers</option>
                                    <option value="3">3 Passengers</option>
                                    <option value="4">4 Passengers</option>
                                    <option value="5">5 Passengers</option>
                                    <option value="6">6 Passengers</option>
                                    <option value="7">7 Passengers</option>
                                    <option value="8">8 Passengers</option>
                                    <option value="9">9 Passengers</option>
                                    <option value="10">10 Passengers</option>
                                </select>
                            </div>
                        </div>

                        <div id="available-times-container" style="display: none;" class="mb-3">
                            <label class="form-label">Select Departure Time *</label>
                            <div id="available-times-list" class="d-grid gap-2"></div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button type="button" id="load-times-btn" class="btn btn-wizard btn-next">
                                <i class="bx bx-time me-1"></i>Load Available Times
                            </button>
                            <button type="button" id="search-seats-btn" class="btn btn-wizard btn-next" disabled>
                                <i class="bx bx-right-arrow-alt me-1"></i>Continue to Seat Selection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Step 2: Select Seats -->
        <div class="step-content" id="step-2">
            <div class="row">
                <div class="col-lg-8">
                    <h4 class="mb-4"><i class="bx bx-chair me-2"></i>Select Seats</h4>
                    
                    <div class="info-card" id="trip-info"></div>
                    
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Select {{ '<span id="required-seats">1</span>' }} seat(s)</strong> for your passengers. 
                        Click on available seats to select them.
                    </div>

                    <div class="seat-layout" id="seat-layout-container">
                        <!-- Seats will be loaded here -->
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="calculator-card">
                        <div class="calculator-header">
                            <h6 class="mb-0"><i class="bx bx-list-check me-2"></i>Selection Summary</h6>
                        </div>
                        <div class="p-3">
                            <div id="selected-seats-summary">
                                <p class="text-muted text-center py-3">No seats selected yet</p>
                            </div>

                            <div id="seat-fare-summary" style="display: none;" class="mt-3">
                                <div class="fare-row">
                                    <span>Selected Seats:</span>
                                    <strong id="total-seats-count">0</strong>
                                </div>
                                <div class="fare-row">
                                    <span>Fare per Seat:</span>
                                    <strong id="fare-per-seat">PKR 0.00</strong>
                                </div>
                                <div class="fare-row total">
                                    <span>Subtotal:</span>
                                    <strong id="seats-subtotal">PKR 0.00</strong>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <button type="button" id="continue-to-passengers-btn" class="btn btn-wizard btn-next" disabled>
                                    <i class="bx bx-right-arrow-alt me-1"></i>Continue to Passenger Details
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-wizard" onclick="goToStep(1)">
                                    <i class="bx bx-left-arrow-alt me-1"></i>Back to Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Passenger Details -->
        <div class="step-content" id="step-3">
            <div class="row">
                <div class="col-lg-8">
                    <h4 class="mb-4"><i class="bx bx-user me-2"></i>Passenger Details</h4>
                    
                    <div id="passenger-forms-container">
                        <!-- Passenger forms will be generated here -->
                    </div>

                    <div class="passenger-card mt-4">
                        <h6 class="mb-3"><i class="bx bx-phone me-2"></i>Contact Person Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Name *</label>
                                <input type="text" id="contact_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone *</label>
                                <input type="text" id="contact_phone" class="form-control" 
                                       placeholder="03XX-XXXXXXX" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" id="contact_email" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea id="booking_notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="calculator-card">
                        <div class="calculator-header">
                            <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Booking Summary</h6>
                        </div>
                        <div class="p-3">
                            <div class="selection-summary mb-3">
                                <small class="text-muted d-block mb-2">Selected Seats:</small>
                                <div id="summary-seats-list"></div>
                            </div>

                            <div class="fare-row">
                                <span>Total Fare:</span>
                                <strong id="summary-total-fare">PKR 0.00</strong>
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <button type="button" id="continue-to-payment-btn" class="btn btn-wizard btn-next">
                                    <i class="bx bx-right-arrow-alt me-1"></i>Continue to Payment
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-wizard" onclick="goToStep(2)">
                                    <i class="bx bx-left-arrow-alt me-1"></i>Back to Seats
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Payment & Confirm -->
        <div class="step-content" id="step-4">
            <div class="row">
                <div class="col-lg-8">
                    <h4 class="mb-4"><i class="bx bx-calculator me-2"></i>Payment & Confirmation</h4>
                    
                    <div class="passenger-card">
                        <h6 class="mb-3"><i class="bx bx-credit-card me-2"></i>Payment Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Booking Type *</label>
                                <select id="booking_type" class="form-select" required>
                                    <option value="counter">Counter Booking</option>
                                    <option value="phone">Phone Booking</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method *</label>
                                <select id="payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="mobile_wallet">Mobile Wallet (Easypaisa/JazzCash)</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Discount Amount (PKR)</label>
                                <input type="number" id="discount_amount" class="form-control" 
                                       min="0" step="0.01" value="0">
                                <small class="text-muted">Enter discount if applicable</small>
                            </div>
                        </div>
                    </div>

                    <div class="passenger-card mt-3">
                        <h6 class="mb-3"><i class="bx bx-money me-2"></i>Payment Calculator</h6>
                        <div class="payment-inputs">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Amount Received (PKR) *</label>
                                    <input type="number" id="amount_received" class="form-control" 
                                           min="0" step="0.01" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Change to Return</label>
                                    <input type="text" id="change_amount" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning" id="insufficient-payment" style="display: none;">
                            <i class="bx bx-error me-2"></i>
                            Amount received is less than the total amount. Booking will be marked as <strong>Pending Payment</strong>.
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="calculator-card">
                        <div class="calculator-header">
                            <h6 class="mb-0"><i class="bx bx-receipt me-2"></i>Final Bill</h6>
                        </div>
                        <div class="p-3">
                            <div class="fare-row">
                                <span>Subtotal:</span>
                                <strong id="final-subtotal">PKR 0.00</strong>
                            </div>
                            <div class="fare-row">
                                <span>Discount:</span>
                                <strong id="final-discount" class="text-success">PKR 0.00</strong>
                            </div>
                            <div class="fare-row total">
                                <span>Grand Total:</span>
                                <strong id="final-grand-total">PKR 0.00</strong>
                            </div>

                            <hr>

                            <div class="fare-row">
                                <span>Amount Received:</span>
                                <strong id="final-received" class="text-primary">PKR 0.00</strong>
                            </div>
                            <div class="fare-row">
                                <span>Change:</span>
                                <strong id="final-change" class="text-info">PKR 0.00</strong>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="button" id="confirm-booking-btn" class="btn btn-success btn-wizard btn-lg">
                                    <i class="bx bx-check-circle me-1"></i>Confirm Booking
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-wizard" onclick="goToStep(3)">
                                    <i class="bx bx-left-arrow-alt me-1"></i>Back to Passengers
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Global variables
        let currentStep = 1;
        let selectedSeats = [];
        let tripData = {};
        let farePerSeat = 0;
        let bookedSeats = [];
        let seatStatuses = {};
        let passengerCount = 1;
        let employeeTerminal = null;
        
        // Employee permissions
        const employeePermissions = @json($employeeRoutes);
        const allowedTerminalIds = employeePermissions.allowed_terminal_ids || [];
        const hasAllPermissions = employeePermissions.all || false;

        $(document).ready(function() {
            $('.select2').select2();
            initializeStepNavigation();
            initializePaymentCalculator();
            
            // Filter terminals based on employee permissions
            if (!hasAllPermissions && allowedTerminalIds.length > 0) {
                filterEmployeeTerminals();
            }
            
            // Update terminal badge when from terminal is selected
            $('#from_terminal_id').on('change', function() {
                const selectedText = $(this).find('option:selected').text();
                const terminalId = $(this).val();
                
                if (terminalId) {
                    $('#current-terminal').text(selectedText);
                    employeeTerminal = terminalId;
                    
                    // Check employee permissions
                    checkEmployeePermissions(terminalId);
                } else {
                    $('#current-terminal').text('Not Selected');
                }
            });

            // Passenger count change
            $('#passenger_count').on('change', function() {
                passengerCount = parseInt($(this).val());
                $('#required-seats').text(passengerCount);
            });
        });

        // Step Navigation
        function initializeStepNavigation() {
            $('#load-times-btn').on('click', loadAvailableTimes);
            $('#search-seats-btn').on('click', searchTrip);
            $('#continue-to-passengers-btn').on('click', () => {
                if (validateSeatSelection()) {
                    generatePassengerForms();
                    goToStep(3);
                }
            });
            $('#continue-to-payment-btn').on('click', () => {
                if (validatePassengerDetails()) {
                    goToStep(4);
                    updatePaymentCalculator();
                }
            });
            $('#confirm-booking-btn').on('click', confirmBooking);
        }

        function goToStep(step) {
            // Hide all steps
            $('.step-content').removeClass('active');
            $('.step-item').removeClass('active completed');
            
            // Show current step
            $(`#step-${step}`).addClass('active');
            $(`.step-item[data-step="${step}"]`).addClass('active');
            
            // Mark previous steps as completed
            for (let i = 1; i < step; i++) {
                $(`.step-item[data-step="${i}"]`).addClass('completed');
            }
            
            // Update progress line
            const progress = ((step - 1) / 3) * 100;
            $('#progress-line').css('width', progress + '%');
            
            currentStep = step;
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Filter Terminals Based on Employee Permissions
        function filterEmployeeTerminals() {
            $('#from_terminal_id option').each(function() {
                const terminalId = parseInt($(this).val());
                if (terminalId && !allowedTerminalIds.includes(terminalId)) {
                    $(this).prop('disabled', true).addClass('text-muted');
                    $(this).text($(this).text() + ' (Not Authorized)');
                }
            });
        }

        // Check Employee Permissions
        function checkEmployeePermissions(terminalId) {
            if (!hasAllPermissions && !allowedTerminalIds.includes(parseInt(terminalId))) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unauthorized Terminal',
                    text: 'You are not authorized to create bookings from this terminal.',
                    confirmButtonText: 'OK'
                });
                $('#from_terminal_id').val('').trigger('change');
                return false;
            }
            return true;
        }

        // Load Available Times
        function loadAvailableTimes() {
            const fromTerminal = $('#from_terminal_id').val();
            const toTerminal = $('#to_terminal_id').val();
            const departureDate = $('#departure_date').val();

            if (!fromTerminal || !toTerminal || !departureDate) {
                Swal.fire('Missing Information', 'Please select terminals and date', 'warning');
                return;
            }

            if (fromTerminal === toTerminal) {
                Swal.fire('Invalid Selection', 'Departure and destination must be different', 'error');
                return;
            }

            $.ajax({
                url: "{{ route('admin.bookings.get-available-times') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    from_terminal_id: fromTerminal,
                    to_terminal_id: toTerminal,
                    departure_date: departureDate
                },
                success: function(response) {
                    if (response.success && response.data.times.length > 0) {
                        displayAvailableTimes(response.data.times);
                    } else {
                        Swal.fire('No Times Available', response.message || 'No departure times found', 'info');
                    }
                }
            });
        }

        function displayAvailableTimes(times) {
            const timesList = times.map(time => `
                <div class="form-check border p-3 rounded" style="cursor: pointer;" onclick="selectTime('${time.route_id}', '${time.time}')">
                    <input class="form-check-input" type="radio" name="selected_time" 
                           value="${time.time}" data-route="${time.route_id}" 
                           id="time_${time.time.replace(/[^0-9]/g, '')}" required>
                    <label class="form-check-label w-100" for="time_${time.time.replace(/[^0-9]/g, '')}" style="cursor: pointer;">
                        <strong>${time.time}</strong>
                        <br><small class="text-muted">${time.route_name} (${time.route_code})</small>
                    </label>
                </div>
            `).join('');
            
            $('#available-times-list').html(timesList);
            $('#available-times-container').show();
            $('#search-seats-btn').prop('disabled', false);
        }

        function selectTime(routeId, time) {
            $(`input[value="${time}"]`).prop('checked', true);
        }

        // Search Trip
        function searchTrip() {
            const selectedTime = $('input[name="selected_time"]:checked');
            
            if (!selectedTime.length) {
                Swal.fire('Select Time', 'Please select a departure time', 'warning');
                return;
            }

            const formData = {
                route_id: selectedTime.data('route'),
                from_terminal_id: $('#from_terminal_id').val(),
                to_terminal_id: $('#to_terminal_id').val(),
                departure_date: $('#departure_date').val(),
                departure_time: selectedTime.val()
            };

            $.ajax({
                url: "{{ route('admin.bookings.search') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    // Since we're expecting a view return, we need to handle it differently
                    // We'll redirect or load the content
                    goToStep(2);
                    loadSeatLayout(formData);
                }
            });
        }

        function loadSeatLayout(searchData) {
            // This would load the seat layout via AJAX
            // For now, we'll create a basic layout
            generateSeatLayout();
        }

        function generateSeatLayout() {
            let html = '<div class="bus-layout">';
            
            // Driver row
            html += '<div class="seat-row"><div class="seat driver-seat"><i class="bx bx-steering-wheel"></i></div><div class="aisle"></div><div class="seat-group"></div></div>';
            
            // 40 seats (10 rows × 4 seats)
            let seatNumber = 1;
            for (let row = 0; row < 10; row++) {
                html += '<div class="seat-row"><div class="seat-group">';
                for (let i = 0; i < 2; i++) {
                    html += `<div class="seat available" data-seat="${seatNumber}" onclick="handleSeatClick(${seatNumber})">${seatNumber}</div>`;
                    seatNumber++;
                }
                html += '</div><div class="aisle"></div><div class="seat-group">';
                for (let i = 0; i < 2; i++) {
                    html += `<div class="seat available" data-seat="${seatNumber}" onclick="handleSeatClick(${seatNumber})">${seatNumber}</div>`;
                    seatNumber++;
                }
                html += '</div></div>';
            }
            
            // Last row with 5 seats
            html += '<div class="seat-row justify-content-center" style="display: flex;"><div class="seat-group">';
            for (let i = 41; i <= 45; i++) {
                html += `<div class="seat available" data-seat="${i}" onclick="handleSeatClick(${i})">${i}</div>`;
            }
            html += '</div></div>';
            
            html += '</div>';
            
            // Add legend
            html += `
                <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="seat available" style="width: 30px; height: 30px;"></div>
                        <span>Available</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="seat selected" style="width: 30px; height: 30px;"></div>
                        <span>Your Selection</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="seat locked" style="width: 30px; height: 30px;"></div>
                        <span>Locked by Others</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="seat booked" style="width: 30px; height: 30px;"></div>
                        <span>Booked (Pending)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="seat sold" style="width: 30px; height: 30px;"></div>
                        <span>Sold (Confirmed)</span>
                    </div>
                </div>
            `;
            
            $('#seat-layout-container').html(html);
            
            // Mock trip info
            $('#trip-info').html(`
                <h6><i class="bx bx-bus me-1"></i>Trip Information</h6>
                <div class="info-row">
                    <span>Route:</span>
                    <strong>${$('#from_terminal_id option:selected').text()} → ${$('#to_terminal_id option:selected').text()}</strong>
                </div>
                <div class="info-row">
                    <span>Date:</span>
                    <strong>${$('#departure_date').val()}</strong>
                </div>
                <div class="info-row">
                    <span>Fare per Seat:</span>
                    <strong>PKR 1,500.00</strong>
                </div>
            `);
            
            farePerSeat = 1500; // Mock fare
        }

        function handleSeatClick(seatNumber) {
            const $seat = $(`.seat[data-seat="${seatNumber}"]`);
            
            if ($seat.hasClass('selected')) {
                deselectSeat(seatNumber);
            } else if ($seat.hasClass('available')) {
                if (selectedSeats.length >= passengerCount) {
                    Swal.fire('Maximum Reached', `You can only select ${passengerCount} seat(s)`, 'warning');
                    return;
                }
                selectSeat(seatNumber);
            }
        }

        function selectSeat(seatNumber) {
            Swal.fire({
                title: 'Select Gender',
                text: `Seat ${seatNumber}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bx bx-male"></i> Male',
                cancelButtonText: '<i class="bx bx-female"></i> Female',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ec4899',
                showCloseButton: true,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    addSeatSelection(seatNumber, 'male');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    addSeatSelection(seatNumber, 'female');
                }
            });
        }

        function addSeatSelection(seatNumber, gender) {
            const $seat = $(`.seat[data-seat="${seatNumber}"]`);
            $seat.removeClass('available').addClass('selected');
            
            const icon = gender === 'male' ? '<i class="bx bx-male"></i>' : '<i class="bx bx-female"></i>';
            $seat.append(`<span class="gender-badge ${gender}">${icon}</span>`);
            
            selectedSeats.push({ seat: seatNumber, gender: gender });
            updateSeatSummary();
        }

        function deselectSeat(seatNumber) {
            const $seat = $(`.seat[data-seat="${seatNumber}"]`);
            $seat.removeClass('selected').addClass('available').find('.gender-badge').remove();
            
            selectedSeats = selectedSeats.filter(s => s.seat !== seatNumber);
            updateSeatSummary();
        }

        function updateSeatSummary() {
            if (selectedSeats.length === 0) {
                $('#selected-seats-summary').html('<p class="text-muted text-center py-3">No seats selected yet</p>');
                $('#seat-fare-summary').hide();
                $('#continue-to-passengers-btn').prop('disabled', true);
            } else {
                const list = selectedSeats.map(item => {
                    const color = item.gender === 'male' ? 'male' : 'female';
                    const icon = item.gender === 'male' ? 'bx-male' : 'bx-female';
                    return `<span class="seat-badge ${color}"><i class="bx ${icon} me-1"></i>Seat ${item.seat}</span>`;
                }).join('');
                
                $('#selected-seats-summary').html(list);
                $('#seat-fare-summary').show();
                
                const total = selectedSeats.length * farePerSeat;
                $('#total-seats-count').text(selectedSeats.length);
                $('#fare-per-seat').text('PKR ' + farePerSeat.toFixed(2));
                $('#seats-subtotal').text('PKR ' + total.toFixed(2));
                
                $('#continue-to-passengers-btn').prop('disabled', selectedSeats.length !== passengerCount);
            }
            
            updateFinalSummary();
        }

        function validateSeatSelection() {
            if (selectedSeats.length !== passengerCount) {
                Swal.fire('Incomplete Selection', `Please select exactly ${passengerCount} seat(s)`, 'warning');
                return false;
            }
            return true;
        }

        // Generate Passenger Forms
        function generatePassengerForms() {
            let html = '';
            selectedSeats.forEach((item, index) => {
                html += `
                    <div class="passenger-card">
                        <div class="passenger-card-header">
                            <span><i class="bx bx-user me-2"></i>Passenger ${index + 1}</span>
                            <span class="badge bg-white text-dark">Seat ${item.seat} - ${item.gender === 'male' ? 'Male' : 'Female'}</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control passenger-name" data-index="${index}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CNIC *</label>
                                <input type="text" class="form-control passenger-cnic" data-index="${index}" 
                                       placeholder="XXXXX-XXXXXXX-X" maxlength="15" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control passenger-age" data-index="${index}" min="1" max="150">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control passenger-phone" data-index="${index}" placeholder="03XX-XXXXXXX">
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#passenger-forms-container').html(html);
            
            // Update summary
            $('#summary-seats-list').html(selectedSeats.map(item => 
                `<span class="seat-badge ${item.gender}"><i class="bx bx-${item.gender} me-1"></i>Seat ${item.seat}</span>`
            ).join(''));
            $('#summary-total-fare').text('PKR ' + (selectedSeats.length * farePerSeat).toFixed(2));
        }

        function validatePassengerDetails() {
            let valid = true;
            
            $('.passenger-name').each(function() {
                if (!$(this).val().trim()) {
                    valid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            $('.passenger-cnic').each(function() {
                if (!$(this).val().trim()) {
                    valid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            if (!$('#contact_name').val().trim()) {
                valid = false;
                $('#contact_name').addClass('is-invalid');
            } else {
                $('#contact_name').removeClass('is-invalid');
            }
            
            if (!$('#contact_phone').val().trim()) {
                valid = false;
                $('#contact_phone').addClass('is-invalid');
            } else {
                $('#contact_phone').removeClass('is-invalid');
            }
            
            if (!valid) {
                Swal.fire('Incomplete Information', 'Please fill all required fields', 'warning');
            }
            
            return valid;
        }

        // Payment Calculator
        function initializePaymentCalculator() {
            $('#discount_amount, #amount_received').on('input', updatePaymentCalculator);
        }

        function updatePaymentCalculator() {
            const subtotal = selectedSeats.length * farePerSeat;
            const discount = parseFloat($('#discount_amount').val()) || 0;
            const grandTotal = subtotal - discount;
            const received = parseFloat($('#amount_received').val()) || 0;
            const change = received - grandTotal;
            
            $('#final-subtotal').text('PKR ' + subtotal.toFixed(2));
            $('#final-discount').text('PKR ' + discount.toFixed(2));
            $('#final-grand-total').text('PKR ' + grandTotal.toFixed(2));
            $('#final-received').text('PKR ' + received.toFixed(2));
            $('#final-change').text('PKR ' + change.toFixed(2));
            $('#change_amount').val(change >= 0 ? 'PKR ' + change.toFixed(2) : 'Insufficient');
            
            if (received > 0 && received < grandTotal) {
                $('#insufficient-payment').show();
            } else {
                $('#insufficient-payment').hide();
            }
        }

        function updateFinalSummary() {
            const subtotal = selectedSeats.length * farePerSeat;
            $('#summary-total-fare').text('PKR ' + subtotal.toFixed(2));
        }

        // Confirm Booking
        function confirmBooking() {
            if (!validateBooking()) {
                return;
            }
            
            Swal.fire({
                title: 'Confirm Booking?',
                text: 'Are you sure you want to create this booking?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Create Booking',
                cancelButtonText: 'Review Again',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBooking();
                }
            });
        }

        function validateBooking() {
            const received = parseFloat($('#amount_received').val()) || 0;
            
            if (received === 0) {
                Swal.fire('Payment Required', 'Please enter the amount received', 'warning');
                return false;
            }
            
            return true;
        }

        function submitBooking() {
            const bookingData = {
                // Trip data
                trip_id: tripData.id || 1, // Mock
                from_stop_id: 1, // Mock
                to_stop_id: 2, // Mock
                
                // Booking details
                booking_type: $('#booking_type').val(),
                payment_method: $('#payment_method').val(),
                payment_status: parseFloat($('#amount_received').val()) >= (selectedSeats.length * farePerSeat - parseFloat($('#discount_amount').val())) ? 'paid' : 'pending',
                
                // Fare
                total_fare: selectedSeats.length * farePerSeat,
                discount_amount: parseFloat($('#discount_amount').val()) || 0,
                
                // Contact
                passenger_contact_name: $('#contact_name').val(),
                passenger_contact_phone: $('#contact_phone').val(),
                passenger_contact_email: $('#contact_email').val(),
                notes: $('#booking_notes').val(),
                
                // Seats
                seats: selectedSeats.map((item, index) => ({
                    seat_number: item.seat,
                    passenger_name: $(`.passenger-name[data-index="${index}"]`).val(),
                    passenger_cnic: $(`.passenger-cnic[data-index="${index}"]`).val(),
                    passenger_age: $(`.passenger-age[data-index="${index}"]`).val(),
                    passenger_phone: $(`.passenger-phone[data-index="${index}"]`).val(),
                    passenger_gender: item.gender,
                    fare: farePerSeat
                }))
            };
            
            $.ajax({
                url: "{{ route('admin.bookings.store') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: bookingData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Created!',
                        text: 'Booking has been created successfully',
                        confirmButtonText: 'View Booking'
                    }).then(() => {
                        // Redirect to booking details or list
                        window.location.href = "{{ route('admin.bookings.index') }}";
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to create booking. Please try again.', 'error');
                }
            });
        }
    </script>
@endsection


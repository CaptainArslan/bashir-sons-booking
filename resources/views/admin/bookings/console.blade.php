@extends('admin.layouts.app')

@section('title', 'Booking Console')

@section('content')
    @include('admin.bookings.console._styles')
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt"></i>
                    Booking Console - Real-Time Seat Booking
                    @if (auth()->user()->hasRole('admin'))
                        <span class="badge bg-info ms-2">Admin Mode</span>
                    @else
                        <span class="badge bg-warning ms-2">Employee Mode - Terminal:
                            {{ auth()->user()->terminal?->name ?? 'N/A' }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">

                    <!-- Date -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Travel Date</label>
                        <input type="date" class="form-control form-control-lg" id="travelDate"
                            value="{{ $mindate->format('Y-m-d') }}" min="{{ $mindate->format('Y-m-d') }}"
                            max="{{ $maxdate->format('Y-m-d') }}" />
                    </div>

                    <!-- From Terminal / Stop -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">From Terminal</label>
                        <select class="form-select form-select-lg select2" id="fromTerminal"
                            @if (!auth()->user()->hasRole('admin')) disabled @endif>
                            <option value="">Loading...</option>
                        </select>
                    </div>

                    <!-- To Terminal / Stop -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">To Terminal</label>
                        <select class="form-select form-select-lg select2" id="toTerminal" disabled
                            onchange="fetchArrivalTime()">
                            <option value="">Select Destination</option>
                        </select>
                    </div>

                    <!-- Departure Time (Timetable Stops) -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Departure Time</label>
                        <select class="form-select form-select-lg select2" id="departureTime" onchange="fetchArrivalTime()"
                            disabled>
                            <option value="">Select Departure Time</option>
                        </select>
                    </div>

                    <!-- Arrival Time (Timetable Stops) -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Arrival Time</label>
                        <input type="text" class="form-control form-control-lg" id="arrivalTime" disabled readonly>
                    </div>

                    <!-- Load Trip Button -->
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button class="btn btn-primary btn-lg flex-grow-1 fw-bold" id="loadTripBtn" onclick="loadTrip()">
                            <i class="fas fa-play"></i> Load
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trip Content (shown when trip loaded) -->
        <div id="tripContent" style="display: none;">
            <!-- Trip Details Card (Above all sections) -->
            <div class="card mb-4 shadow-sm border-0"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-route"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Route</small>
                                    <h5 class="mb-0 fw-bold" id="tripRoute">-</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Date</small>
                                    <h5 class="mb-0 fw-bold" id="tripDate">-</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Time</small>
                                    <h5 class="mb-0 fw-bold" id="tripTime">-</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div id="busDriverSection"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Left Column: Seat Map (3 columns) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header text-white"
                            style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-chair"></i> Seat Map
                            </h6>
                        </div>
                        <div class="card-body p-3" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                            <!-- Summary Card (Matching Image Design) -->
                            <div class="mb-3 p-3 bg-white rounded-lg shadow-sm w-100 border border-gray-200">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-dark">Outbound</span>
                                    <span class="small text-dark" id="outboundFare">Rs 0</span>
                                </div>
                                <hr class="my-2" style="border-color: #e2e8f0; margin: 0.5rem 0;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-semibold">Total</span>
                                    <span class="small fw-bold" style="color: #3B82F6;" id="totalSummaryFare">Rs 0</span>
                                </div>
                            </div>

                            <!-- Legend -->
                            <div class="mb-3 p-3 bg-white rounded-lg shadow-sm w-100 border border-gray-200">
                                <div class="seat-legend">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #E2E8F0; border: 1px solid #cbd5e1; border-radius: 4px;">
                                        </div>
                                        <span class="small text-dark">Available</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #3B82F6; border: 1px solid #2563eb; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👨</span>
                                        </div>
                                        <span class="small text-dark">Selected (Male)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #3B82F6; border: 1px solid #2563eb; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👩</span>
                                        </div>
                                        <span class="small text-dark">Selected (Female)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #22D3EE; border: 1px solid #06b6d4; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👨</span>
                                        </div>
                                        <span class="small text-dark">Male Booked</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #EC4899; border: 1px solid #db2777; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👩</span>
                                        </div>
                                        <span class="small text-dark">Female Booked</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #fbbf24; border: 1px solid #f59e0b; border-radius: 4px;">
                                        </div>
                                        <span class="small text-dark">Held</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Seat Grid -->
                            <div class="seat-map-container">
                                <h6 class="text-center mb-3" style="color: #334155; font-weight: 600; font-size: 1rem;">
                                    Select Your Seat</h6>
                                <div class="seat-grid" id="seatGrid"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Column: Booking Form (5 columns) -->
                <div class="col-lg-5 col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-list"></i> Booking Summary
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: calc(100vh - 300px); overflow-y: auto; padding: 1rem;">
                            <!-- Selected Seats -->
                            <div class="mb-2">
                                <label class="form-label fw-bold small mb-1">
                                    <i class="fas fa-list"></i> Selected Seats
                                    <span class="badge bg-primary ms-2" id="seatCount">(0)</span>
                                </label>
                                <div id="selectedSeatsList" class="d-flex flex-wrap gap-2 mb-0"
                                    style="min-height: 40px;">
                                    <span class="text-muted small">No seats selected yet</span>
                                </div>
                            </div>

                            <!-- Fare Calculation -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-calculator"></i> Fare</h6>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Base Fare</label>
                                        <input type="number" class="form-control form-control-sm" id="baseFare"
                                            min="0" step="0.01" placeholder="0.00" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Discount</label>
                                        <input type="text" class="form-control form-control-sm" id="discountInfo"
                                            placeholder="None" readonly>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Total Fare</label>
                                        <input type="number" class="form-control form-control-sm fw-bold text-success"
                                            id="totalFare" min="0" step="0.01" placeholder="0.00" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Tax/Charge
                                            <small class="text-muted" id="taxLabel">(Auto: PKR 40 for Mobile
                                                Wallet)</small>
                                        </label>
                                        <input type="number" class="form-control form-control-sm" id="tax"
                                            min="0" step="0.01" value="0" placeholder="0.00"
                                            onchange="calculateFinal()">
                                    </div>
                                </div>
                                <div class="alert alert-primary border-1 mb-0 p-2 small text-center">
                                    <strong class="d-block mb-0">Final: PKR <span id="finalAmount"
                                            class="text-success">0.00</span></strong>
                                </div>
                            </div>

                            <!-- Booking Type & Payment -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-bookmark"></i> Type & Payment</h6>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Booking Type</label>
                                        <select class="form-select form-select-sm" id="bookingType" name="bookingType"
                                            onchange="togglePaymentFields()">
                                            <option value="counter" selected>🏪 Counter</option>
                                            <option value="phone">📞 Phone (Hold till before 60 mins of departure)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2" id="paymentMethodSelect" style="display: block;">
                                        <label class="form-label small fw-bold">Payment Method</label>
                                        <select class="form-select form-select-sm" id="paymentMethod"
                                            name="paymentMethod" onchange="toggleTransactionIdField()">
                                            @foreach ($paymentMethods as $method)
                                                <option value="{{ $method['value'] }}"
                                                    {{ $loop->first ? 'selected' : '' }}>
                                                    {{ $method['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Fields (Counter Only) -->
                            <div id="paymentFields" class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-credit-card"></i> Payment Details</h6>

                                <!-- Transaction ID Field (for non-cash payments) -->
                                <div class="mb-2" id="transactionIdField" style="display: none;">
                                    <label class="form-label small">Transaction ID</label>
                                    <input type="text" class="form-control form-control-sm" id="transactionId"
                                        placeholder="TXN123456789" maxlength="100">
                                </div>

                                <div class="mb-2" id="amountReceivedField">
                                    <label class="form-label small">Amount Received (PKR)</label>
                                    <input type="number" class="form-control form-control-sm" id="amountReceived"
                                        min="0" step="0.01" placeholder="0.00" value="0"
                                        onchange="calculateReturn()">
                                </div>
                                <div id="returnDiv" style="display: none;">
                                    <div class="alert alert-success border-1 mb-0 p-2 small">
                                        <strong>💰 Return: PKR <span id="returnAmount">0.00</span></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold"><i class="fas fa-sticky-note"></i> Notes</label>
                                <textarea class="form-control form-control-sm" id="notes" rows="2" maxlength="500"
                                    placeholder="Optional notes..."></textarea>
                            </div>

                            <!-- Passenger Information Section -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 small"><i class="fas fa-users"></i> Passengers</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addPassengerBtn"
                                        onclick="addExtraPassenger()">
                                        <i class="fas fa-plus-circle"></i> Add Passenger
                                    </button>
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 0.75rem;"><strong>Required:</strong>
                                    At least 1 passenger information.</p>
                                <div id="passengerInfoContainer" style="max-height: 250px; overflow-y: auto;"></div>
                            </div>

                            <!-- Confirm Button -->
                            <button class="btn btn-success w-100 fw-bold py-2 small" onclick="confirmBooking()"
                                id="confirmBtn">
                                <i class="fas fa-check-circle"></i> Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Assign Bus & Trip Passengers List (4 columns) -->
                <div class="col-lg-4 col-md-12">
                    <!-- Assign Bus Card -->
                    <div class="card shadow-sm mb-3" id="assignBusCard" style="display: none;">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0 small">
                                <i class="fas fa-bus"></i> Bus Assignment
                            </h6>
                        </div>
                        <div class="card-body p-2">
                            <div id="assignBusCardContent">
                                <p class="text-muted small mb-2">Assign a bus and driver for this trip.</p>
                                <button class="btn btn-primary btn-sm w-100 fw-bold" id="assignBusBtnCard"
                                    onclick="openAssignBusModalFromHeader()">
                                    <i class="fas fa-bus"></i> Assign Bus & Driver
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Trip Passengers List Card -->
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0 small">
                                <i class="fas fa-list-check"></i> Booked Passengers
                            </h6>
                        </div>
                        <div class="card-body p-2" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                            <div id="tripPassengersList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gender Selection Modal -->
    <div class="modal fade" id="genderModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user"></i> Select Gender - <span id="seatLabel">Seat</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-center mb-0">Please select passenger gender:</p>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-primary btn-lg flex-grow-1 fw-bold"
                        onclick="selectGender('male')">
                        👨 Male
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-lg flex-grow-1 fw-bold"
                        onclick="selectGender('female')">
                        👩 Female
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-check-circle"></i> Booking Confirmed Successfully!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-4 p-3 bg-light rounded text-center">
                        <h6 class="text-muted mb-2">Booking Number</h6>
                        <h3 class="fw-bold text-primary" id="bookingNumber">-</h3>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2"><strong>Seats:</strong> <span id="bookedSeats"
                                class="badge bg-info ms-2"></span></p>
                        <p class="mb-0"><strong>Status:</strong> <span id="bookingStatus"
                                class="badge bg-success ms-2"></span></p>
                    </div>

                    <div class="alert alert-light border-2 mb-4">
                        <h6 class="fw-bold mb-3">Fare Breakdown</h6>
                        <p class="mb-2"><strong>Total Fare:</strong> <span class="float-end">PKR <span
                                    id="confirmedFare">0.00</span></span></p>
                        <p class="mb-2"><strong>Discount:</strong> <span class="float-end">-PKR <span
                                    id="confirmedDiscount">0.00</span></span></p>
                        <p class="mb-2"><strong>Tax/Charge:</strong> <span class="float-end">+PKR <span
                                    id="confirmedTax">0.00</span></span></p>
                        <hr>
                        <p class="mb-0"><strong>Final Amount:</strong> <span class="float-end fw-bold text-success">PKR
                                <span id="confirmedFinal">0.00</span></span></p>
                    </div>

                    <p><strong>Payment Method:</strong> <span class="badge bg-warning ms-2"
                            id="paymentMethodDisplay">-</span></p>
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-lg fw-bold flex-fill" id="printTicketBtn">
                        <i class="fas fa-print"></i> Print Ticket (80mm)
                    </button>
                    <button type="button" class="btn btn-success btn-lg fw-bold flex-fill" data-bs-dismiss="modal"
                        onclick="reloadPage()">
                        <i class="fas fa-check"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Assign Bus/Driver Modal (Sticky - Only closes on X button) -->
    <div class="modal fade" id="assignBusModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-bus"></i> Assign Bus & Driver
                    </h5>
                    <button type="button" class="btn-close btn-close-white" id="assignBusModalCloseBtn" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div id="assignBusModalBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="assignBusModalCancelBtn" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmAssignBusBtn">
                        <i class="fas fa-check"></i> Assign Bus & Driver
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        @include('admin.bookings.console._state-init')
        @include('admin.bookings.console._terminal-route-functions')
        @include('admin.bookings.console._fare-functions')
        @include('admin.bookings.console._trip-seat-functions')
        @include('admin.bookings.console._passenger-functions')
        @include('admin.bookings.console._booking-payment-functions')
        @include('admin.bookings.console._websocket-functions')
        @include('admin.bookings.console._bus-assignment-functions')
        @include('admin.bookings.console._utility-functions')
    </script>
@endsection

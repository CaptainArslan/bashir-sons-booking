@extends('admin.layouts.app')

@section('title', 'Booking Console')

@section('content')
<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-ticket-alt"></i> 
                Booking Console - Real-Time Seat Booking
                @if(auth()->user()->hasRole('admin'))
                    <span class="badge bg-info ms-2">Admin Mode</span>
                @else
                    <span class="badge bg-warning ms-2">Employee Mode - Terminal: {{ auth()->user()->terminal?->name ?? 'N/A' }}</span>
                @endif
            </h5>
        </div>
        <div class="card-body bg-light">
            <div class="row g-3">
                <!-- From Terminal / Stop -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">From Terminal</label>
                    <select class="form-select form-select-lg" id="fromTerminal" @if(!auth()->user()->hasRole('admin')) disabled @endif>
                        <option value="">Loading...</option>
                    </select>
                </div>

                <!-- To Terminal / Stop -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">To Terminal</label>
                    <select class="form-select form-select-lg" id="toTerminal" disabled>
                        <option value="">Select Destination</option>
                    </select>
                </div>

                <!-- Date -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Travel Date</label>
                    <input type="date" class="form-control form-control-lg" id="travelDate"
                        min="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" />
                </div>

                <!-- Departure Time (Timetable Stops) -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">Departure Time</label>
                    <select class="form-select form-select-lg" id="departureTime" disabled>
                        <option value="">Select Departure Time</option>
                    </select>
                </div>

                <!-- Load Trip Button -->
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-lg flex-grow-1 fw-bold" id="loadTripBtn" onclick="loadTrip()">
                        <i class="fas fa-play"></i> Load Trip & Seats
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Content (shown when trip loaded) -->
    <div id="tripContent" style="display: none;">
        <div class="row g-4">
            <!-- Left Column: Seat Map & Booking Form (8 columns) -->
            <div class="col-lg-8">
                <!-- Seat Map Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chair"></i> Seat Map (44 Seats - 4x11 Layout)
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Legend -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <span class="badge bg-success me-3 p-2">🟩 Available</span>
                            <span class="badge bg-danger me-3 p-2">🟥 Booked</span>
                            <span class="badge bg-warning me-3 p-2">🟨 Held by Others</span>
                            <span class="badge bg-info p-2">🟦 Your Selection</span>
                        </div>
                        <!-- Seat Grid -->
                        <div class="seat-grid" id="seatGrid"></div>
                    </div>
                </div>

                <!-- Booking Form Section -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-clipboard-list"></i> Booking Summary
                        </h6>
                    </div>
                    <div class="card-body" style="max-height: 80vh; overflow-y: auto;">
                        <!-- Trip Info Section -->
                        <div class="mb-3 p-3 bg-light rounded border-start border-4 border-info">
                            <h6 class="fw-bold mb-2">📍 Trip Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Route</small>
                                    <p class="mb-2"><strong><span id="tripRoute">-</span></strong></p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Date</small>
                                    <p class="mb-2"><strong><span id="tripDate">-</span></strong></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Departure Time</small>
                                    <p class="mb-0"><strong><span id="tripTime">-</span></strong></p>
                                </div>
                            </div>

                            <!-- Bus & Driver Section -->
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="fw-bold mb-2">🚌 Bus & Driver</h6>
                                <div id="busDriverSection"></div>
                            </div>
                        </div>

                        <!-- Selected Seats -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list"></i> Selected Seats 
                                <span class="badge bg-primary ms-2" id="seatCount">(0)</span>
                            </label>
                            <div class="alert alert-info border-2" id="selectedSeatsList" style="min-height: 100px; font-size: 0.95rem;">
                                <p class="text-muted mb-0">No seats selected yet</p>
                            </div>
                        </div>

                        <!-- Fare Calculation -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3"><i class="fas fa-calculator"></i> Fare Calculation</h6>
                            <div class="mb-3">
                                <label class="form-label">Base Fare (PKR) - Per Seat</label>
                                <div class="input-group">
                                    <input type="number" class="form-control form-control-lg" id="baseFare" 
                                        min="0" step="0.01" placeholder="0.00" readonly>
                                    <span class="input-group-text">Read-only</span>
                                </div>
                                <small class="form-text text-muted">Automatically fetched from fare database</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Discount Applied</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg" id="discountInfo" 
                                        placeholder="None" readonly>
                                    <span class="input-group-text">-</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Fare (PKR)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control form-control-lg fw-bold text-success" id="totalFare" 
                                        min="0" step="0.01" placeholder="0.00" readonly>
                                    <span class="input-group-text">Calculated</span>
                                </div>
                                <small class="form-text text-muted">Base Fare × Number of Seats</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Additional Tax/Service Charge (PKR)</label>
                                <input type="number" class="form-control form-control-lg" id="tax" 
                                    min="0" step="0.01" value="0" placeholder="0.00" onchange="calculateFinal()">
                            </div>
                            <div class="alert alert-primary border-3 mb-0 py-3">
                                <h5 class="mb-0">
                                    <strong>Final Amount: PKR <span id="finalAmount" class="text-success">0.00</span></strong>
                                </h5>
                            </div>
                        </div>

                        <!-- Booking Type -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3"><i class="fas fa-bookmark"></i> Booking Type</h6>
                            <div>
                                <div class="form-check form-check-lg">
                                    <input class="form-check-input" type="radio" name="bookingType" 
                                        id="counterBooking" value="counter" checked onchange="togglePaymentFields()">
                                    <label class="form-check-label fw-bold" for="counterBooking">
                                        🏪 Counter Booking (Immediate Confirmation)
                                    </label>
                                </div>
                                <div class="form-check form-check-lg">
                                    <input class="form-check-input" type="radio" name="bookingType" 
                                        id="phoneBooking" value="phone" onchange="togglePaymentFields()">
                                    <label class="form-check-label fw-bold" for="phoneBooking">
                                        📞 Phone Booking (Hold for 15 mins)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Fields (Counter Only) -->
                        <div id="paymentFields" class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3"><i class="fas fa-credit-card"></i> Payment Details</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Payment Method</label>
                                <div>
                                    @foreach($paymentMethods as $method)
                                        <div class="form-check form-check-lg">
                                            <input class="form-check-input" type="radio" name="paymentMethod" 
                                                id="payment_{{ $method['value'] }}" value="{{ $method['value'] }}"
                                                {{ $loop->first ? 'checked' : '' }}
                                                onchange="toggleTransactionIdField()">
                                            <label class="form-check-label fw-bold" for="payment_{{ $method['value'] }}">
                                                <i class="{{ $method['icon'] }}"></i> {{ $method['label'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Transaction ID Field (for non-cash payments) -->
                            <div class="mb-3" id="transactionIdField" style="display: none;">
                                <label class="form-label fw-bold">Transaction ID / Reference Number</label>
                                <input type="text" class="form-control form-control-lg" id="transactionId" 
                                    placeholder="e.g., TXN123456789 or Card Last 4 Digits" maxlength="100">
                                <small class="form-text text-muted">Required for non-cash payments</small>
                            </div>

                            <div class="mb-3" id="amountReceivedField">
                                <label class="form-label fw-bold">Amount Received (PKR)</label>
                                <input type="number" class="form-control form-control-lg" id="amountReceived" 
                                    min="0" step="0.01" placeholder="0.00" value="0" onchange="calculateReturn()">
                            </div>
                            <div id="returnDiv" style="display: none;">
                                <div class="alert alert-success border-3 mb-0 py-3">
                                    <h5 class="mb-0">
                                        <strong>💰 Return: PKR <span id="returnAmount">0.00</span></strong>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-bold"><i class="fas fa-sticky-note"></i> Notes (Optional)</label>
                            <textarea class="form-control" id="notes" rows="3" maxlength="500" 
                                placeholder="Add any special notes..."></textarea>
                            <small class="form-text text-muted">Max 500 characters</small>
                        </div>

                        <!-- Passenger Information Section -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-users"></i> Passenger Information</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addPassengerBtn" onclick="addExtraPassenger()" style="display: none;">
                                    <i class="fas fa-plus-circle"></i> Add Extra Passenger
                                </button>
                            </div>
                            <p class="text-muted small mb-3"><strong>Required:</strong> One passenger per selected seat. <strong>Optional:</strong> Add extra passengers using the button.</p>
                            <div id="passengerInfoContainer"></div>
                        </div>

                        <!-- Confirm Button -->
                        <button class="btn btn-success btn-lg w-100 fw-bold py-3" onclick="confirmBooking()" id="confirmBtn">
                            <i class="fas fa-check-circle"></i> Confirm Booking
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Trip Passengers List (4 columns) -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-list-check"></i> Trip Passengers (Booked Seats)
                        </h6>
                    </div>
                    <div class="card-body" style="max-height: 85vh; overflow-y: auto;">
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
                <button type="button" class="btn btn-outline-primary btn-lg flex-grow-1 fw-bold" onclick="selectGender('male')">
                    👨 Male
                </button>
                <button type="button" class="btn btn-outline-danger btn-lg flex-grow-1 fw-bold" onclick="selectGender('female')">
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
                    <p class="mb-2"><strong>Seats:</strong> <span id="bookedSeats" class="badge bg-info ms-2"></span></p>
                    <p class="mb-0"><strong>Status:</strong> <span id="bookingStatus" class="badge bg-success ms-2"></span></p>
                </div>

                <div class="alert alert-light border-2 mb-4">
                    <h6 class="fw-bold mb-3">Fare Breakdown</h6>
                    <p class="mb-2"><strong>Total Fare:</strong> <span class="float-end">PKR <span id="confirmedFare">0.00</span></span></p>
                    <p class="mb-2"><strong>Discount:</strong> <span class="float-end">-PKR <span id="confirmedDiscount">0.00</span></span></p>
                    <p class="mb-2"><strong>Tax/Charge:</strong> <span class="float-end">+PKR <span id="confirmedTax">0.00</span></span></p>
                    <hr>
                    <p class="mb-0"><strong>Final Amount:</strong> <span class="float-end fw-bold text-success">PKR <span id="confirmedFinal">0.00</span></span></p>
                </div>

                <p><strong>Payment Method:</strong> <span class="badge bg-warning ms-2" id="paymentMethodDisplay">-</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-lg fw-bold w-100" data-bs-dismiss="modal" onclick="resetForm()">
                    <i class="fas fa-check"></i> Done
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-receipt"></i> Booking Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div id="bookingDetailsModalBody"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Bus/Driver Modal -->
<div class="modal fade" id="assignBusModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-bus"></i> Assign Bus & Driver
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div id="assignBusModalBody"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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
    // ========================================
    // STATE MANAGEMENT
    // ========================================
    let appState = {
        isAdmin: {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }},
        userTerminalId: {{ auth()->user()->terminal_id ?? 'null' }},
        terminals: [],
        routeStops: [],
        timetableStops: [],
        tripData: null,
        seatMap: {},
        selectedSeats: {},
        passengerInfo: {},  // ← New: Store passenger details
        pendingSeat: null,
        tripLoaded: false,
        fareData: null,
        baseFare: 0,
    };

    // ========================================
    // INITIALIZATION
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        fetchTerminals();
        setupWebSocket();
        togglePaymentFields(); // Initialize payment fields visibility
    });

    // ========================================
    // FETCH TERMINALS
    // ========================================
    function fetchTerminals() {
        $.ajax({
            url: '/admin/bookings/console/terminals',
            type: 'GET',
            success: function(response) {
                appState.terminals = response.terminals;
                const fromSelect = document.getElementById('fromTerminal');
                
                fromSelect.innerHTML = '<option value="">Select Terminal</option>';
                response.terminals.forEach(t => {
                    fromSelect.innerHTML += `<option value="${t.id}">${t.name} (${t.code})</option>`;
                });
                
                // Employee: Set their terminal and disable
                if (!appState.isAdmin && appState.userTerminalId) {
                    fromSelect.value = appState.userTerminalId;
                    fromSelect.disabled = true;
                    onFromTerminalChange();
                } else {
                    fromSelect.disabled = false;
                }
            },
            error: function() {
                alert('Failed to load terminals');
            }
        });
    }

    // ========================================
    // ON FROM TERMINAL CHANGE
    // ========================================
    document.getElementById('fromTerminal')?.addEventListener('change', onFromTerminalChange);

    function onFromTerminalChange() {
        const fromTerminalId = document.getElementById('fromTerminal').value;
        document.getElementById('toTerminal').value = '';
        document.getElementById('departureTime').innerHTML = '<option value="">Select Departure Time</option>';
        document.getElementById('toTerminal').disabled = true;
        document.getElementById('departureTime').disabled = true;
        
        if (fromTerminalId) {
            fetchToTerminals(fromTerminalId);
            fetchFare(fromTerminalId); // Fetch fare when from terminal changes
        }
    }

    // ========================================
    // FETCH TO TERMINALS (Route Stops)
    // ========================================
    function fetchToTerminals(fromTerminalId) {
        $.ajax({
            url: '/admin/bookings/console/route-stops',
            type: 'GET',
            data: { from_terminal_id: fromTerminalId },
            success: function(response) {
                appState.routeStops = response.route_stops;
                const toSelect = document.getElementById('toTerminal');
                
                toSelect.innerHTML = '<option value="">Select Destination</option>';
                response.route_stops.forEach(stop => {
                    toSelect.innerHTML += `<option value="${stop.id}">${stop.terminal.name}</option>`;
                });
                
                toSelect.disabled = false;
            },
            error: function() {
                alert('Failed to load destination terminals');
            }
        });
    }

    // ========================================
    // FETCH FARE FOR SEGMENT
    // ========================================
    function fetchFare(fromTerminalId, toTerminalId) {
        if (!fromTerminalId || !toTerminalId) {
            resetFareDisplay();
            return;
        }

        $.ajax({
            url: '/admin/bookings/console/fare',
            type: 'GET',
            data: {
                from_terminal_id: fromTerminalId,
                to_terminal_id: toTerminalId
            },
            success: function(response) {
                if (response.success) {
                    appState.fareData = response.fare;
                    appState.baseFare = response.fare.final_fare;
                    
                    // Update UI with fare info
                    document.getElementById('baseFare').value = parseFloat(response.fare.final_fare).toFixed(2);
                    
                    // Display discount info
                    if (response.fare.discount_type === 'flat') {
                        document.getElementById('discountInfo').value = `Flat: PKR ${parseFloat(response.fare.discount_value).toFixed(2)}`;
                    } else if (response.fare.discount_type === 'percent') {
                        document.getElementById('discountInfo').value = `${parseFloat(response.fare.discount_value).toFixed(0)}% Discount`;
                    } else {
                        document.getElementById('discountInfo').value = 'None';
                    }
                    
                    calculateTotalFare();
                } else {
                    alert('No fare found for this route segment');
                    resetFareDisplay();
                }
            },
            error: function(error) {
                const message = error.responseJSON?.error || 'Failed to load fare';
                alert(message);
                resetFareDisplay();
            }
        });
    }

    // ========================================
    // RESET FARE DISPLAY
    // ========================================
    function resetFareDisplay() {
        appState.fareData = null;
        appState.baseFare = 0;
        document.getElementById('baseFare').value = '';
        document.getElementById('discountInfo').value = '';
        document.getElementById('totalFare').value = '';
        calculateFinal();
    }

    // ========================================
    // ON TO TERMINAL CHANGE
    // ========================================
    document.getElementById('toTerminal')?.addEventListener('change', onToTerminalChange);

    function onToTerminalChange() {
        const fromTerminalId = document.getElementById('fromTerminal').value;
        const toTerminalId = document.getElementById('toTerminal').value;
        const date = document.getElementById('travelDate').value;
        
        document.getElementById('departureTime').innerHTML = '<option value="">Select Departure Time</option>';
        document.getElementById('departureTime').disabled = true;
        
        if (fromTerminalId && toTerminalId && date) {
            fetchDepartureTimes(fromTerminalId, toTerminalId, date);
            fetchFare(fromTerminalId, toTerminalId);
        }
    }

    // ========================================
    // FETCH DEPARTURE TIMES (Timetable Stops)
    // ========================================
    function fetchDepartureTimes(fromTerminalId, toTerminalId, date) {
        $.ajax({
            url: '/admin/bookings/console/departure-times',
            type: 'GET',
            data: {
                from_terminal_id: fromTerminalId,
                to_terminal_id: toTerminalId,
                date: date
            },
            success: function(response) {
                appState.timetableStops = response.timetable_stops;
                const timeSelect = document.getElementById('departureTime');
                
                timeSelect.innerHTML = '<option value="">Select Departure Time</option>';
                response.timetable_stops.forEach(stop => {
                    const time = new Date(stop.departure_at).toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    timeSelect.innerHTML += `<option value="${stop.id}">${time}</option>`;
                });
                
                timeSelect.disabled = false;
            },
            error: function() {
                alert('No trips available for this route and date');
            }
        });
    }

    // ========================================
    // ON TRAVEL DATE CHANGE
    // ========================================
    document.getElementById('travelDate')?.addEventListener('change', function() {
        const fromTerminalId = document.getElementById('fromTerminal').value;
        const toTerminalId = document.getElementById('toTerminal').value;
        
        if (fromTerminalId && toTerminalId) {
            fetchDepartureTimes(fromTerminalId, toTerminalId, this.value);
        }
    });

    // ========================================
    // LOAD TRIP
    // ========================================
    function loadTrip() {
        const fromTerminalId = document.getElementById('fromTerminal').value;
        const toTerminalId = document.getElementById('toTerminal').value;
        const departureTimeId = document.getElementById('departureTime').value;
        const date = document.getElementById('travelDate').value;

        if (!fromTerminalId || !toTerminalId || !departureTimeId || !date) {
            alert('Please fill all fields');
            return;
        }

        document.getElementById('loadTripBtn').disabled = true;
        
        $.ajax({
            url: '/admin/bookings/console/load-trip',
            type: 'POST',
            data: {
                from_terminal_id: fromTerminalId,
                to_terminal_id: toTerminalId,
                timetable_stop_id: departureTimeId,
                date: date,
                _token: document.querySelector('meta[name="csrf-token"]').content
            },
            success: function(response) {
                appState.tripData = response;
                appState.seatMap = response.seat_map;
                appState.tripLoaded = true;
                
                // Update trip info display
                document.getElementById('tripRoute').textContent = response.route.name;
                document.getElementById('tripDate').textContent = new Date(response.trip.departure_datetime).toLocaleDateString();
                document.getElementById('tripTime').textContent = new Date(response.trip.departure_datetime).toLocaleTimeString();
                
                // Update bus & driver section
                renderBusDriverSection(response.trip);
                renderSeatMap();
                document.getElementById('tripContent').style.display = 'block';
                document.getElementById('tripContent').scrollIntoView({ behavior: 'smooth' });
                loadTripPassengers(response.trip.id);
            },
            error: function(error) {
                const message = error.responseJSON?.error || 'Failed to load trip';
                alert(message);
            },
            complete: function() {
                document.getElementById('loadTripBtn').disabled = false;
            }
        });
    }

    // ========================================
    // RENDER SEAT MAP
    // ========================================
    function renderSeatMap() {
        const grid = document.getElementById('seatGrid');
        grid.innerHTML = '';

        for (let row = 0; row < 11; row++) {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'seat-row mb-2 d-flex gap-2';

            for (let col = 0; col < 4; col++) {
                const seatNumber = row * 4 + col + 1;
                const seat = appState.seatMap[seatNumber];

                const button = document.createElement('button');
                button.className = 'btn btn-sm';
                button.style.width = '50px';
                button.style.height = '50px';
                button.textContent = seatNumber;
                button.title = `Seat ${seatNumber} - ${seat.status}`;

                // Set color
                if (appState.selectedSeats[seatNumber]) {
                    button.className += ' bg-info text-white';
                } else if (seat.status === 'booked') {
                    button.className += ' bg-danger text-white';
                } else if (seat.status === 'held') {
                    button.className += ' bg-warning text-dark';
                } else {
                    button.className += ' bg-success text-white';
                }

                // Disable if not available
                if (seat.status === 'booked' || seat.status === 'held') {
                    button.disabled = true;
                }

                button.onclick = () => handleSeatClick(seatNumber);
                rowDiv.appendChild(button);
            }

            grid.appendChild(rowDiv);
        }
    }

    // ========================================
    // HANDLE SEAT CLICK
    // ========================================
    function handleSeatClick(seatNumber) {
        if (appState.selectedSeats[seatNumber]) {
            delete appState.selectedSeats[seatNumber];
        } else {
            appState.pendingSeat = seatNumber;
            document.getElementById('seatLabel').textContent = `Seat ${seatNumber}`;
            new bootstrap.Modal(document.getElementById('genderModal')).show();
        }
    }

    // ========================================
    // SELECT GENDER
    // ========================================
    function selectGender(gender) {
        if (appState.pendingSeat) {
            appState.selectedSeats[appState.pendingSeat] = gender;
            appState.pendingSeat = null;
        }
        bootstrap.Modal.getInstance(document.getElementById('genderModal')).hide();
        updateSeatsList();
        renderSeatMap();
    }

    // ========================================
    // UPDATE SEATS LIST
    // ========================================
    function updateSeatsList() {
        const list = document.getElementById('selectedSeatsList');
        const count = Object.keys(appState.selectedSeats).length;
        document.getElementById('seatCount').textContent = `(${count})`;

        if (count === 0) {
            list.innerHTML = '<p class="text-muted mb-0">No seats selected yet</p>';
            updatePassengerForms(); // ← Clear passenger forms
            calculateTotalFare();
            return;
        }

        let html = '';
        Object.keys(appState.selectedSeats).sort((a, b) => a - b).forEach(seat => {
            const gender = appState.selectedSeats[seat] === 'male' ? '👨 Male' : '👩 Female';
            html += `<div class="mb-2 p-2 bg-white rounded border"><strong>Seat ${seat}</strong> - ${gender}</div>`;
        });
        list.innerHTML = html;
        updatePassengerForms(); // ← Update passenger forms based on seats
        calculateTotalFare();
    }

    // ========================================
    // UPDATE PASSENGER FORMS
    // ========================================
    function updatePassengerForms() {
        const container = document.getElementById('passengerInfoContainer');
        const selectedSeats = Object.keys(appState.selectedSeats).sort((a, b) => a - b);
        
        if (selectedSeats.length === 0) {
            container.innerHTML = '';
            document.getElementById('addPassengerBtn').style.display = 'none';
            appState.passengerInfo = {};
            return;
        }

        // Initialize passengerInfo for new seats (mandatory)
        selectedSeats.forEach(seat => {
            if (!appState.passengerInfo[seat]) {
                appState.passengerInfo[seat] = {
                    type: 'mandatory',
                    seat_number: parseInt(seat),
                    name: '',
                    age: '',
                    gender: appState.selectedSeats[seat],
                    cnic: '',
                    phone: '',
                    email: ''
                };
            }
        });

        // Remove passengerInfo for deselected seats
        Object.keys(appState.passengerInfo).forEach(key => {
            if (appState.passengerInfo[key].type === 'mandatory' && !selectedSeats.includes(key)) {
                delete appState.passengerInfo[key];
            }
        });

        // Generate forms - Mandatory passengers first
        let html = '';
        
        // Render mandatory passengers
        selectedSeats.forEach(seat => {
            const info = appState.passengerInfo[seat];
            const icon = info.gender === 'male' ? '👨' : '👩';
            
            html += `
                <div class="card mb-3 border-2" style="border-color: #e9ecef;">
                    <div class="card-header" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <span>${icon} Seat ${seat}</span>
                            </h6>
                            <span class="badge bg-secondary">Required</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Name *</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.name}" 
                                    onchange="updatePassengerField(${seat}, 'name', this.value)"
                                    placeholder="Full Name" maxlength="100" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Age</label>
                                <input type="number" class="form-control form-control-sm" 
                                    value="${info.age}" 
                                    onchange="updatePassengerField(${seat}, 'age', this.value)"
                                    placeholder="Age" min="1" max="120" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">CNIC</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.cnic}" 
                                    onchange="updatePassengerField(${seat}, 'cnic', this.value)"
                                    placeholder="CNIC / ID Number" maxlength="20" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Phone</label>
                                <input type="tel" class="form-control form-control-sm" 
                                    value="${info.phone}" 
                                    onchange="updatePassengerField(${seat}, 'phone', this.value)"
                                    placeholder="Phone Number" maxlength="20">
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" class="form-control form-control-sm" 
                                    value="${info.email}" 
                                    onchange="updatePassengerField(${seat}, 'email', this.value)"
                                    placeholder="Email Address" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        // Render extra passengers (optional, with remove button)
        const extraPassengers = Object.keys(appState.passengerInfo).filter(
            key => appState.passengerInfo[key].type === 'extra'
        );

        extraPassengers.forEach((passengerId, index) => {
            const info = appState.passengerInfo[passengerId];
            
            html += `
                <div class="card mb-3 border-2 border-warning" style="border-color: #ffc107;">
                    <div class="card-header" style="background-color: #fff3cd;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-user-plus"></i> Extra Passenger ${index + 1}
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExtraPassenger('${passengerId}')">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Name *</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.name}" 
                                    onchange="updatePassengerField('${passengerId}', 'name', this.value)"
                                    placeholder="Full Name" maxlength="100" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Gender *</label>
                                <select class="form-control form-control-sm" onchange="updatePassengerField('${passengerId}', 'gender', this.value)">
                                    <option value="">Select Gender</option>
                                    <option value="male" ${info.gender === 'male' ? 'selected' : ''}>👨 Male</option>
                                    <option value="female" ${info.gender === 'female' ? 'selected' : ''}>👩 Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Age</label>
                                <input type="number" class="form-control form-control-sm" 
                                    value="${info.age}" 
                                    onchange="updatePassengerField('${passengerId}', 'age', this.value)"
                                    placeholder="Age" min="1" max="120">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">CNIC</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.cnic}" 
                                    onchange="updatePassengerField('${passengerId}', 'cnic', this.value)"
                                    placeholder="CNIC / ID Number" maxlength="20">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Phone</label>
                                <input type="tel" class="form-control form-control-sm" 
                                    value="${info.phone}" 
                                    onchange="updatePassengerField('${passengerId}', 'phone', this.value)"
                                    placeholder="Phone Number" maxlength="20">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" class="form-control form-control-sm" 
                                    value="${info.email}" 
                                    onchange="updatePassengerField('${passengerId}', 'email', this.value)"
                                    placeholder="Email Address" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('addPassengerBtn').style.display = selectedSeats.length > 0 ? 'inline-block' : 'none';
    }

    // ========================================
    // UPDATE PASSENGER FIELD
    // ========================================
    function updatePassengerField(key, field, value) {
        if (appState.passengerInfo[key]) {
            appState.passengerInfo[key][field] = value;
        }
    }

    // ========================================
    // REMOVE EXTRA PASSENGER
    // ========================================
    function removeExtraPassenger(passengerId) {
        delete appState.passengerInfo[passengerId];
        updatePassengerForms();
    }

    // ========================================
    // ADD PASSENGER FORM (Legacy - now calls addExtraPassenger)
    // ========================================
    function addPassengerForm() {
        addExtraPassenger();
    }

    // ========================================
    // VALIDATE PASSENGER INFORMATION
    // ========================================
    function validatePassengerInfo() {
        const selectedSeats = Object.keys(appState.selectedSeats).sort((a, b) => a - b);
        
        // Validate mandatory passengers
        for (let seat of selectedSeats) {
            const info = appState.passengerInfo[seat];
            if (!info || !info.name || info.name.trim() === '') {
                alert(`Please enter passenger name for Seat ${seat}`);
                return false;
            }
        }

        // Validate extra passengers if any
        const extraPassengers = Object.keys(appState.passengerInfo).filter(
            key => appState.passengerInfo[key].type === 'extra'
        );

        for (let passengerId of extraPassengers) {
            const info = appState.passengerInfo[passengerId];
            if (!info.name || info.name.trim() === '') {
                alert(`Extra Passenger: Please enter name`);
                return false;
            }
            if (!info.gender || info.gender === '') {
                alert(`Extra Passenger: Please select gender`);
                return false;
            }
        }
        
        return true;
    }

    // ========================================
    // CALCULATE TOTAL FARE (based on seat count)
    // ========================================
    function calculateTotalFare() {
        const seatCount = Object.keys(appState.selectedSeats).length;
        const baseFare = appState.baseFare || 0;
        const totalFare = baseFare * seatCount;
        
        document.getElementById('totalFare').value = totalFare.toFixed(2);
        calculateFinal();
    }

    // ========================================
    // CALCULATE FINAL AMOUNT
    // ========================================
    function calculateFinal() {
        const fare = parseFloat(document.getElementById('totalFare').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const final = fare + tax;
        document.getElementById('finalAmount').textContent = final.toFixed(2);
        calculateReturn();
    }

    // ========================================
    // CALCULATE RETURN
    // ========================================
    function calculateReturn() {
        const final = parseFloat(document.getElementById('finalAmount').textContent);
        const received = parseFloat(document.getElementById('amountReceived').value) || 0;
        const returnDiv = document.getElementById('returnDiv');
        
        if (received > 0) {
            document.getElementById('returnAmount').textContent = Math.max(0, received - final).toFixed(2);
            returnDiv.style.display = 'block';
        } else {
            returnDiv.style.display = 'none';
        }
    }

    // ========================================
    // TOGGLE TRANSACTION ID FIELD
    // ========================================
    function toggleTransactionIdField() {
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value || 'cash';
        const transactionIdField = document.getElementById('transactionIdField');
        const transactionIdInput = document.getElementById('transactionId');
        const amountReceivedField = document.getElementById('amountReceivedField');
        const amountReceivedInput = document.getElementById('amountReceived');
        
        if (paymentMethod === 'cash') {
            // Cash payment: show Amount Received, hide Transaction ID
            if (transactionIdField) {
                transactionIdField.style.display = 'none';
                transactionIdInput.required = false;
                transactionIdInput.value = '';
            }
            if (amountReceivedField) {
                amountReceivedField.style.display = 'block';
                amountReceivedInput.required = true;
            }
        } else {
            // Non-cash payment: show Transaction ID, hide Amount Received
            if (transactionIdField) {
                transactionIdField.style.display = 'block';
                transactionIdInput.required = true;
            }
            if (amountReceivedField) {
                amountReceivedField.style.display = 'none';
                amountReceivedInput.required = false;
                amountReceivedInput.value = '0';
            }
        }
    }

    // ========================================
    // TOGGLE PAYMENT FIELDS
    // ========================================
    function togglePaymentFields() {
        const isCounter = document.getElementById('counterBooking').checked;
        document.getElementById('paymentFields').style.display = isCounter ? 'block' : 'none';
        
        if (!isCounter) {
            // Clear transaction ID if switching to phone booking
            document.getElementById('transactionId').value = '';
        }
        
        toggleTransactionIdField();
    }

    // ========================================
    // CONFIRM BOOKING
    // ========================================
    function confirmBooking() {
        const selectedSeats = Object.keys(appState.selectedSeats);
        
        if (selectedSeats.length === 0) {
            alert('Please select at least one seat');
            return;
        }

        // Validate passenger information
        if (!validatePassengerInfo()) {
            return;
        }

        if (!appState.baseFare || appState.baseFare <= 0) {
            alert('Fare not loaded. Please select destination first.');
            return;
        }

        const isCounter = document.getElementById('counterBooking').checked;
        const final = parseFloat(document.getElementById('finalAmount').textContent);
        const received = parseFloat(document.getElementById('amountReceived').value) || 0;
        const paymentMethod = isCounter 
            ? document.querySelector('input[name="paymentMethod"]:checked').value 
            : 'cash';

        if (isCounter && paymentMethod === 'cash' && received < final) {
            alert('Insufficient amount received from customer');
            return;
        }

        // Create passengers array with detailed information
        const passengers = selectedSeats.map(seat => {
            const info = appState.passengerInfo[seat];
            return {
                seat_number: parseInt(seat),
                name: info.name || `Passenger - Seat ${seat}`,
                age: info.age || null,
                gender: info.gender,
                cnic: info.cnic || null,
                phone: info.phone || null,
                email: info.email || null
            };
        });

        // Add extra passengers to the array
        const extraPassengers = Object.keys(appState.passengerInfo).filter(
            key => appState.passengerInfo[key].type === 'extra'
        );

        extraPassengers.forEach(passengerId => {
            const info = appState.passengerInfo[passengerId];
            passengers.push({
                passenger_id: info.passenger_id,
                seat_number: null, // Extra passengers not tied to seats
                name: info.name,
                age: info.age || null,
                gender: info.gender,
                cnic: info.cnic || null,
                phone: info.phone || null,
                email: info.email || null
            });
        });

        const totalFare = parseFloat(document.getElementById('totalFare').value);
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const farePerSeat = selectedSeats.length > 0 ? totalFare / selectedSeats.length : 0;

        document.getElementById('confirmBtn').disabled = true;

        $.ajax({
            url: '/admin/bookings',
            type: 'POST',
            data: {
                trip_id: appState.tripData.trip.id,
                from_terminal_id: document.getElementById('fromTerminal').value,
                to_terminal_id: document.getElementById('toTerminal').value,
                seat_numbers: selectedSeats.map(Number),
                passengers: JSON.stringify(passengers),
                channel: isCounter ? 'counter' : 'phone',
                payment_method: paymentMethod,
                amount_received: paymentMethod === 'cash' && isCounter ? received : null,
                fare_per_seat: farePerSeat,
                total_fare: totalFare,
                discount_amount: 0,
                tax_amount: tax,
                final_amount: final,
                notes: document.getElementById('notes').value,
                transaction_id: paymentMethod !== 'cash' && isCounter ? document.getElementById('transactionId').value : null,
                _token: document.querySelector('meta[name="csrf-token"]').content
            },
            success: function(response) {
                const booking = response.booking;
                document.getElementById('bookingNumber').textContent = booking.booking_number;
                document.getElementById('bookedSeats').textContent = booking.seats.join(', ');
                document.getElementById('bookingStatus').textContent = booking.status === 'hold' ? 'On Hold' : 'Confirmed';
                document.getElementById('confirmedFare').textContent = parseFloat(booking.total_fare).toFixed(2);
                document.getElementById('confirmedDiscount').textContent = '0.00';
                document.getElementById('confirmedTax').textContent = parseFloat(booking.tax_amount).toFixed(2);
                document.getElementById('confirmedFinal').textContent = parseFloat(booking.final_amount).toFixed(2);
                document.getElementById('paymentMethodDisplay').textContent = booking.payment_method || 'N/A';

                new bootstrap.Modal(document.getElementById('successModal')).show();
            },
            error: function(error) {
                const message = error.responseJSON?.error || 'Failed to create booking';
                alert(message);
            },
            complete: function() {
                document.getElementById('confirmBtn').disabled = false;
            }
        });
    }

    // ========================================
    // RESET FORM
    // ========================================
    function resetForm() {
        appState.selectedSeats = {};
        appState.passengerInfo = {};  // ← Clear passenger info
        appState.tripLoaded = false;
        appState.fareData = null;
        appState.baseFare = 0;
        document.getElementById('tripContent').style.display = 'none';
        document.getElementById('baseFare').value = '';
        document.getElementById('discountInfo').value = '';
        document.getElementById('totalFare').value = '';
        document.getElementById('tax').value = '0';
        document.getElementById('amountReceived').value = '0';
        document.getElementById('notes').value = '';
        document.getElementById('finalAmount').textContent = '0.00';
        document.getElementById('departureTime').value = '';
        updateSeatsList();
    }

    // ========================================
    // SETUP WEBSOCKET
    // ========================================
    function setupWebSocket() {
        if (!window.Echo) return;

        window.Echo.connector.options.auth.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
    }

    // ========================================
    // LOAD TRIP PASSENGERS
    // ========================================
    function loadTripPassengers(tripId) {
        $.ajax({
            url: `/admin/bookings/console/trip-passengers/${tripId}`,
            type: 'GET',
            success: function(response) {
                const passengersList = document.getElementById('tripPassengersList');
                passengersList.innerHTML = ''; // Clear previous content

                if (response.length === 0) {
                    passengersList.innerHTML = '<p class="text-muted text-center py-4"><i class="fas fa-inbox"></i><br>No passengers booked for this trip yet.</p>';
                    return;
                }

                const table = document.createElement('table');
                table.className = 'table table-striped table-bordered table-hover table-sm';
                table.style.width = '100%';
                table.style.fontSize = '0.88rem';

                const headerRow = document.createElement('tr');
                headerRow.className = '';
                headerRow.innerHTML = `
                    <th style="width: 8%;">Seat</th>
                    <th style="width: 18%;">Passenger</th>
                    <th style="width: 10%;">Route</th>
                    <th style="width: 12%;">From</th>
                    <th style="width: 12%;">To</th>
                    <th style="width: 8%;">Gender</th>
                    <th style="width: 8%;">Booking #</th>
                    <th style="width: 8%;">Status</th>
                `;
                table.appendChild(headerRow);

                response.forEach(passenger => {
                    const row = document.createElement('tr');
                    const genderIcon = passenger.gender === 'male' ? 'Male' : passenger.gender === 'female' ? 'Female' : 'Unknown';
                    const statusBadgeClass = passenger.status === 'confirmed' ? 'bg-success' : 
                                            passenger.status === 'hold' ? 'bg-warning' :
                                            passenger.status === 'checked_in' ? 'bg-info' :
                                            passenger.status === 'boarded' ? 'bg-primary' : 'bg-secondary';
                    const channelIcon = passenger.channel === 'counter' ? '🏪' :
                                       passenger.channel === 'phone' ? '📞' :
                                       passenger.channel === 'online' ? '🌐' : '❓';

                    row.innerHTML = `
                        <td class="text-center fw-bold"><span class="badge bg-info">${passenger.seat_number || 'N/A'}</span></td>
                        <td>
                            <div class="fw-bold">${passenger.name || 'N/A'}</div>
                            <small class="text-muted">${passenger.phone || 'No phone'}</small>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary">${passenger.from_code} → ${passenger.to_code}</span></td>
                        <td>
                            <small><strong>${passenger.from_stop}</strong></small>
                        </td>
                        <td>
                            <small><strong>${passenger.to_stop}</strong></small>
                        </td>
                        <td class="text-center">${genderIcon}</td>
                        <td class="text-center"><small class="badge bg-light text-dark">#${passenger.booking_number}</small></td>
                        <td class="text-center">
                            <span class="badge ${statusBadgeClass}">${passenger.status}</span>
                            <br>
                            <button class="btn btn-link btn-sm text-primary p-0 mt-1" onclick="viewPassengerBooking(${passenger.booking_id}, '${passenger.booking_number}')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    `;
                    table.appendChild(row);
                });

                passengersList.appendChild(table);
            },
            error: function() {
                alert('Failed to load trip passengers');
            }
        });
    }

    // ========================================
    // VIEW PASSENGER BOOKING DETAILS
    // ========================================
    function viewPassengerBooking(bookingId, bookingNumber) {
        $.ajax({
            url: `/admin/bookings/console/booking-details/${bookingId}`,
            type: 'GET',
            success: function(response) {
                const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                const modalBody = document.getElementById('bookingDetailsModalBody');
                modalBody.innerHTML = ''; // Clear previous content

                if (response.success) {
                    const booking = response.booking;
                    const passengers = booking.passengers;
                    const seats = booking.seats;
                    const totalFare = booking.total_fare;
                    const discount = booking.discount_amount;
                    const tax = booking.tax_amount;
                    const finalAmount = booking.final_amount;
                    const paymentMethod = booking.payment_method;
                    const notes = booking.notes;
                    const transactionId = booking.transaction_id;
                    const channel = booking.channel;
                    const status = booking.status;
                    const createdAt = booking.created_at;
                    const updatedAt = booking.updated_at;

                    modalBody.innerHTML = `
                        <div class="mb-3">
                            <h5 class="fw-bold">Booking #${bookingNumber}</h5>
                            <p class="text-muted">Status: <span class="badge ${status === 'hold' ? 'bg-warning' : status === 'confirmed' ? 'bg-success' : 'bg-info'}">${status}</span></p>
                            <p class="text-muted">Channel: <span class="badge ${channel === 'counter' ? 'bg-primary' : channel === 'phone' ? 'bg-info' : 'bg-secondary'}">${channel}</span></p>
                            <p class="text-muted">Payment Method: <span class="badge ${paymentMethod === 'cash' ? 'bg-success' : 'bg-warning'}">${paymentMethod}</span></p>
                            <p class="text-muted">Total Fare: PKR <span class="fw-bold text-success">${totalFare.toFixed(2)}</span></p>
                            <p class="text-muted">Discount: PKR <span class="text-danger">-${discount.toFixed(2)}</span></p>
                            <p class="text-muted">Tax/Charge: PKR <span class="text-success">+${tax.toFixed(2)}</span></p>
                            <p class="text-muted">Final Amount: PKR <span class="fw-bold text-success">${finalAmount.toFixed(2)}</span></p>
                            <p class="text-muted">Notes: ${notes || 'N/A'}</p>
                            <p class="text-muted">Transaction ID: ${transactionId || 'N/A'}</p>
                            <p class="text-muted">Created At: ${new Date(createdAt).toLocaleString()}</p>
                            <p class="text-muted">Updated At: ${new Date(updatedAt).toLocaleString()}</p>
                        </div>
                        <h6 class="fw-bold mb-2">Passengers:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Seat</th>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>CNIC</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${passengers.map(p => `
                                        <tr>
                                            <td>${p.seat_number || 'N/A'}</td>
                                            <td>${p.name || 'N/A'}</td>
                                            <td>${p.age || 'N/A'}</td>
                                            <td>${p.gender === 'male' ? '👨 Male' : p.gender === 'female' ? '👩 Female' : 'N/A'}</td>
                                            <td>${p.cnic || 'N/A'}</td>
                                            <td>${p.phone || 'N/A'}</td>
                                            <td>${p.email || 'N/A'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                    modal.show();
                } else {
                    alert('Failed to load booking details');
                }
            },
            error: function() {
                alert('Failed to load booking details');
            }
        });
    }

    // ========================================
    // RENDER BUS & DRIVER SECTION
    // ========================================
    function renderBusDriverSection(trip) {
        const busDriverSection = document.getElementById('busDriverSection');
        busDriverSection.innerHTML = ''; // Clear previous content

        if (!trip.bus_id) {
            busDriverSection.innerHTML = `
                <div class="alert alert-warning text-center py-3 mb-0">
                    <p class="mb-2"><strong>Bus and Driver not assigned for this trip.</strong></p>
                    <button class="btn btn-primary btn-sm" onclick="openAssignBusModal(${trip.id})">
                        <i class="fas fa-bus"></i> Assign Bus & Driver
                    </button>
                </div>
            `;
            return;
        }

        // Bus assigned - show details
        busDriverSection.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-left-primary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="fas fa-bus text-primary"></i> Bus Details</h6>
                            <p class="mb-2">
                                <small class="text-muted">Bus Name</small><br>
                                <strong>${trip.bus?.name || 'N/A'}</strong>
                            </p>
                            <p class="mb-2">
                                <small class="text-muted">Registration Number</small><br>
                                <strong>${trip.bus?.registration_number || 'N/A'}</strong>
                            </p>
                            <p class="mb-2">
                                <small class="text-muted">Model</small><br>
                                <strong>${trip.bus?.model || 'N/A'}</strong>
                            </p>
                            <p class="mb-2">
                                <small class="text-muted">Color</small><br>
                                <strong>${trip.bus?.color || 'N/A'}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-left-success shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="fas fa-user-tie text-success"></i> Driver Details</h6>
                            <p class="mb-2">
                                <small class="text-muted">Driver Name</small><br>
                                <strong>${trip.driver_name || 'N/A'}</strong>
                            </p>
                            <p class="mb-2">
                                <small class="text-muted">Driver Phone</small><br>
                                <strong>${trip.driver_phone || 'N/A'}</strong>
                            </p>
                            <p class="mb-2">
                                <small class="text-muted">Driver CNIC</small><br>
                                <strong>${trip.driver_cnic || 'N/A'}</strong>
                            </p>
                            <p class="mb-0">
                                <small class="text-muted">Driver License</small><br>
                                <strong>${trip.driver_license || 'N/A'}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <button class="btn btn-outline-primary btn-sm" onclick="openAssignBusModal(${trip.id})">
                    <i class="fas fa-edit"></i> Change Bus & Driver
                </button>
            </div>
        `;
    }

    // ========================================
    // OPEN ASSIGN BUS MODAL
    // ========================================
    function openAssignBusModal(tripId) {
        // Fetch list of available buses
        $.ajax({
            url: '/admin/bookings/console/list-buses',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const buses = response.buses;
                    let busesHtml = '<option value="">-- Select a Bus --</option>';
                    buses.forEach(bus => {
                        busesHtml += `<option value="${bus.id}">${bus.name} (${bus.registration_number})</option>`;
                    });

                    const modalBody = document.getElementById('assignBusModalBody');
                    modalBody.innerHTML = `
                        <form id="assignBusForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-bus"></i> Select Bus
                                </label>
                                <select id="busSelect" class="form-select form-select-lg" required>
                                    ${busesHtml}
                                </select>
                                <small class="text-muted d-block mt-2">Choose a bus to assign to this trip</small>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3"><i class="fas fa-user-tie"></i> Driver Information</h6>

                            <div class="mb-3">
                                <label class="form-label">Driver Name <span class="text-danger">*</span></label>
                                <input type="text" id="driverName" class="form-control" placeholder="Enter driver name" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Driver Phone <span class="text-danger">*</span></label>
                                    <input type="tel" id="driverPhone" class="form-control" placeholder="03001234567" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Driver CNIC <span class="text-danger">*</span></label>
                                    <input type="text" id="driverCnic" class="form-control" placeholder="12345-6789012-3" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Driver License <span class="text-danger">*</span></label>
                                <input type="text" id="driverLicense" class="form-control" placeholder="License number" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Driver Address</label>
                                <textarea id="driverAddress" class="form-control" rows="2" placeholder="Enter driver address"></textarea>
                            </div>

                            <input type="hidden" id="tripIdInput" value="${tripId}">
                        </form>
                    `;

                    const modal = new bootstrap.Modal(document.getElementById('assignBusModal'));
                    modal.show();

                    // Handle confirm button
                    document.getElementById('confirmAssignBusBtn').onclick = () => {
                        const busId = document.getElementById('busSelect').value;
                        const driverName = document.getElementById('driverName').value;
                        const driverPhone = document.getElementById('driverPhone').value;
                        const driverCnic = document.getElementById('driverCnic').value;
                        const driverLicense = document.getElementById('driverLicense').value;
                        const driverAddress = document.getElementById('driverAddress').value;

                        if (!busId || !driverName || !driverPhone || !driverCnic || !driverLicense) {
                            alert('Please fill all required fields!');
                            return;
                        }

                        // Submit to backend
                        $.ajax({
                            url: `/admin/bookings/console/assign-bus-driver/${tripId}`,
                            type: 'POST',
                            data: {
                                bus_id: busId,
                                driver_name: driverName,
                                driver_phone: driverPhone,
                                driver_cnic: driverCnic,
                                driver_license: driverLicense,
                                driver_address: driverAddress,
                                _token: document.querySelector('meta[name="csrf-token"]').content
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('Bus and Driver assigned successfully!');
                                    modal.hide();
                                    // Reload trip data
                                    const tripId = appState.currentTrip.id;
                                    loadTrip();
                                } else {
                                    alert('Error: ' + (response.error || 'Failed to assign'));
                                }
                            },
                            error: function(error) {
                                console.error('Error:', error);
                                alert('Failed to assign bus and driver');
                            }
                        });
                    };
                } else {
                    alert('Failed to load buses list');
                }
            },
            error: function() {
                alert('Failed to fetch buses');
            }
        });
    }

    // ========================================
    // ASSIGN DRIVER (OLD - DEPRECATED)
    // ========================================
    function assignDriver(tripId) {
        // Now handled in assignBus modal
    }
</script>
@endsection


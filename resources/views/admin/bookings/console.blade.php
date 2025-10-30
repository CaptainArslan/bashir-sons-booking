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
            <!-- Seat Map Section -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
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
            </div>

            <!-- Booking Summary Section -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-clipboard-list"></i> Booking Summary
                        </h6>
                    </div>
                    <div class="card-body" style="max-height: 80vh; overflow-y: auto;">
                        <!-- Trip Info -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-2">Trip Details</h6>
                            <p class="mb-1"><small><strong>Route:</strong> <span id="tripRoute">-</span></small></p>
                            <p class="mb-1"><small><strong>Date:</strong> <span id="tripDate">-</span></small></p>
                            <p class="mb-0"><small><strong>Time:</strong> <span id="tripTime">-</span></small></p>
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

                            <div class="mb-3">
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
                            <h6 class="fw-bold mb-3"><i class="fas fa-users"></i> Passenger Information</h6>
                            <p class="text-muted small mb-3">Add detailed information for each passenger</p>
                            <div id="passengerInfoContainer"></div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addPassengerBtn" onclick="addPassengerForm()" style="display: none;">
                                <i class="fas fa-plus-circle"></i> Add Passenger
                            </button>
                        </div>

                        <!-- Confirm Button -->
                        <button class="btn btn-success btn-lg w-100 fw-bold py-3" onclick="confirmBooking()" id="confirmBtn">
                            <i class="fas fa-check-circle"></i> Confirm Booking
                        </button>
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
                
                renderSeatMap();
                document.getElementById('tripContent').style.display = 'block';
                document.getElementById('tripContent').scrollIntoView({ behavior: 'smooth' });
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
            return;
        }

        // Initialize passengerInfo for new seats
        selectedSeats.forEach(seat => {
            if (!appState.passengerInfo[seat]) {
                appState.passengerInfo[seat] = {
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
        Object.keys(appState.passengerInfo).forEach(seat => {
            if (!selectedSeats.includes(seat)) {
                delete appState.passengerInfo[seat];
            }
        });

        // Generate forms
        let html = '';
        selectedSeats.forEach(seat => {
            const info = appState.passengerInfo[seat];
            const icon = info.gender === 'male' ? '👨' : '👩';
            
            html += `
                <div class="card mb-3 border-2" style="border-color: #e9ecef;">
                    <div class="card-header" style="background-color: #f8f9fa;">
                        <h6 class="mb-0">
                            <span>${icon} Seat ${seat}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="removePassenger(${seat})">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </h6>
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
                                    placeholder="Age" min="1" max="120">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">CNIC</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.cnic}" 
                                    onchange="updatePassengerField(${seat}, 'cnic', this.value)"
                                    placeholder="CNIC / ID Number" maxlength="20">
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

        container.innerHTML = html;
        document.getElementById('addPassengerBtn').style.display = selectedSeats.length > 0 ? 'inline-block' : 'none';
    }

    // ========================================
    // UPDATE PASSENGER FIELD
    // ========================================
    function updatePassengerField(seat, field, value) {
        if (appState.passengerInfo[seat]) {
            appState.passengerInfo[seat][field] = value;
        }
    }

    // ========================================
    // REMOVE PASSENGER
    // ========================================
    function removePassenger(seat) {
        if (appState.passengerInfo[seat]) {
            delete appState.passengerInfo[seat];
            updatePassengerForms();
        }
    }

    // ========================================
    // ADD PASSENGER FORM
    // ========================================
    function addPassengerForm() {
        const selectedSeats = Object.keys(appState.selectedSeats).sort((a, b) => a - b);
        alert(`You have ${selectedSeats.length} seats selected. Please fill passenger information for each seat.`);
    }

    // ========================================
    // VALIDATE PASSENGER INFORMATION
    // ========================================
    function validatePassengerInfo() {
        const selectedSeats = Object.keys(appState.selectedSeats).sort((a, b) => a - b);
        
        for (let seat of selectedSeats) {
            const info = appState.passengerInfo[seat];
            if (!info || !info.name || info.name.trim() === '') {
                alert(`Please enter passenger name for Seat ${seat}`);
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
        
        if (transactionIdField) {
            // Show field only if payment method is NOT cash
            if (paymentMethod !== 'cash') {
                transactionIdField.style.display = 'block';
                transactionIdInput.required = true;
            } else {
                transactionIdField.style.display = 'none';
                transactionIdInput.required = false;
                transactionIdInput.value = ''; // Clear if hiding
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

        if (isCounter && received < final) {
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

        const paymentMethod = isCounter 
            ? document.querySelector('input[name="paymentMethod"]:checked').value 
            : 'cash';

        const totalFare = parseFloat(document.getElementById('totalFare').value);
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const farePerSeat = selectedSeats.length > 0 ? totalFare / selectedSeats.length : 0;

        const transactionId = isCounter 
            ? document.getElementById('transactionId').value 
            : null;

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
                amount_received: isCounter ? received : null,
                fare_per_seat: farePerSeat,
                total_fare: totalFare,
                discount_amount: 0,
                tax_amount: tax,
                final_amount: final,
                notes: document.getElementById('notes').value,
                transaction_id: transactionId,
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
</script>
@endsection


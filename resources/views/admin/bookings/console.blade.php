@extends('admin.layouts.app')

@section('title', 'Booking Console')

@section('content')
<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Booking Console - Live Seat Map</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Terminal -->
                <div class="col-md-2">
                    <label class="form-label">Terminal</label>
                    <select class="form-select" id="terminal" disabled>
                        <option value="">Loading...</option>
                    </select>
                </div>

                <!-- Route -->
                <div class="col-md-2">
                    <label class="form-label">Route</label>
                    <select class="form-select" id="route" disabled>
                        <option value="">Select Route</option>
                    </select>
                </div>

                <!-- Date -->
                <div class="col-md-2">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" 
                        min="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" />
                </div>

                <!-- From Stop -->
                <div class="col-md-2">
                    <label class="form-label">From Stop</label>
                    <select class="form-select" id="fromStop" disabled>
                        <option value="">Select From</option>
                    </select>
                </div>

                <!-- To Stop -->
                <div class="col-md-2">
                    <label class="form-label">To Stop</label>
                    <select class="form-select" id="toStop" disabled>
                        <option value="">Select To</option>
                    </select>
                </div>

                <!-- Load Trip Button -->
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" id="loadTripBtn" onclick="loadTrip()">
                        Load Trip
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Content (shown when trip loaded) -->
    <div id="tripContent" style="display: none;">
        <div class="row">
            <!-- Seat Map Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Seat Map (44 Seats - 4x11)</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-success me-2">🟩 Available</span>
                            <span class="badge bg-danger me-2">🟥 Booked</span>
                            <span class="badge bg-warning me-2">🟨 Held</span>
                            <span class="badge bg-info">🟦 Selected</span>
                        </div>
                        <div class="seat-grid" id="seatGrid"></div>
                    </div>
                </div>
            </div>

            <!-- Booking Summary Section -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Booking Summary</h6>
                    </div>
                    <div class="card-body">
                        <!-- Selected Seats -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Selected Seats <span id="seatCount">(0)</span></label>
                            <div class="alert alert-info" id="selectedSeatsList" style="min-height: 80px;">
                                No seats selected
                            </div>
                        </div>

                        <!-- Fare Calculation -->
                        <div class="mb-3">
                            <div class="mb-2">
                                <label class="form-label">Total Fare</label>
                                <input type="number" class="form-control" id="totalFare" 
                                    min="0" step="0.01" onchange="calculateFinal()">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Discount</label>
                                <input type="number" class="form-control" id="discount" 
                                    min="0" step="0.01" value="0" onchange="calculateFinal()">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tax</label>
                                <input type="number" class="form-control" id="tax" 
                                    min="0" step="0.01" value="0" onchange="calculateFinal()">
                            </div>
                            <div class="alert alert-primary mb-2">
                                <strong>Final Amount: PKR <span id="finalAmount">0.00</span></strong>
                            </div>
                        </div>

                        <!-- Booking Type -->
                        <div class="mb-3">
                            <label class="form-label">Booking Type</label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bookingType" 
                                        id="counterBooking" value="counter" checked onchange="togglePaymentFields()">
                                    <label class="form-check-label" for="counterBooking">Counter Booking</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bookingType" 
                                        id="phoneBooking" value="phone" onchange="togglePaymentFields()">
                                    <label class="form-check-label" for="phoneBooking">Phone Booking (Hold)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Fields (Counter Only) -->
                        <div id="paymentFields">
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod" 
                                            id="cashPayment" value="cash" checked>
                                        <label class="form-check-label" for="cashPayment">Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod" 
                                            id="cardPayment" value="card">
                                        <label class="form-check-label" for="cardPayment">Card</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Amount Received</label>
                                <input type="number" class="form-control" id="amountReceived" 
                                    min="0" step="0.01" onchange="calculateReturn()">
                            </div>
                            <div id="returnDiv" style="display: none;">
                                <div class="alert alert-success mb-3">
                                    <strong>Return: PKR <span id="returnAmount">0.00</span></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" rows="2" maxlength="500"></textarea>
                        </div>

                        <!-- Confirm Button -->
                        <button class="btn btn-success w-100" onclick="confirmBooking()" id="confirmBtn">
                            Confirm Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gender Modal -->
<div class="modal fade" id="genderModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Gender - <span id="seatLabel">Seat</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please select passenger gender:</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="selectGender('male')">
                    👨 Male
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="selectGender('female')">
                    👩 Female
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">✓ Booking Confirmed</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p><strong>Booking Number:</strong> <span id="bookingNumber"></span></p>
                    <p><strong>Seats:</strong> <span id="bookedSeats"></span></p>
                    <p><strong>Status:</strong> <span id="bookingStatus"></span></p>
                </div>
                <div class="alert alert-info">
                    <p class="mb-1"><strong>Total Fare:</strong> PKR <span id="confirmedFare"></span></p>
                    <p class="mb-1"><strong>Discount:</strong> PKR <span id="confirmedDiscount"></span></p>
                    <p class="mb-1"><strong>Tax:</strong> PKR <span id="confirmedTax"></span></p>
                    <p class="mb-0"><strong>Final Amount:</strong> PKR <span id="confirmedFinal"></span></p>
                </div>
                <p><strong>Payment Method:</strong> <span id="paymentMethodDisplay"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="resetForm()">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // State variables
    let appState = {
        terminals: [],
        routes: [],
        stops: [],
        tripData: null,
        seatMap: {},
        selectedSeats: {},
        pendingSeat: null,
        tripLoaded: false,
    };

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        fetchTerminals();
        setupWebSocket();
    });

    // Fetch terminals
    function fetchTerminals() {
        $.ajax({
            url: '/admin/bookings/console/terminals',
            type: 'GET',
            success: function(response) {
                appState.terminals = response.terminals;
                const select = document.getElementById('terminal');
                select.innerHTML = '<option value="">Select Terminal</option>';
                response.terminals.forEach(t => {
                    select.innerHTML += `<option value="${t.id}">${t.name} (${t.code})</option>`;
                });
                select.disabled = false;
            },
            error: function(error) {
                console.error('Failed to fetch terminals', error);
                alert('Failed to load terminals');
            }
        });
    }

    // Terminal change
    document.getElementById('terminal')?.addEventListener('change', function() {
        const terminalId = this.value;
        document.getElementById('route').value = '';
        document.getElementById('fromStop').value = '';
        document.getElementById('toStop').value = '';
        document.getElementById('route').disabled = true;
        
        if (terminalId) {
            fetchRoutes(terminalId);
        }
    });

    // Fetch routes
    function fetchRoutes(terminalId) {
        $.ajax({
            url: '/admin/bookings/console/routes',
            type: 'GET',
            data: { terminal_id: terminalId },
            success: function(response) {
                appState.routes = response.routes;
                const select = document.getElementById('route');
                select.innerHTML = '<option value="">Select Route</option>';
                response.routes.forEach(r => {
                    select.innerHTML += `<option value="${r.id}">${r.name} (${r.code})</option>`;
                });
                select.disabled = false;
            },
            error: function(error) {
                console.error('Failed to fetch routes', error);
            }
        });
    }

    // Route change
    document.getElementById('route')?.addEventListener('change', function() {
        const routeId = this.value;
        document.getElementById('fromStop').value = '';
        document.getElementById('toStop').value = '';
        document.getElementById('fromStop').disabled = true;
        document.getElementById('toStop').disabled = true;
        
        if (routeId) {
            fetchStops(routeId);
        }
    });

    // Fetch stops
    function fetchStops(routeId) {
        $.ajax({
            url: '/admin/bookings/console/stops',
            type: 'GET',
            data: { route_id: routeId },
            success: function(response) {
                appState.stops = response.stops;
                const fromSelect = document.getElementById('fromStop');
                const toSelect = document.getElementById('toStop');
                
                fromSelect.innerHTML = '<option value="">Select From</option>';
                toSelect.innerHTML = '<option value="">Select To</option>';
                
                response.stops.forEach(s => {
                    fromSelect.innerHTML += `<option value="${s.id}">${s.terminal.name}</option>`;
                    toSelect.innerHTML += `<option value="${s.id}">${s.terminal.name}</option>`;
                });
                
                fromSelect.disabled = false;
                toSelect.disabled = false;
            },
            error: function(error) {
                console.error('Failed to fetch stops', error);
            }
        });
    }

    // Load Trip
    function loadTrip() {
        const route = document.getElementById('route').value;
        const date = document.getElementById('date').value;
        const fromStop = document.getElementById('fromStop').value;
        const toStop = document.getElementById('toStop').value;

        if (!route || !date || !fromStop || !toStop) {
            alert('Please fill all fields');
            return;
        }

        document.getElementById('loadTripBtn').disabled = true;
        
        $.ajax({
            url: '/admin/bookings/console/load-trip',
            type: 'POST',
            data: {
                route_id: route,
                date: date,
                from_stop_id: fromStop,
                to_stop_id: toStop,
                _token: document.querySelector('meta[name="csrf-token"]').content
            },
            success: function(response) {
                appState.tripData = response;
                appState.seatMap = response.seat_map;
                appState.tripLoaded = true;
                renderSeatMap();
                document.getElementById('tripContent').style.display = 'block';
            },
            error: function(error) {
                console.error('Failed to load trip', error);
                const message = error.responseJSON?.error || 'Failed to load trip';
                alert(message);
            },
            complete: function() {
                document.getElementById('loadTripBtn').disabled = false;
            }
        });
    }

    // Render seat map
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
                    button.className += ' bg-info';
                } else if (seat.status === 'booked') {
                    button.className += ' bg-danger';
                } else if (seat.status === 'held') {
                    button.className += ' bg-warning';
                } else {
                    button.className += ' bg-success';
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

    // Handle seat click
    function handleSeatClick(seatNumber) {
        if (appState.selectedSeats[seatNumber]) {
            delete appState.selectedSeats[seatNumber];
        } else {
            appState.pendingSeat = seatNumber;
            document.getElementById('seatLabel').textContent = `Seat ${seatNumber}`;
            new bootstrap.Modal(document.getElementById('genderModal')).show();
        }
    }

    // Select gender
    function selectGender(gender) {
        if (appState.pendingSeat) {
            appState.selectedSeats[appState.pendingSeat] = gender;
            appState.pendingSeat = null;
        }
        bootstrap.Modal.getInstance(document.getElementById('genderModal')).hide();
        updateSeatsList();
        renderSeatMap();
    }

    // Update seats list
    function updateSeatsList() {
        const list = document.getElementById('selectedSeatsList');
        const count = Object.keys(appState.selectedSeats).length;
        document.getElementById('seatCount').textContent = `(${count})`;

        if (count === 0) {
            list.innerHTML = 'No seats selected';
            return;
        }

        let html = '';
        Object.keys(appState.selectedSeats).sort((a, b) => a - b).forEach(seat => {
            const gender = appState.selectedSeats[seat] === 'male' ? '👨 Male' : '👩 Female';
            html += `<div>Seat ${seat} - ${gender}</div>`;
        });
        list.innerHTML = html;
    }

    // Calculate final amount
    function calculateFinal() {
        const fare = parseFloat(document.getElementById('totalFare').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const final = fare - discount + tax;
        document.getElementById('finalAmount').textContent = final.toFixed(2);
        calculateReturn();
    }

    // Calculate return
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

    // Toggle payment fields
    function togglePaymentFields() {
        const isCounter = document.getElementById('counterBooking').checked;
        document.getElementById('paymentFields').style.display = isCounter ? 'block' : 'none';
    }

    // Confirm booking
    function confirmBooking() {
        const selectedSeats = Object.keys(appState.selectedSeats);
        
        if (selectedSeats.length === 0) {
            alert('Please select at least one seat');
            return;
        }

        const fare = parseFloat(document.getElementById('totalFare').value);
        if (!fare || fare <= 0) {
            alert('Please enter total fare');
            return;
        }

        const isCounter = document.getElementById('counterBooking').checked;
        const final = parseFloat(document.getElementById('finalAmount').textContent);
        const received = parseFloat(document.getElementById('amountReceived').value) || 0;

        if (isCounter && received < final) {
            alert('Insufficient amount received from customer');
            return;
        }

        const passengers = selectedSeats.map(seat => ({
            name: `Passenger - Seat ${seat}`,
            gender: appState.selectedSeats[seat]
        }));

        const paymentMethod = isCounter 
            ? document.querySelector('input[name="paymentMethod"]:checked').value 
            : 'none';

        document.getElementById('confirmBtn').disabled = true;

        $.ajax({
            url: '/admin/bookings',
            type: 'POST',
            data: {
                trip_id: appState.tripData.trip.id,
                from_stop_id: appState.tripData.from_stop.id,
                to_stop_id: appState.tripData.to_stop.id,
                seat_numbers: selectedSeats.map(Number),
                passengers: JSON.stringify(passengers),
                channel: isCounter ? 'counter' : 'phone',
                payment_method: paymentMethod,
                amount_received: isCounter ? received : null,
                total_fare: fare,
                discount_amount: parseFloat(document.getElementById('discount').value) || 0,
                tax_amount: parseFloat(document.getElementById('tax').value) || 0,
                final_amount: final,
                notes: document.getElementById('notes').value,
                terminal_id: document.getElementById('terminal').value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            },
            success: function(response) {
                const booking = response.booking;
                document.getElementById('bookingNumber').textContent = booking.booking_number;
                document.getElementById('bookedSeats').textContent = booking.seats.join(', ');
                document.getElementById('bookingStatus').textContent = booking.status === 'hold' ? 'On Hold' : 'Confirmed';
                document.getElementById('confirmedFare').textContent = parseFloat(booking.total_fare).toFixed(2);
                document.getElementById('confirmedDiscount').textContent = parseFloat(booking.discount_amount).toFixed(2);
                document.getElementById('confirmedTax').textContent = parseFloat(booking.tax_amount).toFixed(2);
                document.getElementById('confirmedFinal').textContent = parseFloat(booking.final_amount).toFixed(2);
                document.getElementById('paymentMethodDisplay').textContent = booking.payment_method;

                new bootstrap.Modal(document.getElementById('successModal')).show();
            },
            error: function(error) {
                console.error('Failed to create booking', error);
                const message = error.responseJSON?.error || 'Failed to create booking';
                alert(message);
            },
            complete: function() {
                document.getElementById('confirmBtn').disabled = false;
            }
        });
    }

    // Reset form
    function resetForm() {
        appState.selectedSeats = {};
        appState.tripLoaded = false;
        document.getElementById('tripContent').style.display = 'none';
        document.getElementById('totalFare').value = '';
        document.getElementById('discount').value = '0';
        document.getElementById('tax').value = '0';
        document.getElementById('amountReceived').value = '';
        document.getElementById('notes').value = '';
        document.getElementById('finalAmount').textContent = '0.00';
        updateSeatsList();
    }

    // Setup WebSocket
    function setupWebSocket() {
        if (!window.Echo) return;

        window.Echo.connector.options.auth.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
    }
</script>
@endsection


@extends('frontend.layouts.app')

@section('title', 'Select Seats')

@section('styles')
    <style>
        .booking-progress {
            background-color: #0d6efd;
            border-radius: 0.5rem;
            padding: 1.5rem;
            color: white;
            margin-bottom: 2rem;
        }

        .progress-step {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .progress-step.active {
            color: #ffd700;
        }

        .seat-btn {
            width: 3.8rem;
            height: 3.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            padding: 0;
            line-height: 1;
            border: 3px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .available {
            background-color: #e9ecef;
            color: #495057;
            border-color: #dee2e6;
        }

        .available:hover {
            background-color: #dee2e6;
            border-color: #adb5bd;
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .selected {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
        }

        .booked-male {
            background-color: #0dcaf0;
            color: #fff;
            border-color: #0dcaf0;
            cursor: not-allowed;
            position: relative;
        }

        .booked-male::before {
            content: '👨';
            position: absolute;
            font-size: 0.7rem;
            top: 2px;
            right: 2px;
        }

        .booked-female {
            background-color: #e83e8c;
            color: #fff;
            border-color: #e83e8c;
            cursor: not-allowed;
            position: relative;
        }

        .booked-female::before {
            content: '👩';
            position: absolute;
            font-size: 0.7rem;
            top: 2px;
            right: 2px;
        }

        .held {
            background-color: #ffc107;
            color: #000;
            border-color: #ffc107;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .seat-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .aisle {
            width: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #adb5bd;
            font-size: 1.2rem;
        }

        .aisle::before {
            content: '│';
        }

        .legend-box {
            width: 1.75rem;
            height: 1.75rem;
            display: inline-block;
            border-radius: 0.5rem;
            border: 2px solid transparent;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .passenger-form {
            background-color: #ffffff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .passenger-form:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }

        .summary-card {
            position: sticky;
            top: 100px;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .summary-card .card-header {
            background-color: #0d6efd;
            border: none;
        }

        .route-info-banner {
            background-color: #0d6efd;
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .seat-btn.selected {
            animation: pulse 2s infinite;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .passenger-form {
            animation: slideIn 0.4s ease forwards;
        }

        .seat-map-container {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .driver-seat-indicator {
            text-align: center;
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .seat-gender-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.75rem;
            line-height: 1;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 1.2rem;
            height: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .seat-btn {
            position: relative;
        }

        .seat-btn.selected .seat-gender-badge {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
@endsection

@section('content')
    <section class="py-5 bg-light" style="min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <!-- Booking Progress -->
            <div class="booking-progress">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="progress-step active">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>1. Search</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="progress-step active">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>2. Select Trip</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="progress-step active">
                            <i class="bi bi-grid"></i>
                            <span>3. Select Seats</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route Info Banner -->
            <div class="route-info-banner">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <!-- Full Route Name (Small, Less Prominent) -->
                        <div class="mb-2">
                            <small class="text-white-50 d-flex align-items-center">
                                <i class="bi bi-route me-2" style="font-size: 0.9rem;"></i>
                                <span id="full-route-name" style="font-size: 0.85rem; font-weight: 400;">Loading route...</span>
                            </small>
                        </div>
                        
                        <!-- Selected Terminals (Large, Prominent) -->
                        <h3 class="mb-2 fw-bold" id="selected-terminals" style="font-size: 1.75rem;">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            <span id="from-terminal-name">Loading...</span>
                            <i class="bi bi-arrow-right mx-2" style="font-size: 1.5rem;"></i>
                            <span id="to-terminal-name">Loading...</span>
                        </h3>
                        
                        <!-- Trip Details -->
                        <div class="d-flex align-items-center gap-4 flex-wrap" id="trip-details">
                            <div>
                                <i class="bi bi-calendar-event me-2"></i>
                                <span id="trip-date">Loading...</span>
                            </div>
                            <div>
                                <i class="bi bi-people me-2"></i>
                                <span id="selected-seats-count">0</span> of {{ $passengers }} seat(s) selected
                            </div>
                            <div>
                                <i class="bi bi-bus-front me-2"></i>
                                <span id="bus-name">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('frontend.bookings.trips', request()->only(['from_terminal_id', 'to_terminal_id', 'date', 'passengers'])) }}" class="btn btn-light btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Back to Trips
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h4 class="mb-0 fw-bold">
                                <i class="bi bi-grid me-2 text-primary"></i>Select Your Seats
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="loading-spinner text-center py-5" id="loading-seats">
                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted fs-5">Loading seat map...</p>
                            </div>

                            <div id="seat-map-container" class="seat-map-container" style="display: none;">
                                <div class="driver-seat-indicator">
                                    <i class="bi bi-shield-check me-2"></i>Driver's Seat (Front)
                                </div>
                                <div id="seat-map" class="mb-4">
                                    <!-- Seat map will be rendered here -->
                                </div>

                                <!-- Legend -->
                                <div class="d-flex justify-content-center align-items-center flex-wrap gap-4 mt-4 pt-4 border-top">
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box available me-2"></span>
                                        <small class="fw-semibold">Available</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box selected me-2"></span>
                                        <small class="fw-semibold">Selected</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box booked-male me-2"></span>
                                        <small class="fw-semibold">Male Booked</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box booked-female me-2"></span>
                                        <small class="fw-semibold">Female Booked</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box held me-2"></span>
                                        <small class="fw-semibold">Held</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Information Forms -->
                    <div class="card shadow-lg border-0" id="passenger-forms-card" style="display: none;">
                        <div class="card-header bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 fw-bold">
                                    <i class="bi bi-person-fill me-2 text-primary"></i>Passenger Information
                                </h4>
                                <button type="button" class="btn btn-primary" id="addPassengerBtn" onclick="addExtraPassenger()">
                                    <i class="bi bi-plus-circle me-2"></i>Add Passenger
                                </button>
                            </div>
                            <p class="text-muted small mb-0 mt-2">
                                <strong>Required:</strong> At least 1 passenger information. Passengers will be assigned to selected seats.
                            </p>
                        </div>
                        <div class="card-body">
                            <div id="passengers-container" style="max-height: 500px; overflow-y: auto;">
                                <!-- Passenger forms will be generated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-lg border-0 summary-card">
                        <div class="card-header text-white border-0">
                            <h4 class="mb-0 fw-bold">
                                <i class="bi bi-receipt-cutoff me-2"></i>Booking Summary
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                    <span class="text-muted">Fare per seat:</span>
                                    <strong id="fare-per-seat" class="text-dark">PKR 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                    <span class="text-muted">Seats selected:</span>
                                    <strong id="seats-count" class="text-dark">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                    <span class="text-muted">Subtotal:</span>
                                    <strong id="subtotal" class="text-dark">PKR 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom" id="discount-row" style="display: none;">
                                    <span class="text-muted">Discount:</span>
                                    <strong id="discount" class="text-success">-PKR 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                    <span class="text-muted">Tax:</span>
                                    <strong id="tax" class="text-dark">PKR 0.00</strong>
                                </div>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between mb-4">
                                    <h4 class="mb-0 fw-bold">Total:</h4>
                                    <h4 class="mb-0 fw-bold text-primary" id="total">PKR 0.00</h4>
                                </div>
                            </div>

                            <button type="button" id="proceed-booking-btn" class="btn btn-lg btn-primary w-100 text-white fw-bold" disabled>
                                <i class="bi bi-check-circle me-2"></i>Confirm Booking
                            </button>
                            <small class="text-muted d-block text-center mt-3">
                                <i class="bi bi-shield-check me-1"></i>Secure payment processing
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gender Selection Modal -->
    <div class="modal fade" id="genderModal" tabindex="-1" aria-labelledby="genderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="genderModalLabel">Select Gender for Seat <span id="seatLabel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male" checked>
                        <label class="form-check-label" for="genderMale">Male</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female">
                        <label class="form-check-label" for="genderFemale">Female</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmGenderBtn" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const tripId = {{ $trip_id }};
            const fromTerminalId = {{ $from_terminal_id }};
            const toTerminalId = {{ $to_terminal_id }};
            const date = '{{ $date }}';
            const passengers = {{ $passengers }};

            let tripData = null;
            let seatMap = {};
            let selectedSeats = {}; // {seatNumber: 'male'|'female'}
            let passengerInfo = {}; // {passengerId: {name, gender, age, cnic, phone, email, type}}
            let nextPassengerId = 1;
            let pendingSeat = null;
            let fareData = null;

            // Initialize mandatory passenger
            passengerInfo['passenger_1'] = {
                type: 'mandatory',
                name: '',
                gender: '',
                age: '',
                cnic: '',
                phone: '',
                email: ''
            };

            // Load trip details
            loadTripDetails();

            function loadTripDetails() {
                $('#loading-seats').show();
                $('#seat-map-container').hide();

                $.ajax({
                    url: '{{ route('frontend.bookings.load-trip-details') }}',
                    type: 'GET',
                    data: {
                        trip_id: tripId,
                        from_terminal_id: fromTerminalId,
                        to_terminal_id: toTerminalId
                    },
                    success: function(response) {
                        tripData = response;
                        seatMap = response.seat_map;
                        fareData = response.fare;

                        // Update trip info
                        // Full route name (small, less prominent)
                        $('#full-route-name').text('Route: ' + response.trip.route_name);
                        
                        // Selected terminals (large, prominent)
                        $('#from-terminal-name').text(response.from_stop.terminal_name || 'N/A');
                        $('#to-terminal-name').text(response.to_stop.terminal_name || 'N/A');
                        
                        // Trip date and other details
                        $('#trip-date').text(new Date(response.trip.departure_date).toLocaleDateString('en-US', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        }));
                        
                        // Bus name
                        $('#bus-name').text(response.trip.bus_name || 'TBA');

                        // Render seat map
                        renderSeatMap();

                        // Initialize passenger forms
                        updatePassengerForms();

                        // Update fare display
                        updateFareDisplay();

                        $('#loading-seats').hide();
                        $('#seat-map-container').show();
                    },
                    error: function(xhr) {
                        $('#loading-seats').hide();
                        let errorMsg = 'Failed to load trip details';
                        let errorTitle = 'Error';
                        
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                            
                            // Check if it's a 2-hour restriction error
                            if (errorMsg.includes('2 hours') || errorMsg.includes('departs too soon')) {
                                errorTitle = 'Booking Not Available';
                                errorMsg = 'Online bookings must be made at least 2 hours before departure. This trip departs too soon to book online. Please visit our counter or try booking an upcoming trip.';
                            }
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: errorTitle,
                            text: errorMsg,
                            confirmButtonColor: '#ffc107',
                            confirmButtonText: 'Go Back to Trips'
                        }).then(() => {
                            window.history.back();
                        });
                    }
                });
            }

            function renderSeatMap() {
                const container = $('#seat-map');
                container.html('');

                const totalSeats = Object.keys(seatMap).length;
                const seatsPerRow = 4; // 2-2 layout (2 seats right, aisle, 2 seats left)
                const rows = Math.ceil(totalSeats / seatsPerRow);

                let seatNum = 1;
                for (let row = 0; row < rows; row++) {
                    const rowDiv = $('<div class="mb-3 d-flex justify-content-center align-items-center gap-2"></div>');

                    // Calculate seat numbers for this row
                    const rightSeat1 = (row * 4) + 3;
                    const rightSeat2 = (row * 4) + 4;
                    const leftSeat1 = (row * 4) + 1;
                    const leftSeat2 = (row * 4) + 2;

                    // Right side (2 seats) - displayed first in reverse order (4, 3)
                    if (rightSeat2 <= totalSeats && seatMap[rightSeat2]) {
                        rowDiv.append(createSeatButton(rightSeat2, seatMap[rightSeat2]));
                    }
                    if (rightSeat1 <= totalSeats && seatMap[rightSeat1]) {
                        rowDiv.append(createSeatButton(rightSeat1, seatMap[rightSeat1]));
                    }

                    // Aisle (show if there are seats on both sides or if it's not the last row)
                    const hasRightSeats = (rightSeat1 <= totalSeats && seatMap[rightSeat1]) || (rightSeat2 <= totalSeats && seatMap[rightSeat2]);
                    const hasLeftSeats = (leftSeat1 <= totalSeats && seatMap[leftSeat1]) || (leftSeat2 <= totalSeats && seatMap[leftSeat2]);
                    if (hasRightSeats && hasLeftSeats) {
                        rowDiv.append($('<div class="aisle"></div>'));
                    }
                    // Also show aisle if it's not the last row (even if one side is empty)
                    else if (row < rows - 1) {
                        rowDiv.append($('<div class="aisle"></div>'));
                    }

                    // Left side (2 seats) - displayed last in reverse order (2, 1)
                    if (leftSeat2 <= totalSeats && seatMap[leftSeat2]) {
                        rowDiv.append(createSeatButton(leftSeat2, seatMap[leftSeat2]));
                    }
                    if (leftSeat1 <= totalSeats && seatMap[leftSeat1]) {
                        rowDiv.append(createSeatButton(leftSeat1, seatMap[leftSeat1]));
                    }
                    container.append(rowDiv);
                }
            }

            function createSeatButton(seatNumber, seatInfo) {
                const button = $('<button></button>')
                    .addClass('seat-btn')
                    .text(seatNumber)
                    .attr('data-seat', seatNumber)
                    .attr('title', `Seat ${seatNumber}`);

                let genderIcon = '';

                // Determine seat status and add gender icon
                if (selectedSeats[seatNumber]) {
                    // Selected seat - show gender icon
                    button.removeClass('available').addClass('selected');
                    const selectedGender = selectedSeats[seatNumber];
                    if (selectedGender === 'male') {
                        genderIcon = '👨';
                        button.attr('title', `Seat ${seatNumber} - Selected (Male)`);
                    } else if (selectedGender === 'female') {
                        genderIcon = '👩';
                        button.attr('title', `Seat ${seatNumber} - Selected (Female)`);
                    } else {
                        button.attr('title', `Seat ${seatNumber} - Selected`);
                    }
                } else if (seatInfo.status === 'available') {
                    button.addClass('available');
                    button.attr('title', `Seat ${seatNumber} - Available`);
                } else if (seatInfo.status === 'booked') {
                    if (seatInfo.gender === 'male') {
                        button.addClass('booked-male').prop('disabled', true);
                        genderIcon = '👨';
                        button.attr('title', `Seat ${seatNumber} - Booked (Male)`);
                    } else if (seatInfo.gender === 'female') {
                        button.addClass('booked-female').prop('disabled', true);
                        genderIcon = '👩';
                        button.attr('title', `Seat ${seatNumber} - Booked (Female)`);
                    } else {
                        button.addClass('booked-male').prop('disabled', true);
                        button.attr('title', `Seat ${seatNumber} - Booked`);
                    }
                } else if (seatInfo.status === 'held') {
                    button.addClass('held').prop('disabled', true);
                    button.attr('title', `Seat ${seatNumber} - Held`);
                }

                // Add gender badge in top-right corner if gender icon is available
                if (genderIcon) {
                    const badge = $('<span></span>')
                        .addClass('seat-gender-badge')
                        .text(genderIcon);
                    button.append(badge);
                }

                // Make clickable only if available or selected
                if (seatInfo.status === 'available' || selectedSeats[seatNumber]) {
                    button.on('click', function() {
                        handleSeatClick(seatNumber);
                    });
                }

                return button;
            }

            function handleSeatClick(seatNumber) {
                // If already selected, deselect
                if (selectedSeats[seatNumber]) {
                    delete selectedSeats[seatNumber];
                    renderSeatMap();
                    updateFareDisplay();
                    updateSelectedSeatsCount();
                    return;
                }

                // Check if max passengers reached
                if (Object.keys(selectedSeats).length >= passengers) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maximum Seats Selected',
                        text: `You can only select ${passengers} seat(s). Please deselect a seat first.`,
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                // Check if seat is available
                const seat = seatMap[seatNumber];
                if (!seat || seat.status !== 'available') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Seat Not Available',
                        text: 'This seat is not available for booking.',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                // Show gender selection modal
                pendingSeat = seatNumber;
                $('#seatLabel').text(seatNumber);
                const genderModal = new bootstrap.Modal(document.getElementById('genderModal'));
                genderModal.show();
            }

            // Gender selection
            $('#confirmGenderBtn').on('click', function() {
                if (pendingSeat) {
                    const gender = $('input[name="gender"]:checked').val();
                    selectedSeats[pendingSeat] = gender;
                    pendingSeat = null;

                    bootstrap.Modal.getInstance(document.getElementById('genderModal')).hide();
                    renderSeatMap();
                    updateFareDisplay();
                    updateSelectedSeatsCount();
                    
                    // Sync passenger gender if only one passenger exists and matches seat
                    syncPassengerGenders();
                }
            });

            function updatePassengerForms() {
                const container = $('#passengers-container');
                container.html('');

                // Show passenger forms if we have selected seats or passengers
                const selectedSeatCount = Object.keys(selectedSeats).length;
                const passengerCount = Object.keys(passengerInfo).length;

                if (selectedSeatCount === 0 && passengerCount === 0) {
                    $('#passenger-forms-card').hide();
                    return;
                }

                $('#passenger-forms-card').show();

                // Sort passengers: mandatory first, then extras
                const passengers = Object.keys(passengerInfo).sort((a, b) => {
                    if (passengerInfo[a].type === 'mandatory') return -1;
                    if (passengerInfo[b].type === 'mandatory') return 1;
                    return a.localeCompare(b);
                });

                passengers.forEach(function(passengerId, index) {
                    const info = passengerInfo[passengerId];
                    const isMandatory = info.type === 'mandatory';
                    const passengerNumber = index + 1;

                    const form = `
                        <div class="passenger-form" data-passenger-id="${passengerId}" style="animation-delay: ${index * 0.1}s; border-color: ${isMandatory ? '#e9ecef' : '#ffc107'};">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi ${isMandatory ? 'bi-person-fill' : 'bi-person-plus-fill'} me-2"></i>
                                    Passenger ${passengerNumber}
                                    ${isMandatory ? '<span class="badge bg-danger ms-2">Required</span>' : ''}
                                </h5>
                                ${!isMandatory ? `
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-passenger-btn" data-passenger-id="${passengerId}">
                                        <i class="bi bi-trash me-1"></i>Remove
                                    </button>
                                ` : ''}
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg passenger-name" 
                                        data-passenger-id="${passengerId}"
                                        value="${info.name || ''}"
                                        placeholder="Enter passenger name" 
                                        maxlength="100" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-lg passenger-gender" 
                                        data-passenger-id="${passengerId}" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" ${info.gender === 'male' ? 'selected' : ''}>👨 Male</option>
                                        <option value="female" ${info.gender === 'female' ? 'selected' : ''}>👩 Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Age</label>
                                    <input type="number" class="form-control form-control-lg passenger-age" 
                                        data-passenger-id="${passengerId}"
                                        value="${info.age || ''}"
                                        min="1" max="120" 
                                        placeholder="Age">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">CNIC</label>
                                    <input type="text" class="form-control form-control-lg passenger-cnic" 
                                        data-passenger-id="${passengerId}"
                                        value="${info.cnic || ''}"
                                        placeholder="34101-1111111-1"
                                        maxlength="15">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" class="form-control form-control-lg passenger-phone" 
                                        data-passenger-id="${passengerId}"
                                        value="${info.phone || ''}"
                                        placeholder="0317-7777777"
                                        maxlength="12">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control form-control-lg passenger-email" 
                                        data-passenger-id="${passengerId}"
                                        value="${info.email || ''}"
                                        placeholder="email@example.com"
                                        maxlength="100">
                                </div>
                            </div>
                        </div>
                    `;

                    container.append(form);
                });

                // Apply input masks
                setTimeout(function() {
                    $('.passenger-cnic').each(function() {
                        if (!$(this).data('inputmask')) {
                            $(this).inputmask('99999-9999999-9', {
                                placeholder: '_',
                                clearMaskOnLostFocus: false,
                                showMaskOnHover: true,
                                showMaskOnFocus: true
                            });
                        }
                    });

                    $('.passenger-phone').each(function() {
                        if (!$(this).data('inputmask')) {
                            $(this).inputmask('9999-9999999', {
                                placeholder: '_',
                                clearMaskOnLostFocus: false,
                                showMaskOnHover: true,
                                showMaskOnFocus: true
                            });
                        }
                    });
                }, 100);

                // Update passenger info on input change
                $(document).off('input change', '.passenger-name, .passenger-gender, .passenger-age, .passenger-cnic, .passenger-phone, .passenger-email');
                $(document).on('input change', '.passenger-name, .passenger-gender, .passenger-age, .passenger-cnic, .passenger-phone, .passenger-email', function() {
                    const passengerId = $(this).data('passenger-id');
                    const field = $(this).hasClass('passenger-name') ? 'name' :
                                 $(this).hasClass('passenger-gender') ? 'gender' :
                                 $(this).hasClass('passenger-age') ? 'age' :
                                 $(this).hasClass('passenger-cnic') ? 'cnic' :
                                 $(this).hasClass('passenger-phone') ? 'phone' : 'email';
                    const value = $(this).val();
                    
                    if (passengerInfo[passengerId]) {
                        passengerInfo[passengerId][field] = value;
                        updateFareDisplay();
                    }
                });

                // Handle remove passenger button
                $(document).off('click', '.remove-passenger-btn');
                $(document).on('click', '.remove-passenger-btn', function() {
                    const passengerId = $(this).data('passenger-id');
                    removeExtraPassenger(passengerId);
                });
            }

            function syncPassengerGenders() {
                // Sync first passenger gender with first selected seat gender if only one passenger exists
                const selectedSeatNumbers = Object.keys(selectedSeats).sort((a, b) => parseInt(a) - parseInt(b));
                const passengerIds = Object.keys(passengerInfo).sort((a, b) => {
                    if (passengerInfo[a].type === 'mandatory') return -1;
                    if (passengerInfo[b].type === 'mandatory') return 1;
                    return a.localeCompare(b);
                });

                if (selectedSeatNumbers.length === 1 && passengerIds.length === 1) {
                    const seatGender = selectedSeats[selectedSeatNumbers[0]];
                    const passengerId = passengerIds[0];
                    if (passengerInfo[passengerId] && !passengerInfo[passengerId].gender) {
                        passengerInfo[passengerId].gender = seatGender;
                        updatePassengerForms();
                    }
                }
            }

            // Make addExtraPassenger globally accessible
            window.addExtraPassenger = function() {
                const timestamp = Date.now();
                const passengerId = `passenger_extra_${timestamp}`;

                passengerInfo[passengerId] = {
                    type: 'extra',
                    name: '',
                    gender: '',
                    age: '',
                    cnic: '',
                    phone: '',
                    email: ''
                };

                updatePassengerForms();

                // Scroll to newly added passenger
                setTimeout(() => {
                    const container = document.getElementById('passengers-container');
                    if (container) {
                        container.scrollIntoView({ behavior: 'smooth', block: 'end' });
                    }
                }, 100);
            };

            function removeExtraPassenger(passengerId) {
                if (passengerInfo[passengerId]?.type === 'mandatory') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Remove',
                        text: 'At least one passenger information is required.',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                delete passengerInfo[passengerId];
                updatePassengerForms();
                updateFareDisplay();
            }

            function validatePassengerInfo() {
                const passengers = Object.keys(passengerInfo);

                if (passengers.length === 0) {
                    return false;
                }

                for (let passengerId of passengers) {
                    const info = passengerInfo[passengerId];
                    if (!info.name || info.name.trim() === '') {
                        return false;
                    }
                    if (!info.gender || info.gender === '') {
                        return false;
                    }
                }

                return true;
            }

            function updateFareDisplay() {
                if (!fareData) return;

                const seatCount = Object.keys(selectedSeats).length;
                const baseFarePerSeat = parseFloat(fareData.base_fare || fareData.final_fare);
                let subtotal = baseFarePerSeat * seatCount;

                // Calculate discount amount from route discounts
                // Discount is calculated on the total subtotal amount
                let discountAmount = 0;
                if (fareData.has_discount && fareData.discount_type && fareData.discount_value !== undefined) {
                    if (fareData.discount_type === 'fixed' || fareData.discount_type === 'flat') {
                        // Fixed discount - can be per seat or per booking
                        // Assuming it's per seat based on the value structure
                        const discountValue = parseFloat(fareData.discount_value);
                        discountAmount = discountValue * seatCount;
                    } else if (fareData.discount_type === 'percentage' || fareData.discount_type === 'percent') {
                        // Percentage discount - apply to total subtotal
                        discountAmount = subtotal * (parseFloat(fareData.discount_value || 0) / 100);
                    }
                }

                const tax = 0; // No tax for online bookings initially
                const total = Math.max(0, subtotal - discountAmount + tax);

                $('#fare-per-seat').text(`${fareData.currency} ${baseFarePerSeat.toFixed(2)}`);
                $('#seats-count').text(seatCount);
                $('#subtotal').text(`${fareData.currency} ${subtotal.toFixed(2)}`);
                
                // Show/hide discount row
                if (discountAmount > 0) {
                    $('#discount-row').show();
                    $('#discount').text(`-${fareData.currency} ${discountAmount.toFixed(2)}`);
                } else {
                    $('#discount-row').hide();
                    $('#discount').text(`-${fareData.currency} 0.00`);
                }
                
                $('#tax').text(`${fareData.currency} ${tax.toFixed(2)}`);
                $('#total').text(`${fareData.currency} ${total.toFixed(2)}`);

                // Show passenger forms if seats are selected
                if (seatCount > 0) {
                    $('#passenger-forms-card').show();
                }

                // Enable/disable proceed button
                if (seatCount > 0 && seatCount <= passengers && validatePassengerInfo()) {
                    const passengerCount = Object.keys(passengerInfo).length;
                    // Ensure we have enough passengers for seats (can have more passengers than seats)
                    if (passengerCount >= seatCount) {
                        $('#proceed-booking-btn').prop('disabled', false).removeClass('opacity-50');
                    } else {
                        $('#proceed-booking-btn').prop('disabled', true).addClass('opacity-50');
                    }
                } else {
                    $('#proceed-booking-btn').prop('disabled', true).addClass('opacity-50');
                }
            }

            function updateSelectedSeatsCount() {
                $('#selected-seats-count').text(Object.keys(selectedSeats).length);
            }

            // Proceed to booking
            $('#proceed-booking-btn').on('click', function() {
                if (!validatePassengerInfo()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Information',
                        text: 'Please fill all required fields (Name and Gender) for all passengers.',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                if (Object.keys(selectedSeats).length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Seats Selected',
                        text: 'Please select at least one seat.',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                const selectedSeatCount = Object.keys(selectedSeats).length;
                const passengerCount = Object.keys(passengerInfo).length;

                if (passengerCount < selectedSeatCount) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Not Enough Passengers',
                        text: `You have selected ${selectedSeatCount} seat(s) but only ${passengerCount} passenger(s). Please add more passengers or select fewer seats.`,
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                // Collect passenger data - take first N passengers for N seats
                const passengersData = [];
                const seatsData = [];
                const selectedSeatNumbers = Object.keys(selectedSeats).sort((a, b) => parseInt(a) - parseInt(b));
                const passengerIds = Object.keys(passengerInfo).sort((a, b) => {
                    if (passengerInfo[a].type === 'mandatory') return -1;
                    if (passengerInfo[b].type === 'mandatory') return 1;
                    return a.localeCompare(b);
                });

                // Map passengers to seats
                selectedSeatNumbers.forEach((seatNum, index) => {
                    const passengerId = passengerIds[index];
                    const info = passengerInfo[passengerId];

                    if (info) {
                        passengersData.push({
                            name: info.name,
                            gender: info.gender || selectedSeats[seatNum], // Use passenger gender or seat gender
                            cnic: info.cnic || null,
                            phone: info.phone || null,
                            email: info.email || null,
                            age: info.age ? parseInt(info.age) : null
                        });

                        seatsData.push({
                            seat_number: parseInt(seatNum),
                            gender: selectedSeats[seatNum]
                        });
                    }
                });

                // Calculate totals
                const seatCount = seatsData.length;
                const baseFarePerSeat = parseFloat(fareData.base_fare || fareData.final_fare);
                let subtotal = baseFarePerSeat * seatCount;

                // Calculate discount amount
                let discountAmount = 0;
                if (fareData.has_discount && fareData.discount_type && fareData.discount_value !== undefined) {
                    if (fareData.discount_type === 'fixed' || fareData.discount_type === 'flat') {
                        // Fixed discount - multiply by seat count
                        const discountValue = parseFloat(fareData.discount_value);
                        discountAmount = discountValue * seatCount;
                    } else if (fareData.discount_type === 'percentage' || fareData.discount_type === 'percent') {
                        // Percentage discount on total subtotal
                        discountAmount = subtotal * (parseFloat(fareData.discount_value || 0) / 100);
                    }
                }

                const tax = 0;
                const total = Math.max(0, subtotal - discountAmount + tax);

                // Submit booking
                $('#proceed-booking-btn').prop('disabled', true);

                $.ajax({
                    url: '{{ route('frontend.bookings.store') }}',
                    type: 'POST',
                    data: {
                        trip_id: tripId,
                        from_terminal_id: fromTerminalId,
                        to_terminal_id: toTerminalId,
                        seat_numbers: Object.keys(selectedSeats).map(Number),
                        seats_data: JSON.stringify(seatsData),
                        passengers: JSON.stringify(passengersData),
                        total_fare: subtotal,
                        discount_amount: discountAmount,
                        tax_amount: tax,
                        final_amount: total,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Redirect to payment page
                        window.location.href = `/bookings/${response.booking.id}/payment`;
                    },
                    error: function(xhr) {
                        $('#proceed-booking-btn').prop('disabled', false);
                        let errorMsg = 'Failed to create booking';
                        let errorTitle = 'Booking Failed';
                        let errorIcon = 'error';
                        let confirmColor = '#d33';
                        
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                errorMsg = Object.values(errors).flat().join(', ');
                            }
                            
                            // Check if it's a 2-hour restriction error
                            if (errorMsg.includes('2 hours') || errorMsg.includes('departs too soon')) {
                                errorTitle = 'Booking Not Available';
                                errorIcon = 'warning';
                                confirmColor = '#ffc107';
                                errorMsg = 'Online bookings must be made at least 2 hours before departure. This trip departs too soon to book online. Please visit our counter or try booking an upcoming trip.';
                            }
                        }

                        Swal.fire({
                            icon: errorIcon,
                            title: errorTitle,
                            text: errorMsg,
                            confirmButtonColor: confirmColor
                        });
                    }
                });
            });
        });
    </script>
@endsection


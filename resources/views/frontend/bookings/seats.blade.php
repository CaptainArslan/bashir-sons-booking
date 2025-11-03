@extends('frontend.layouts.app')

@section('title', 'Select Seats')

@section('styles')
    <style>
        .seat-btn {
            width: 3.5rem;
            height: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .5rem;
            font-size: .9rem;
            font-weight: 600;
            padding: 0;
            line-height: 1;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .available {
            background-color: #e9ecef;
            color: #000;
            border-color: #dee2e6;
        }

        .available:hover {
            background-color: #d0d7de;
            border-color: #adb5bd;
        }

        .selected {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0a58ca;
        }

        .booked-male {
            background-color: #0dcaf0;
            color: #fff;
            border-color: #0aa2c0;
            cursor: not-allowed;
        }

        .booked-female {
            background-color: #e83e8c;
            color: #fff;
            border-color: #c2185b;
            cursor: not-allowed;
        }

        .held {
            background-color: #ffc107;
            color: #000;
            border-color: #ffb300;
            cursor: not-allowed;
        }

        .seat-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .aisle {
            width: 2rem;
        }

        .legend-box {
            width: 1.5rem;
            height: 1.5rem;
            display: inline-block;
            border-radius: .25rem;
            border: 2px solid transparent;
        }

        .passenger-form {
            background: #f8f9fa;
            border-radius: .5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .summary-card {
            position: sticky;
            top: 100px;
        }
    </style>
@endsection

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h4 class="mb-1" id="trip-info">
                                        <i class="bi bi-bus-front me-2 text-primary"></i>
                                        <span id="route-name">Loading...</span>
                                    </h4>
                                    <p class="text-muted mb-0" id="trip-details">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <span id="trip-date">Loading...</span>
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-people me-1"></i>
                                        <span id="selected-seats-count">0</span> of {{ $passengers }} seat(s) selected
                                    </p>
                                </div>
                                <a href="{{ route('frontend.bookings.trips', request()->only(['from_terminal_id', 'to_terminal_id', 'date', 'passengers'])) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Trips
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-grid me-2"></i>Select Your Seats
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="loading-spinner text-center py-5" id="loading-seats">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Loading seat map...</p>
                            </div>

                            <div id="seat-map-container" class="text-center" style="display: none;">
                                <div id="seat-map" class="mb-4">
                                    <!-- Seat map will be rendered here -->
                                </div>

                                <!-- Legend -->
                                <div class="d-flex justify-content-center align-items-center flex-wrap gap-4 mt-4">
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box available me-2"></span>
                                        <small>Available</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box selected me-2"></span>
                                        <small>Selected</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box booked-male me-2"></span>
                                        <small>Male Booked</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box booked-female me-2"></span>
                                        <small>Female Booked</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="legend-box held me-2"></span>
                                        <small>Held</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Information Forms -->
                    <div class="card shadow-sm border-0" id="passenger-forms-card" style="display: none;">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>Passenger Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="passengers-container">
                                <!-- Passenger forms will be generated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 summary-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-receipt me-2"></i>Booking Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Fare per seat:</span>
                                    <strong id="fare-per-seat">PKR 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Seats selected:</span>
                                    <strong id="seats-count">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong id="subtotal">PKR 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount:</span>
                                    <strong id="discount" class="text-success">PKR 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax:</span>
                                    <strong id="tax">PKR 0.00</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <h5 class="mb-0">Total:</h5>
                                    <h5 class="mb-0 text-primary" id="total">PKR 0.00</h5>
                                </div>
                            </div>

                            <button type="button" id="proceed-booking-btn" class="btn btn-primary w-100" disabled>
                                <i class="bi bi-check-circle me-2"></i>Proceed to Booking
                            </button>
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
            let pendingSeat = null;
            let fareData = null;

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
                        $('#route-name').text(response.trip.route_name);
                        $('#trip-date').text(new Date(response.trip.departure_date).toLocaleDateString('en-US', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        }));

                        // Render seat map
                        renderSeatMap();

                        // Update fare display
                        updateFareDisplay();

                        $('#loading-seats').hide();
                        $('#seat-map-container').show();
                    },
                    error: function(xhr) {
                        $('#loading-seats').hide();
                        let errorMsg = 'Failed to load trip details';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#d33'
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
                const seatsPerRow = 4; // 2-2 layout (2 seats left, aisle, 2 seats right)
                const rows = Math.ceil(totalSeats / seatsPerRow);

                let seatNum = 1;
                for (let row = 0; row < rows; row++) {
                    const rowDiv = $('<div class="mb-3 d-flex justify-content-center align-items-center gap-2"></div>');

                    // Left side (2 seats)
                    for (let i = 0; i < 2 && seatNum <= totalSeats; i++) {
                        if (seatMap[seatNum]) {
                            rowDiv.append(createSeatButton(seatNum, seatMap[seatNum]));
                        }
                        seatNum++;
                    }

                    // Aisle (always show, except maybe last row if odd)
                    if (seatNum <= totalSeats || row < rows - 1) {
                        rowDiv.append($('<div class="aisle"></div>'));
                    }

                    // Right side (2 seats)
                    for (let i = 0; i < 2 && seatNum <= totalSeats; i++) {
                        if (seatMap[seatNum]) {
                            rowDiv.append(createSeatButton(seatNum, seatMap[seatNum]));
                        }
                        seatNum++;
                    }

                    container.append(rowDiv);
                }
            }

            function createSeatButton(seatNumber, seatInfo) {
                const button = $('<button></button>')
                    .addClass('seat-btn')
                    .text(seatNumber)
                    .attr('data-seat', seatNumber);

                if (seatInfo.status === 'available') {
                    button.addClass('available');
                } else if (seatInfo.status === 'booked') {
                    if (seatInfo.gender === 'male') {
                        button.addClass('booked-male').prop('disabled', true);
                    } else if (seatInfo.gender === 'female') {
                        button.addClass('booked-female').prop('disabled', true);
                    } else {
                        button.addClass('booked-male').prop('disabled', true);
                    }
                } else if (seatInfo.status === 'held') {
                    button.addClass('held').prop('disabled', true);
                }

                if (selectedSeats[seatNumber]) {
                    button.removeClass('available').addClass('selected');
                }

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
                    updatePassengerForms();
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
                    updatePassengerForms();
                    updateFareDisplay();
                    updateSelectedSeatsCount();
                }
            });

            function updatePassengerForms() {
                const container = $('#passengers-container');
                container.html('');

                const seatNumbers = Object.keys(selectedSeats).sort((a, b) => parseInt(a) - parseInt(b));

                if (seatNumbers.length === 0) {
                    $('#passenger-forms-card').hide();
                    return;
                }

                $('#passenger-forms-card').show();

                seatNumbers.forEach(function(seatNum, index) {
                    const gender = selectedSeats[seatNum];
                    const form = `
                        <div class="passenger-form" data-seat="${seatNum}">
                            <h6 class="mb-3">
                                <i class="bi bi-person me-2"></i>Passenger ${index + 1} - Seat ${seatNum}
                                <span class="badge ${gender === 'male' ? 'bg-info' : 'bg-pink'} ms-2">${gender}</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control passenger-name" required 
                                        placeholder="Enter passenger name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CNIC <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control passenger-cnic" required 
                                        placeholder="34101-1111111-1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control passenger-phone" 
                                        placeholder="0317-7777777">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control passenger-email" 
                                        placeholder="email@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control passenger-age" min="1" max="120" 
                                        placeholder="Age">
                                </div>
                            </div>
                        </div>
                    `;

                    container.append(form);
                });

                // Apply input masks
                $('.passenger-cnic').inputmask('99999-9999999-9');
                $('.passenger-phone').inputmask('9999-9999999');
            }

            function updateFareDisplay() {
                if (!fareData) return;

                const seatCount = Object.keys(selectedSeats).length;
                const farePerSeat = parseFloat(fareData.final_fare);
                let subtotal = farePerSeat * seatCount;

                // Apply discount
                let discountAmount = 0;
                if (fareData.discount_type && fareData.discount_value) {
                    if (fareData.discount_type === 'flat') {
                        discountAmount = parseFloat(fareData.discount_value) * seatCount;
                    } else if (fareData.discount_type === 'percent') {
                        discountAmount = subtotal * (parseFloat(fareData.discount_value) / 100);
                    }
                }

                const total = Math.max(0, subtotal - discountAmount);
                const tax = 0; // No tax for online bookings initially

                $('#fare-per-seat').text(`${fareData.currency} ${farePerSeat.toFixed(2)}`);
                $('#seats-count').text(seatCount);
                $('#subtotal').text(`${fareData.currency} ${subtotal.toFixed(2)}`);
                $('#discount').text(`-${fareData.currency} ${discountAmount.toFixed(2)}`);
                $('#tax').text(`${fareData.currency} ${tax.toFixed(2)}`);
                $('#total').text(`${fareData.currency} ${total.toFixed(2)}`);

                // Enable/disable proceed button
                if (seatCount > 0 && seatCount <= passengers && allPassengerFormsValid()) {
                    $('#proceed-booking-btn').prop('disabled', false);
                } else {
                    $('#proceed-booking-btn').prop('disabled', true);
                }
            }

            function updateSelectedSeatsCount() {
                $('#selected-seats-count').text(Object.keys(selectedSeats).length);
            }

            function allPassengerFormsValid() {
                let valid = true;
                $('.passenger-form').each(function() {
                    const name = $(this).find('.passenger-name').val();
                    const cnic = $(this).find('.passenger-cnic').val();
                    if (!name || !cnic) {
                        valid = false;
                        return false;
                    }
                });
                return valid;
            }

            // Validate forms on input
            $(document).on('input', '.passenger-name, .passenger-cnic', function() {
                updateFareDisplay();
            });

            // Proceed to booking
            $('#proceed-booking-btn').on('click', function() {
                if (!allPassengerFormsValid()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Information',
                        text: 'Please fill all required fields for all passengers.',
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

                // Collect passenger data
                const passengersData = [];
                const seatsData = [];

                $('.passenger-form').each(function() {
                    const seatNum = parseInt($(this).data('seat'));
                    const name = $(this).find('.passenger-name').val();
                    const cnic = $(this).find('.passenger-cnic').val();
                    const phone = $(this).find('.passenger-phone').val();
                    const email = $(this).find('.passenger-email').val();
                    const age = $(this).find('.passenger-age').val();

                    passengersData.push({
                        name: name,
                        cnic: cnic || null,
                        phone: phone || null,
                        email: email || null,
                        age: age ? parseInt(age) : null,
                        gender: selectedSeats[seatNum]
                    });

                    seatsData.push({
                        seat_number: seatNum,
                        gender: selectedSeats[seatNum]
                    });
                });

                // Calculate totals
                const seatCount = seatsData.length;
                const farePerSeat = parseFloat(fareData.final_fare);
                let subtotal = farePerSeat * seatCount;

                let discountAmount = 0;
                if (fareData.discount_type && fareData.discount_value) {
                    if (fareData.discount_type === 'flat') {
                        discountAmount = parseFloat(fareData.discount_value) * seatCount;
                    } else if (fareData.discount_type === 'percent') {
                        discountAmount = subtotal * (parseFloat(fareData.discount_value) / 100);
                    }
                }

                const tax = 0;
                const total = Math.max(0, subtotal - discountAmount);

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
                        Swal.fire({
                            icon: 'success',
                            title: 'Booking Successful!',
                            html: `
                                <p>Your booking has been confirmed!</p>
                                <p><strong>Booking Number:</strong> ${response.booking.booking_number}</p>
                                <p><strong>Total Amount:</strong> PKR ${parseFloat(response.booking.final_amount).toFixed(2)}</p>
                            `,
                            confirmButtonColor: '#0d6efd'
                        }).then(() => {
                            window.location.href = '{{ route('profile.bookings') }}';
                        });
                    },
                    error: function(xhr) {
                        $('#proceed-booking-btn').prop('disabled', false);
                        let errorMsg = 'Failed to create booking';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                errorMsg = Object.values(errors).flat().join(', ');
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Booking Failed',
                            text: errorMsg,
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            });
        });
    </script>
@endsection


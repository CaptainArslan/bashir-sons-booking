@extends('admin.layouts.app')

@section('title', 'Select Seats')

@section('styles')
    <style>
        .seat-layout {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 2rem;
        }

        .bus-layout {
            max-width: 600px;
            margin: 0 auto;
        }

        .seat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 10px;
        }

        .seat-group {
            display: flex;
            gap: 10px;
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

        .seat:hover:not(.booked):not(.driver-seat) {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .seat.available {
            border-color: #28a745;
            color: #28a745;
        }

        .seat.available:hover {
            background: #28a745;
            color: white;
        }

        .seat.selected {
            background: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .seat.selected-male {
            background: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .seat.selected-female {
            background: #ec4899;
            border-color: #ec4899;
            color: white;
        }

        .seat.booked {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }

        .seat.locked {
            background: #ffc107;
            border-color: #ffc107;
            color: white;
            cursor: not-allowed;
        }

        .seat.driver-seat {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
            cursor: not-allowed;
        }

        .legend {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-box {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid;
        }

        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-card h6 {
            color: white;
            margin-bottom: 0.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .fare-summary {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
        }

        .fare-summary h6 {
            color: #1976d2;
            margin-bottom: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Booking Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.create') }}">Create Booking</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Select Seats</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-chair me-2"></i>Select Seats</h5>
                </div>
                <div class="card-body">
                    <!-- Trip Info -->
                    <div class="info-card">
                        <h6><i class="bx bx-bus me-1"></i>Trip Information</h6>
                        <div class="info-row">
                            <span>Route:</span>
                            <strong>{{ $trip->route->name }}</strong>
                        </div>
                        <div class="info-row">
                            <span>From:</span>
                            <strong>{{ $fromStop->terminal->name }}</strong>
                        </div>
                        <div class="info-row">
                            <span>To:</span>
                            <strong>{{ $toStop->terminal->name }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Departure:</span>
                            <strong>{{ \Carbon\Carbon::parse($trip->departure_datetime)->format('d M Y, h:i A') }}</strong>
                        </div>
                        <div class="info-row">
                            <span>Fare per Seat:</span>
                            <strong>PKR {{ number_format($fare, 2) }}</strong>
                        </div>
                    </div>

                    <!-- Seat Layout -->
                    <div class="seat-layout">
                        <div class="bus-layout">
                            <!-- Driver Row -->
                            <div class="seat-row">
                                <div class="seat driver-seat">
                                    <i class="bx bx-steering-wheel"></i>
                                </div>
                                <div class="aisle"></div>
                                <div class="seat-group"></div>
                            </div>

                            <!-- Seats Layout (45 seats: 11 rows x 4 seats + 1 last row) -->
                            @php
                                $seatNumber = 1;
                                $totalSeats = 45;
                                $seatsPerRow = 4;
                                $rows = 11; // 10 full rows + 1 last row with 5 seats
                            @endphp

                            @for ($row = 1; $row <= $rows; $row++)
                                <div class="seat-row">
                                    <div class="seat-group">
                                        @for ($seat = 1; $seat <= 2; $seat++)
                                            @if ($seatNumber <= $totalSeats)
                                                @php
                                                    $isBooked = in_array($seatNumber, $bookedSeats);
                                                    $seatClass = $isBooked ? 'booked' : 'available';
                                                @endphp
                                                <div class="seat {{ $seatClass }}"
                                                    data-seat="{{ $seatNumber }}"
                                                    data-fare="{{ $fare }}"
                                                    {{ $isBooked ? 'title=Seat Booked' : '' }}>
                                                    {{ $seatNumber }}
                                                </div>
                                                @php $seatNumber++; @endphp
                                            @endif
                                        @endfor
                                    </div>
                                    <div class="aisle"></div>
                                    <div class="seat-group">
                                        @for ($seat = 1; $seat <= 2; $seat++)
                                            @if ($seatNumber <= $totalSeats)
                                                @php
                                                    $isBooked = in_array($seatNumber, $bookedSeats);
                                                    $seatClass = $isBooked ? 'booked' : 'available';
                                                @endphp
                                                <div class="seat {{ $seatClass }}"
                                                    data-seat="{{ $seatNumber }}"
                                                    data-fare="{{ $fare }}"
                                                    {{ $isBooked ? 'title=Seat Booked' : '' }}>
                                                    {{ $seatNumber }}
                                                </div>
                                                @php $seatNumber++; @endphp
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                            @endfor

                            <!-- Last Row with 5 Seats -->
                            <div class="seat-row justify-content-center">
                                <div class="seat-group">
                                    @for ($seat = 41; $seat <= 45; $seat++)
                                        @php
                                            $isBooked = in_array($seat, $bookedSeats);
                                            $seatClass = $isBooked ? 'booked' : 'available';
                                        @endphp
                                        <div class="seat {{ $seatClass }}"
                                            data-seat="{{ $seat }}"
                                            data-fare="{{ $fare }}"
                                            {{ $isBooked ? 'title=Seat Booked' : '' }}>
                                            {{ $seat }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-box" style="background: white; border-color: #28a745;"></div>
                                <span>Available</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #0d6efd; border-color: #0d6efd;"></div>
                                <span>Male</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #ec4899; border-color: #ec4899;"></div>
                                <span>Female</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #ffc107; border-color: #ffc107;"></div>
                                <span>Locked</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #dc3545; border-color: #dc3545;"></div>
                                <span>Booked</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bx bx-list-check me-1"></i>Selection Summary</h6>
                </div>
                <div class="card-body">
                    <div id="selected-seats-list">
                        <p class="text-muted text-center">No seats selected yet</p>
                    </div>

                    <div class="fare-summary" id="fare-summary" style="display: none;">
                        <h6><i class="bx bx-money me-1"></i>Fare Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Selected Seats:</span>
                            <strong id="total-seats">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Fare per Seat:</span>
                            <strong>PKR {{ number_format($fare, 2) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Fare:</strong>
                            <strong class="text-primary" id="total-fare">PKR 0.00</strong>
                        </div>
                    </div>

                    <form action="{{ route('admin.bookings.select-seats') }}" method="POST" id="seat-selection-form">
                        @csrf
                        <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                        <input type="hidden" name="from_stop_id" value="{{ $fromStop->id }}">
                        <input type="hidden" name="to_stop_id" value="{{ $toStop->id }}">
                        <div id="selected-seats-inputs"></div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary" id="continue-btn" disabled>
                                <i class="bx bx-right-arrow-alt me-1"></i>Continue to Passenger Details
                            </button>
                            <a href="{{ route('admin.bookings.create') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to Search
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let selectedSeats = [];
            const farePerSeat = {{ $fare }};
            const tripId = {{ $trip->id }};
            const fromStopId = {{ $fromStop->id }};
            const toStopId = {{ $toStop->id }};
            let pollingInterval;

            // Start polling for seat updates
            startSeatPolling();

            // Seat click handler
            $(document).on('click', '.seat.available', function() {
                const seatNumber = $(this).data('seat');
                const $seat = $(this);

                if ($(this).hasClass('selected-male') || $(this).hasClass('selected-female')) {
                    // Deselect seat
                    deselectSeat(seatNumber, $seat);
                } else {
                    // Ask for gender before selecting
                    Swal.fire({
                        title: 'Select Passenger Gender',
                        text: `Seat ${seatNumber}`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bx bx-male"></i> Male',
                        cancelButtonText: '<i class="bx bx-female"></i> Female',
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#ec4899',
                        showCloseButton: true,
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Male selected
                            selectSeat(seatNumber, 'male', $seat);
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Female selected
                            selectSeat(seatNumber, 'female', $seat);
                        }
                    });
                }
            });

            function selectSeat(seatNumber, gender, $seat) {
                // Immediately lock the seat visually
                $seat.removeClass('available').addClass('selected-' + gender);
                
                // Add to selection
                selectedSeats.push({
                    seat: seatNumber,
                    gender: gender
                });

                // Lock seat on server
                lockSeatOnServer(seatNumber, gender);
                
                updateSelection();
            }

            function deselectSeat(seatNumber, $seat) {
                Swal.fire({
                    title: 'Remove Seat?',
                    text: `Do you want to remove seat ${seatNumber} from selection?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Remove gender class and make available
                        $seat.removeClass('selected-male selected-female').addClass('available');
                        
                        // Remove from selection
                        selectedSeats = selectedSeats.filter(s => s.seat !== seatNumber);
                        
                        // Unlock seat on server
                        unlockSeatOnServer(seatNumber);
                        
                        updateSelection();
                    }
                });
            }

            function lockSeatOnServer(seatNumber, gender) {
                $.ajax({
                    url: "{{ route('admin.bookings.lock-seat') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        trip_id: tripId,
                        from_stop_id: fromStopId,
                        to_stop_id: toStopId,
                        seat_number: seatNumber,
                        gender: gender
                    },
                    success: function(response) {
                        if (!response.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Seat Unavailable',
                                text: response.message || 'This seat has been taken by another user.'
                            });
                            // Revert the selection
                            const $seat = $(`.seat[data-seat="${seatNumber}"]`);
                            $seat.removeClass('selected-male selected-female').addClass('booked');
                            selectedSeats = selectedSeats.filter(s => s.seat !== seatNumber);
                            updateSelection();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to lock seat. Please try again.'
                        });
                    }
                });
            }

            function unlockSeatOnServer(seatNumber) {
                $.ajax({
                    url: "{{ route('admin.bookings.unlock-seat') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        trip_id: tripId,
                        from_stop_id: fromStopId,
                        to_stop_id: toStopId,
                        seat_number: seatNumber
                    }
                });
            }

            function startSeatPolling() {
                // Poll every 3 seconds for seat updates
                pollingInterval = setInterval(function() {
                    checkSeatAvailability();
                }, 3000);
            }

            function checkSeatAvailability() {
                $.ajax({
                    url: "{{ route('admin.bookings.check-seats') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        trip_id: tripId,
                        from_stop_id: fromStopId,
                        to_stop_id: toStopId
                    },
                    success: function(response) {
                        if (response.success) {
                            updateSeatStatus(response.data.locked_seats, response.data.booked_seats);
                        }
                    }
                });
            }

            function updateSeatStatus(lockedSeats, bookedSeats) {
                $('.seat.available, .seat.locked').each(function() {
                    const seatNumber = $(this).data('seat');
                    const isSelectedByMe = selectedSeats.some(s => s.seat === seatNumber);
                    
                    // Skip if this user selected it
                    if (isSelectedByMe) {
                        return;
                    }

                    // Check if locked by another user
                    if (lockedSeats.includes(seatNumber)) {
                        if (!$(this).hasClass('locked')) {
                            $(this).removeClass('available').addClass('locked');
                        }
                    } else if (bookedSeats.includes(seatNumber)) {
                        $(this).removeClass('available locked').addClass('booked');
                    } else {
                        if ($(this).hasClass('locked')) {
                            $(this).removeClass('locked').addClass('available');
                        }
                    }
                });
            }

            function updateSelection() {
                const seatsCount = selectedSeats.length;

                // Update seats list
                if (seatsCount === 0) {
                    $('#selected-seats-list').html('<p class="text-muted text-center">No seats selected yet</p>');
                    $('#fare-summary').hide();
                    $('#continue-btn').prop('disabled', true);
                } else {
                    const seatsList = selectedSeats.sort((a, b) => a.seat - b.seat).map(item => {
                        const badgeColor = item.gender === 'male' ? 'bg-primary' : 'bg-pink';
                        const icon = item.gender === 'male' ? 'bx-male' : 'bx-female';
                        return `<span class="badge ${badgeColor} me-1 mb-1">
                            <i class="bx ${icon}"></i> Seat ${item.seat}
                        </span>`;
                    }).join('');
                    $('#selected-seats-list').html(seatsList);
                    $('#fare-summary').show();
                    $('#continue-btn').prop('disabled', false);
                }

                // Update fare summary
                const totalFare = seatsCount * farePerSeat;
                $('#total-seats').text(seatsCount);
                $('#total-fare').text('PKR ' + totalFare.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));

                // Update form inputs
                $('#selected-seats-inputs').html(selectedSeats.map(item =>
                    `<input type="hidden" name="selected_seats[]" value="${item.seat}">
                     <input type="hidden" name="seat_genders[${item.seat}]" value="${item.gender}">`
                ).join(''));
            }

            // Clear polling on page unload
            $(window).on('beforeunload', function() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }
            });
        });
    </script>
    <style>
        .bg-pink {
            background-color: #ec4899 !important;
        }
    </style>
@endsection


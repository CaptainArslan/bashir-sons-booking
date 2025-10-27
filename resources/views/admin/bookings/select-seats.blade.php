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

        .seat:hover:not(.booked):not(.sold):not(.locked):not(.driver-seat) {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .seat.available {
            border-color: #28a745;
            background: #f0fff4;
            color: #28a745;
        }

        .seat.available:hover {
            background: #28a745;
            color: white;
            transform: scale(1.05);
        }

        .seat.selected {
            background: #0d6efd;
            border-color: #0d6efd;
            color: white;
            font-weight: bold;
            position: relative;
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
            border: 2px solid #0d6efd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .seat.selected .gender-badge.male {
            color: #0d6efd;
        }

        .seat.selected .gender-badge.female {
            color: #ec4899;
            border-color: #ec4899;
        }

        .seat.booked {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
            cursor: not-allowed;
            position: relative;
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
            position: relative;
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
            font-weight: bold;
        }

        .seat.locked {
            background: #e9ecef;
            border: 2px dashed #6c757d;
            color: #6c757d;
            cursor: not-allowed;
            position: relative;
        }

        .seat.locked::after {
            content: '🔒';
            position: absolute;
            top: -12px;
            right: -12px;
            font-size: 16px;
            background: white;
            border-radius: 50%;
            padding: 2px;
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
            border: 1px solid #6c757d;
        }

        .seat.locked .gender-icon.male {
            color: #0d6efd;
        }

        .seat.locked .gender-icon.female {
            color: #ec4899;
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
                                                    $seatStatus = isset($seatStatuses[$seatNumber]) ? $seatStatuses[$seatNumber] : null;
                                                    $seatClass = 'available';
                                                    $seatTitle = '';
                                                    
                                                    if ($isBooked && $seatStatus) {
                                                        if ($seatStatus['is_confirmed']) {
                                                            $seatClass = 'sold';
                                                            $seatTitle = 'Confirmed Booking - ' . $seatStatus['booking_number'];
                                                        } else {
                                                            $seatClass = 'booked';
                                                            $seatTitle = 'Pending Payment - ' . $seatStatus['booking_number'];
                                                        }
                                                    }
                                                @endphp
                                                <div class="seat {{ $seatClass }}"
                                                    data-seat="{{ $seatNumber }}"
                                                    data-fare="{{ $fare }}"
                                                    {{ $seatTitle ? "title=\"$seatTitle\"" : '' }}>
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
                                                    $seatStatus = isset($seatStatuses[$seatNumber]) ? $seatStatuses[$seatNumber] : null;
                                                    $seatClass = 'available';
                                                    $seatTitle = '';
                                                    
                                                    if ($isBooked && $seatStatus) {
                                                        if ($seatStatus['is_confirmed']) {
                                                            $seatClass = 'sold';
                                                            $seatTitle = 'Confirmed Booking - ' . $seatStatus['booking_number'];
                                                        } else {
                                                            $seatClass = 'booked';
                                                            $seatTitle = 'Pending Payment - ' . $seatStatus['booking_number'];
                                                        }
                                                    }
                                                @endphp
                                                <div class="seat {{ $seatClass }}"
                                                    data-seat="{{ $seatNumber }}"
                                                    data-fare="{{ $fare }}"
                                                    {{ $seatTitle ? "title=\"$seatTitle\"" : '' }}>
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
                                            $seatStatus = isset($seatStatuses[$seat]) ? $seatStatuses[$seat] : null;
                                            $seatClass = 'available';
                                            $seatTitle = '';
                                            
                                            if ($isBooked && $seatStatus) {
                                                if ($seatStatus['is_confirmed']) {
                                                    $seatClass = 'sold';
                                                    $seatTitle = 'Confirmed Booking - ' . $seatStatus['booking_number'];
                                                } else {
                                                    $seatClass = 'booked';
                                                    $seatTitle = 'Pending Payment - ' . $seatStatus['booking_number'];
                                                }
                                            }
                                        @endphp
                                        <div class="seat {{ $seatClass }}"
                                            data-seat="{{ $seat }}"
                                            data-fare="{{ $fare }}"
                                            {{ $seatTitle ? "title=\"$seatTitle\"" : '' }}>
                                            {{ $seat }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-box" style="background: #f0fff4; border: 2px solid #28a745;"></div>
                                <span>Available</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #0d6efd; border-color: #0d6efd; position: relative;">
                                    <span style="position: absolute; top: -8px; right: -8px; background: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; border: 2px solid #0d6efd; font-size: 10px;">♂</span>
                                </div>
                                <span>Your Selection (♂/♀)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #e9ecef; border: 2px dashed #6c757d; position: relative;">
                                    <span style="position: absolute; top: -8px; right: -8px; font-size: 10px;">🔒</span>
                                    <span style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); font-size: 10px;">♂</span>
                                </div>
                                <span>Locked by Others</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #fff3cd; border: 2px solid #ffc107; position: relative;">
                                    <span style="position: absolute; top: -6px; right: -6px; font-size: 10px;">⏳</span>
                                </div>
                                <span>Booked (Pending)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box" style="background: #dc3545; border-color: #dc3545; position: relative;">
                                    <span style="position: absolute; top: -6px; right: -6px; font-size: 10px; background: white; border-radius: 50%; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center;">✓</span>
                                </div>
                                <span>Sold (Confirmed)</span>
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

            // Subscribe to Ably channel for real-time seat updates
            if (typeof Echo !== 'undefined') {
                console.log('🔌 Connecting to Ably channel: trip.' + tripId);
                
                Echo.channel('trip.' + tripId)
                    .listen('.seat.locked', (data) => {
                        console.log('🔒 Seat locked event received:', data);
                        
                        // Only process if it's for the same segment
                        if (data.from_stop_id === fromStopId && data.to_stop_id === toStopId) {
                            handleSeatLockedEvent(data);
                        }
                    })
                    .listen('.seat.released', (data) => {
                        console.log('🔓 Seat released event received:', data);
                        
                        // Only process if it's for the same segment
                        if (data.from_stop_id === fromStopId && data.to_stop_id === toStopId) {
                            handleSeatReleasedEvent(data);
                        }
                    })
                    .error((error) => {
                        console.error('❌ Echo channel error:', error);
                        showToast('Real-time connection error. Please refresh the page.', 'error');
                    });

                console.log('✅ Real-time seat updates active via Ably');
            } else {
                console.warn('⚠️ Echo is not defined. Real-time updates will not work.');
                showToast('Real-time updates unavailable. Please refresh to see seat changes.', 'warning');
            }

            // Seat click handler
            $(document).on('click', '.seat.available', function() {
                const seatNumber = $(this).data('seat');
                const $seat = $(this);

                if ($(this).hasClass('selected')) {
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
                        reverseButtons: true,
                        allowOutsideClick: false
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
                $seat.removeClass('available').addClass('selected');
                
                // Add gender badge to show male/female selection
                const genderIcon = gender === 'male' 
                    ? '<i class="bx bx-male"></i>' 
                    : '<i class="bx bx-female"></i>';
                const genderClass = gender === 'male' ? 'male' : 'female';
                
                $seat.append(`<span class="gender-badge ${genderClass}">${genderIcon}</span>`);
                
                // Add to selection
                selectedSeats.push({
                    seat: seatNumber,
                    gender: gender
                });

                // Lock seat on server and broadcast to others
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
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Remove selected class and gender badge, make available
                        $seat.removeClass('selected')
                             .addClass('available')
                             .find('.gender-badge').remove();
                        
                        // Remove from selection
                        selectedSeats = selectedSeats.filter(s => s.seat !== seatNumber);
                        
                        // Unlock seat on server and broadcast to others
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
                            // Revert the selection and remove gender badge
                            const $seat = $(`.seat[data-seat="${seatNumber}"]`);
                            $seat.removeClass('selected')
                                 .addClass('available')
                                 .find('.gender-badge').remove();
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

            // Handle real-time seat locked event from Ably
            function handleSeatLockedEvent(data) {
                const seatNumber = parseInt(data.seat_number);
                const $seat = $(`.seat[data-seat="${seatNumber}"]`);
                
                // Only update if not selected by current user
                const isSelectedByMe = selectedSeats.some(s => s.seat === seatNumber);
                if (!isSelectedByMe && $seat.hasClass('available')) {
                    // Add locked class and gender icon
                    const genderIcon = data.gender === 'male' 
                        ? '<i class="bx bx-male"></i>' 
                        : '<i class="bx bx-female"></i>';
                    const genderClass = data.gender === 'male' ? 'male' : 'female';
                    
                    $seat.removeClass('available')
                         .addClass('locked')
                         .attr('title', `Locked by ${data.user_name} (${data.gender.charAt(0).toUpperCase() + data.gender.slice(1)})`);
                    
                    // Add gender icon to the seat
                    if (!$seat.find('.gender-icon').length) {
                        $seat.append(`<span class="gender-icon ${genderClass}">${genderIcon}</span>`);
                    }
                    
                    console.log(`🔒 Seat ${seatNumber} locked by ${data.user_name} (${data.gender})`);
                    
                    // Show toast notification
                    showToast(`Seat ${seatNumber} was just selected by ${data.user_name}`, 'info');
                }
            }

            // Handle real-time seat released event from Ably
            function handleSeatReleasedEvent(data) {
                const seatNumber = parseInt(data.seat_number);
                const $seat = $(`.seat[data-seat="${seatNumber}"]`);
                
                // Only update if not selected by current user
                const isSelectedByMe = selectedSeats.some(s => s.seat === seatNumber);
                if (!isSelectedByMe && $seat.hasClass('locked')) {
                    $seat.removeClass('locked')
                         .addClass('available')
                         .removeAttr('title')
                         .find('.gender-icon').remove(); // Remove gender icon
                    
                    console.log(`🔓 Seat ${seatNumber} released`);
                    
                    // Show toast notification
                    showToast(`Seat ${seatNumber} is now available`, 'success');
                }
            }
            
            // Show toast notification for real-time updates
            function showToast(message, type = 'info') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: type,
                    title: message
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
                        const genderLabel = item.gender === 'male' ? 'Male' : 'Female';
                        return `<span class="badge ${badgeColor} me-1 mb-1" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                            <i class="bx ${icon}"></i> Seat ${item.seat} - ${genderLabel}
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

            // Cleanup on page unload
            $(window).on('beforeunload', function() {
                // Disconnect from Ably channel
                if (typeof Echo !== 'undefined') {
                    Echo.leave('trip.' + tripId);
                }
                
                // Unlock all selected seats on server
                selectedSeats.forEach(item => {
                    unlockSeatOnServer(item.seat);
                });
            });
        });
    </script>
    <style>
        .bg-pink {
            background-color: #ec4899 !important;
        }
    </style>
@endsection


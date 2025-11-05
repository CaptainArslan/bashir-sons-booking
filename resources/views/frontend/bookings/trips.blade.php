@extends('frontend.layouts.app')

@section('title', 'Select Trip')

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

        .progress-step i {
            font-size: 1.25rem;
        }

        .trip-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 1rem;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .trip-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background-color: #0d6efd;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .trip-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: #0d6efd;
        }

        .trip-card:hover::before {
            transform: scaleX(1);
        }

        .trip-card.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
            box-shadow: 0 8px 24px rgba(13, 110, 253, 0.3);
        }

        .trip-card.selected::before {
            transform: scaleX(1);
        }

        .trip-time-section {
            background-color: #0d6efd;
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 0.5rem 0;
        }

        .trip-badge {
            font-size: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .fare-highlight {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d6efd;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.active {
            display: block;
        }

        .route-info {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
        }

        .bus-info {
            font-size: 0.9rem;
            color: #718096;
        }

        .select-trip-btn {
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            background-color: #0d6efd;
            border: none;
        }

        .select-trip-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .no-trips-empty {
            padding: 4rem 2rem;
            text-align: center;
        }

        .no-trips-empty i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .route-banner {
            background-color: #0d6efd;
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .trip-card {
            animation: fadeInUp 0.5s ease forwards;
        }

        .trip-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .trip-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .trip-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .trip-card:nth-child(n+4) {
            animation-delay: 0.4s;
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
                            <i class="bi bi-clock-history"></i>
                            <span>2. Select Trip</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="progress-step">
                            <i class="bi bi-grid"></i>
                            <span>3. Select Seats</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route Banner -->
            <div class="route-banner">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="mb-2 fw-bold">
                            <i class="bi bi-map me-2"></i>
                            {{ $from_terminal->name }}
                            <i class="bi bi-arrow-right mx-3"></i>
                            {{ $to_terminal->name }}
                        </h3>
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <div>
                                <i class="bi bi-calendar-event me-2"></i>
                                <strong>{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</strong>
                            </div>
                            <div>
                                <i class="bi bi-people me-2"></i>
                                <strong>{{ $passengers }} Passenger{{ $passengers > 1 ? 's' : '' }}</strong>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('home') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Change Search
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i>Available Trips
                                </h5>
                                <div class="alert alert-info alert-sm mb-0 py-2 px-3" style="font-size: 0.875rem;">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>2-Hour Rule:</strong> Online bookings must be made at least 2 hours before
                                    departure
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="loading-spinner text-center py-5" id="loading-trips">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Loading available trips...</p>
                            </div>

                            <div id="trips-container" class="row g-3">
                                <!-- Trips will be loaded here -->
                            </div>

                            <div id="no-trips" class="no-trips-empty" style="display: none;">
                                <i class="bi bi-inbox text-muted"></i>
                                <h4 class="mt-3 mb-2">No trips available</h4>
                                <p class="text-muted mb-4">We couldn't find any trips for your selected route and date.
                                    Please try different dates or routes.</p>
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Note:</strong> Online bookings must be made at least 2 hours before departure.
                                    Trips departing within 2 hours are not available for online booking.
                                </div>
                                <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-left me-2"></i>Search Again
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const fromTerminalId = {{ $from_terminal_id }};
            const toTerminalId = {{ $to_terminal_id }};
            const date = '{{ $date }}';
            const passengers = {{ $passengers }};

            let selectedTripId = null;

            // Load trips
            loadTrips();

            function loadTrips() {
                $('#loading-trips').addClass('active');
                $('#trips-container').html('');
                $('#no-trips').hide();

                $.ajax({
                    url: "{{ route('frontend.bookings.load-trips') }}",
                    type: 'GET',
                    data: {
                        from_terminal_id: fromTerminalId,
                        to_terminal_id: toTerminalId,
                        date: date
                    },
                    success: function(response) {
                        $('#loading-trips').removeClass('active');

                        if (response.trips && response.trips.length > 0) {
                            renderTrips(response.trips);
                        } else {
                            $('#no-trips').show();
                        }
                    },
                    error: function(xhr) {
                        $('#loading-trips').removeClass('active');
                        let errorMsg = 'Failed to load trips';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }

            function renderTrips(trips) {
                const container = $('#trips-container');
                container.html('');

                trips.forEach(function(trip) {
                    const fareDisplay = trip.fare ?
                        `${trip.fare.currency} ${parseFloat(trip.fare.final_fare).toFixed(2)}` :
                        'Price on request';

                    const timeDisplay = trip.departure_time;
                    const arrivalDisplay = trip.arrival_time ?? '--';

                    const seatBadgeClass = trip.available_seats > 0 ? 'bg-success' :
                        trip.available_seats === 0 ? 'bg-danger' : 'bg-warning';

                    const seatBadgeText = trip.available_seats > 0 ?
                        `${trip.available_seats} Available` :
                        trip.available_seats === 0 ? 'Sold Out' : 'Limited';

                    const card = `
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card trip-card h-100" data-trip-id="${trip.trip_id}">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="flex-grow-1">
                                            <h5 class="route-info mb-2">${trip.route_name || 'Route'}</h5>
                                            <p class="bus-info mb-0">
                                                <i class="bi bi-bus-front me-1"></i>${trip.bus_name}
                                            </p>
                                        </div>
                                        <span class="badge trip-badge ${seatBadgeClass}">
                                            ${seatBadgeText}
                                        </span>
                                    </div>
                                    
                                    <div class="trip-time-section">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-center flex-grow-1">
                                                <small class="d-block opacity-75 mb-1">Departure</small>
                                                <strong class="fs-5">${timeDisplay}</strong>
                                            </div>
                                            <i class="bi bi-arrow-right fs-4 mx-3"></i>
                                            <div class="text-center flex-grow-1">
                                                <small class="d-block opacity-75 mb-1">Arrival</small>
                                                <strong class="fs-5">${arrivalDisplay}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <small class="text-muted d-block mb-1">Price per seat</small>
                                                <span class="fare-highlight">${fareDisplay}</span>
                                            </div>
                                        </div>
                                        <button class="btn select-trip-btn text-white w-100" data-trip-id="${trip.trip_id}" data-timetable-id="${trip.timetable_id}">
                                            <i class="bi bi-check-circle me-2"></i>Select This Trip
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    container.append(card);
                });

                // Add click handlers
                $('.trip-card').on('click', function(e) {
                    if (!$(e.target).closest('.select-trip-btn').length) {
                        const tripId = $(this).data('trip-id');
                        selectTrip(tripId);
                    }
                });

                $('.select-trip-btn').on('click', function(e) {
                    e.stopPropagation();
                    const tripId = $(this).data('trip-id');
                    proceedToSeats(tripId);
                });
            }

            function selectTrip(tripId) {
                selectedTripId = tripId;
                $('.trip-card').removeClass('selected');
                $(`.trip-card[data-trip-id="${tripId}"]`).addClass('selected');
            }

            function proceedToSeats(tripId) {
                if (!tripId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please Select a Trip',
                        text: 'Please select a trip to continue.',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                // Check if user is authenticated
                @auth
                // User is authenticated, proceed to seat selection
                const url = new URL('{{ route('frontend.bookings.select-seats') }}', window.location.origin);
                url.searchParams.append('trip_id', tripId);
                url.searchParams.append('from_terminal_id', fromTerminalId);
                url.searchParams.append('to_terminal_id', toTerminalId);
                url.searchParams.append('date', date);
                url.searchParams.append('passengers', passengers);

                window.location.href = url.toString();
            @else
                // User not authenticated, redirect to login with return URL
                Swal.fire({
                    icon: 'info',
                    title: 'Login Required',
                    text: 'Please login to continue with your booking.',
                    showCancelButton: true,
                    confirmButtonText: 'Login',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#0d6efd'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const loginUrl = new URL('{{ route('login') }}', window.location.origin);
                        loginUrl.searchParams.append('redirect', window.location.href);
                        window.location.href = loginUrl.toString();
                    }
                });
            @endauth
        }

        function formatTime(time) {
            if (!time) return '--';
            const parts = time.split(':');
            if (parts.length >= 2) {
                let hour = parseInt(parts[0]);
                const minute = parts[1];
                const ampm = hour >= 12 ? 'PM' : 'AM';
                hour = hour % 12 || 12;
                return `${hour}:${minute} ${ampm}`;
            }
            return time;
        }
        });
    </script>
@endsection

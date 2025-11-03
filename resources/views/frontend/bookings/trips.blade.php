@extends('frontend.layouts.app')

@section('title', 'Select Trip')

@section('styles')
    <style>
        .trip-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .trip-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-color: #0d6efd;
        }

        .trip-card.selected {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.active {
            display: block;
        }

        .seat-available {
            color: #28a745;
            font-weight: 600;
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
                                    <h4 class="mb-1">
                                        <i class="bi bi-map me-2 text-primary"></i>
                                        {{ $from_terminal->name }} ({{ $from_terminal->city->name }})
                                        <i class="bi bi-arrow-right mx-2"></i>
                                        {{ $to_terminal->name }} ({{ $to_terminal->city->name }})
                                    </h4>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-people me-1"></i>
                                        {{ $passengers }}
                                        <span>Passenger(s)</span>
                                    </p>
                                </div>
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Change Search
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>Available Trips
                            </h5>
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

                            <div id="no-trips" class="text-center py-5" style="display: none;">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <h5 class="mt-3">No trips available</h5>
                                <p class="text-muted">There are no trips available for the selected route and date.</p>
                                <a href="{{ route('home') }}" class="btn btn-primary">
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
                    url: '{{ route('frontend.bookings.load-trips') }}',
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

                    const timeDisplay = formatTime(trip.departure_time);
                    const arrivalDisplay = trip.arrival_time ? formatTime(trip.arrival_time) : '--';

                    const seatBadgeClass = trip.available_seats > 0 ? 'bg-success' :
                        trip.available_seats === 0 ? 'bg-danger' : 'bg-warning';

                    const card = `
                        <div class="col-md-6 col-lg-4">
                            <div class="card trip-card h-100" data-trip-id="${trip.trip_id}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1">${trip.route_name || 'Route'}</h5>
                                            <p class="text-muted mb-0 small">${trip.bus_name}</p>
                                        </div>
                                        <span class="badge ${seatBadgeClass}">
                                            ${trip.available_seats} Seats
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <small class="text-muted d-block">Departure</small>
                                            <strong>${timeDisplay}</strong>
                                        </div>
                                        <i class="bi bi-arrow-right text-primary"></i>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Arrival</small>
                                            <strong>${arrivalDisplay}</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Price per seat</small>
                                            <h6 class="mb-0 text-primary">${fareDisplay}</h6>
                                        </div>
                                        <button class="btn btn-primary btn-sm select-trip-btn" data-trip-id="${trip.trip_id}" data-timetable-id="${trip.timetable_id}">
                                            Select Trip
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


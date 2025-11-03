@extends('frontend.layouts.app')

@section('title', 'My Bookings')

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="bi bi-ticket-perforated me-2"></i>My Bookings
                        </h2>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                            <i class="bi bi-person me-2"></i>Back to Profile
                        </a>
                    </div>

                    @if ($userCnic)
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Showing bookings matched by CNIC:</strong> {{ $userCnic }}
                            <br>
                            <small>All bookings where you are the creator or any passenger matches your CNIC will be displayed here.</small>
                        </div>
                    @else
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No CNIC on profile:</strong> Please add your CNIC to your profile to view all bookings associated with you.
                            <a href="{{ route('profile.edit') }}" class="alert-link">Update Profile</a>
                        </div>
                    @endif

                    @if ($bookings->isEmpty())
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-ticket-x display-1 text-muted mb-3"></i>
                                <h4 class="text-muted">No Bookings Found</h4>
                                <p class="text-muted">You haven't made any bookings yet.</p>
                                <a href="{{ route('bookings') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Book a Ticket
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach ($bookings as $booking)
                                <div class="col-12">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }} text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0">
                                                        <i class="bi bi-ticket-perforated me-2"></i>
                                                        Booking #{{ $booking->booking_number }}
                                                    </h5>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    @if(($booking->status === 'hold' || $booking->payment_status === 'unpaid') && $booking->reserved_until && now()->lt($booking->reserved_until))
                                                        <div class="countdown-timer" data-reserved-until="{{ $booking->reserved_until->format('Y-m-d H:i:s') }}" data-booking-id="{{ $booking->id }}">
                                                            <i class="bi bi-clock me-2"></i>
                                                            <span class="timer-text fw-bold">00:00</span>
                                                            <small class="d-block" style="font-size: 0.7rem;">Payment Due</small>
                                                        </div>
                                                    @endif
                                                    <span class="badge bg-light text-dark">
                                                        {{ strtoupper($booking->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <!-- Route Information -->
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Route</small>
                                                        <strong class="d-block">{{ $booking->trip->route->name ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">From</small>
                                                        <strong class="d-block">
                                                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                                                            {{ $booking->fromStop->terminal->name ?? 'N/A' }}
                                                        </strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">To</small>
                                                        <strong class="d-block">
                                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                            {{ $booking->toStop->terminal->name ?? 'N/A' }}
                                                        </strong>
                                                    </div>
                                                </div>

                                                <!-- Trip Information -->
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Departure Date</small>
                                                        <strong class="d-block">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            {{ $booking->trip->departure_date?->format('M d, Y') ?? 'N/A' }}
                                                        </strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Departure Time</small>
                                                        <strong class="d-block">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $booking->trip->departure_datetime?->format('h:i A') ?? 'N/A' }}
                                                        </strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Bus</small>
                                                        <strong class="d-block">
                                                            <i class="bi bi-bus-front me-1"></i>
                                                            {{ $booking->trip->bus->name ?? 'N/A' }}
                                                            @if ($booking->trip->bus?->registration_number)
                                                                <small class="text-muted">({{ $booking->trip->bus->registration_number }})</small>
                                                            @endif
                                                        </strong>
                                                    </div>
                                                </div>

                                                <!-- Booking Details -->
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Passengers</small>
                                                        <strong class="d-block">
                                                            <i class="bi bi-people me-1"></i>
                                                            {{ $booking->total_passengers ?? $booking->passengers->count() }}
                                                        </strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Seats</small>
                                                        <strong class="d-block">
                                                            <i class="bi bi-grid-3x3-gap me-1"></i>
                                                            {{ $booking->seats->pluck('seat_number')->sort()->implode(', ') ?: 'N/A' }}
                                                        </strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block">Total Amount</small>
                                                        <strong class="d-block text-success">
                                                            <i class="bi bi-currency-dollar me-1"></i>
                                                            PKR {{ number_format($booking->final_amount ?? 0, 2) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Passengers List -->
                                            @if ($booking->passengers->isNotEmpty())
                                                <hr class="my-3">
                                                <h6 class="mb-2">
                                                    <i class="bi bi-person-lines-fill me-2"></i>Passengers
                                                </h6>
                                                <div class="row g-2">
                                                    @foreach ($booking->passengers as $passenger)
                                                        <div class="col-md-6">
                                                            <div class="card bg-light border-0 p-2">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <strong>{{ $passenger->name }}</strong>
                                                                        @if ($passenger->gender->value)
                                                                            <span class="badge bg-{{ $passenger->gender->value === 'male' ? 'primary' : 'danger' }} ms-2">
                                                                                {{ ucfirst($passenger->gender->value) }}
                                                                            </span>
                                                                        @endif
                                                                        @if ($passenger->age)
                                                                            <small class="text-muted d-block">Age: {{ $passenger->age }}</small>
                                                                        @endif
                                                                        @if ($passenger->cnic)
                                                                            <small class="text-muted d-block">
                                                                                CNIC: {{ $passenger->cnic }}
                                                                                @if ($userCnic && $passenger->cnic === $userCnic)
                                                                                    <span class="badge bg-success ms-1">Matched</span>
                                                                                @endif
                                                                            </small>
                                                                        @endif
                                                                        @if ($passenger->phone)
                                                                            <small class="text-muted d-block">Phone: {{ $passenger->phone }}</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <!-- Payment Information -->
                                            <hr class="my-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Payment Method</small>
                                                    <strong class="d-block">
                                                        {{ ucfirst(str_replace('_', ' ', $booking->payment_method ?? 'N/A')) }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Booking Date</small>
                                                    <strong class="d-block">
                                                        {{ $booking->created_at->format('M d, Y h:i A') }}
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                        @if(($booking->status === 'hold' || $booking->payment_status === 'unpaid') && $booking->reserved_until && now()->lt($booking->reserved_until))
                                            <div class="alert alert-warning mb-0 border-0 rounded-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                                        <strong>Payment Pending:</strong> Complete payment to confirm your booking
                                                    </div>
                                                    <a href="{{ route('frontend.bookings.payment', $booking) }}" class="btn btn-warning btn-sm">
                                                        <i class="bi bi-credit-card me-1"></i>Pay Now
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="card-footer bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted">
                                                        Channel: <strong>{{ ucfirst($booking->channel ?? 'N/A') }}</strong>
                                                    </small>
                                                </div>
                                                <div>
                                                    @if ($booking->status === 'confirmed')
                                                        <a href="#" class="btn btn-sm btn-outline-primary me-2" onclick="printTicket({{ $booking->id }})">
                                                            <i class="bi bi-printer me-1"></i>Print Ticket
                                                        </a>
                                                    @endif
                                                    <a href="#" class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-eye me-1"></i>View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .countdown-timer {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-align: center;
            min-width: 100px;
        }

        .countdown-timer .timer-text {
            font-size: 1.25rem;
            font-weight: 700;
            display: block;
        }

        .countdown-timer.expiring {
            background-color: rgba(220, 53, 69, 0.3);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        function printTicket(bookingId) {
            // Redirect to print ticket route
            window.open('/admin/bookings/' + bookingId + '/print', '_blank');
        }

        $(document).ready(function() {
            // Initialize countdown timers for unpaid bookings
            $('.countdown-timer').each(function() {
                const $timer = $(this);
                const reservedUntil = $timer.data('reserved-until');
                const bookingId = $timer.data('booking-id');
                
                if (!reservedUntil) {
                    return;
                }

                function updateCountdown() {
                    const now = new Date().getTime();
                    const endTime = new Date(reservedUntil).getTime();
                    const distance = endTime - now;

                    if (distance < 0) {
                        $timer.find('.timer-text').text('EXPIRED');
                        $timer.addClass('expiring');
                        
                        // Optionally reload the page after expiration
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                        return;
                    }

                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    const timeString = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                    $timer.find('.timer-text').text(timeString);

                    // Add warning class when less than 5 minutes
                    if (minutes < 5) {
                        $timer.addClass('expiring');
                    } else {
                        $timer.removeClass('expiring');
                    }
                }

                // Update immediately
                updateCountdown();

                // Update every second
                const interval = setInterval(updateCountdown, 1000);

                // Store interval ID for cleanup if needed
                $timer.data('interval-id', interval);
            });
        });
    </script>
@endsection

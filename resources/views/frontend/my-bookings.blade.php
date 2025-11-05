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
                            <small>All bookings where you are the creator or any passenger matches your CNIC will be
                                displayed here.</small>
                        </div>
                    @else
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No CNIC on profile:</strong> Please add your CNIC to your profile to view all bookings
                            associated with you.
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
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="bookings-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;">
                                                    <i class="bi bi-chevron-down"></i>
                                                </th>
                                                <th>Booking #</th>
                                                <th>Route</th>
                                                <th>From → To</th>
                                                <th>Date & Time</th>
                                                <th>Passengers</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bookings as $booking)
                                                <tr class="booking-row" data-booking-id="{{ $booking->id }}">
                                                    <td>
                                                        <button class="btn btn-sm btn-link text-dark p-0 toggle-details"
                                                            type="button">
                                                            <i class="bi bi-chevron-right"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $booking->booking_number }}</strong>
                                                        @if (
                                                            ($booking->status === 'hold' || $booking->payment_status === 'unpaid') &&
                                                                $booking->reserved_until &&
                                                                now()->lt($booking->reserved_until))
                                                            <div class="countdown-timer-small mt-1"
                                                                data-reserved-until="{{ $booking->reserved_until->format('Y-m-d H:i:s') }}"
                                                                data-booking-id="{{ $booking->id }}">
                                                                <small
                                                                    class="text-danger fw-bold timer-text-small">00:00</small>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small
                                                            class="text-muted d-block">{{ $booking->trip->route->name ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span
                                                                class="text-primary">{{ $booking->fromStop->terminal->name ?? 'N/A' }}</span>
                                                            <i class="bi bi-arrow-right mx-2 text-muted"></i>
                                                            <span
                                                                class="text-danger">{{ $booking->toStop->terminal->name ?? 'N/A' }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <small class="text-muted d-block">
                                                                <i
                                                                    class="bi bi-calendar me-1"></i>{{ $booking->trip->departure_date?->format('M d, Y') ?? 'N/A' }}
                                                            </small>
                                                            <small class="text-muted">
                                                                <i
                                                                    class="bi bi-clock me-1"></i>{{ $booking->trip->departure_datetime?->format('h:i A') ?? 'N/A' }}
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <i
                                                                class="bi bi-people me-1"></i>{{ $booking->total_passengers ?? $booking->passengers->count() }}
                                                        </span>
                                                        <small class="text-muted d-block mt-1">
                                                            Seats:
                                                            {{ $booking->seats->pluck('seat_number')->sort()->take(3)->implode(', ') }}{{ $booking->seats->count() > 3 ? '...' : '' }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success">PKR
                                                            {{ number_format($booking->final_amount ?? 0, 2) }}</strong>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                                            {{ strtoupper($booking->status) }}
                                                        </span>
                                                        @if ($booking->payment_status === 'unpaid')
                                                            <small class="text-danger d-block mt-1">Unpaid</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            @if (
                                                                ($booking->status === 'hold' || $booking->payment_status === 'unpaid') &&
                                                                    $booking->reserved_until &&
                                                                    now()->lt($booking->reserved_until))
                                                                <a href="{{ route('frontend.bookings.payment', $booking) }}"
                                                                    class="btn btn-warning" title="Pay Now">
                                                                    <i class="bi bi-credit-card"></i>
                                                                </a>
                                                            @endif
                                                            @if ($booking->status === 'confirmed')
                                                                <button class="btn btn-outline-primary"
                                                                    onclick="printTicket({{ $booking->id }})"
                                                                    title="Print Ticket">
                                                                    <i class="bi bi-printer"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                <!-- Detailed Information Row -->
                                                <tr class="booking-details-row" style="display: none;">
                                                    <td colspan="9">
                                                        <div class="p-4 bg-light">
                                                            <div class="row g-4">
                                                                <!-- Route & Trip Information -->
                                                                <div class="col-md-6">
                                                                    <h6
                                                                        class="mb-3 fw-bold text-primary border-bottom pb-2">
                                                                        <i class="bi bi-route me-2"></i>Route & Trip Details
                                                                    </h6>
                                                                    <table class="table table-sm table-borderless mb-0">
                                                                        <tr>
                                                                            <td style="width: 40%;" class="text-muted">
                                                                                Route:</td>
                                                                            <td><strong>{{ $booking->trip->route->name ?? 'N/A' }}</strong>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">From Terminal:</td>
                                                                            <td>
                                                                                <i
                                                                                    class="bi bi-geo-alt-fill text-primary me-1"></i>
                                                                                <strong>{{ $booking->fromStop->terminal->name ?? 'N/A' }}</strong>
                                                                                @if ($booking->fromStop->terminal->code)
                                                                                    <small
                                                                                        class="text-muted">({{ $booking->fromStop->terminal->code }})</small>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">To Terminal:</td>
                                                                            <td>
                                                                                <i
                                                                                    class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                                                <strong>{{ $booking->toStop->terminal->name ?? 'N/A' }}</strong>
                                                                                @if ($booking->toStop->terminal->code)
                                                                                    <small
                                                                                        class="text-muted">({{ $booking->toStop->terminal->code }})</small>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Departure Date:</td>
                                                                            <td><strong>{{ $booking->trip->departure_date?->format('M d, Y') ?? 'N/A' }}</strong>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Departure Time:</td>
                                                                            <td><strong>{{ $booking->trip->departure_datetime?->format('h:i A') ?? 'N/A' }}</strong>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Bus:</td>
                                                                            <td>
                                                                                <i class="bi bi-bus-front me-1"></i>
                                                                                <strong>{{ $booking->trip->bus->name ?? 'N/A' }}</strong>
                                                                                @if ($booking->trip->bus?->registration_number)
                                                                                    <small
                                                                                        class="text-muted">({{ $booking->trip->bus->registration_number }})</small>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>

                                                                <!-- Booking & Payment Information -->
                                                                <div class="col-md-6">
                                                                    <h6
                                                                        class="mb-3 fw-bold text-primary border-bottom pb-2">
                                                                        <i class="bi bi-receipt-cutoff me-2"></i>Booking &
                                                                        Payment Details
                                                                    </h6>
                                                                    <table class="table table-sm table-borderless mb-0">
                                                                        <tr>
                                                                            <td style="width: 40%;" class="text-muted">
                                                                                Booking Date:</td>
                                                                            <td><strong>{{ $booking->created_at->format('M d, Y h:i A') }}</strong>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Channel:</td>
                                                                            <td><strong>{{ ucfirst($booking->channel ?? 'N/A') }}</strong>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Payment Status:</td>
                                                                            <td>
                                                                                <span
                                                                                    class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'danger' }}">
                                                                                    {{ ucfirst($booking->payment_status ?? 'N/A') }}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Payment Method:</td>
                                                                            <td><strong>{{ ucfirst(str_replace('_', ' ', $booking->payment_method ?? 'N/A')) }}</strong>
                                                                            </td>
                                                                        </tr>
                                                                        @if ($booking->online_transaction_id)
                                                                            <tr>
                                                                                <td class="text-muted">Transaction ID:</td>
                                                                                <td><strong>{{ $booking->online_transaction_id }}</strong>
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        <tr>
                                                                            <td class="text-muted">Subtotal:</td>
                                                                            <td>PKR
                                                                                {{ number_format($booking->total_fare ?? 0, 2) }}
                                                                            </td>
                                                                        </tr>
                                                                        @if ($booking->discount_amount > 0)
                                                                            <tr>
                                                                                <td class="text-muted">Discount:</td>
                                                                                <td class="text-success">-PKR
                                                                                    {{ number_format($booking->discount_amount ?? 0, 2) }}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if ($booking->tax_amount > 0)
                                                                            <tr>
                                                                                <td class="text-muted">Tax:</td>
                                                                                <td>PKR
                                                                                    {{ number_format($booking->tax_amount ?? 0, 2) }}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        <tr class="border-top">
                                                                            <td class="text-muted"><strong>Total
                                                                                    Amount:</strong></td>
                                                                            <td><strong class="text-success fs-5">PKR
                                                                                    {{ number_format($booking->final_amount ?? 0, 2) }}</strong>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>

                                                                <!-- Passengers & Seats -->
                                                                <div class="col-12">
                                                                    <h6
                                                                        class="mb-3 fw-bold text-primary border-bottom pb-2">
                                                                        <i
                                                                            class="bi bi-person-lines-fill me-2"></i>Passengers
                                                                        & Seats
                                                                    </h6>
                                                                    <div class="row g-3">
                                                                        @foreach ($booking->passengers as $index => $passenger)
                                                                            @php
                                                                                $seat = $booking->seats
                                                                                    ->skip($index)
                                                                                    ->first();
                                                                            @endphp
                                                                            <div class="col-md-6">
                                                                                <div class="card border">
                                                                                    <div class="card-body p-3">
                                                                                        <div
                                                                                            class="d-flex justify-content-between align-items-start mb-2">
                                                                                            <div>
                                                                                                <strong
                                                                                                    class="fs-6">{{ $passenger->name }}</strong>
                                                                                                @if ($passenger->gender && $passenger->gender->value)
                                                                                                    <span
                                                                                                        class="badge bg-{{ $passenger->gender->value === 'male' ? 'primary' : 'danger' }} ms-2">
                                                                                                        {{ ucfirst($passenger->gender->value) }}
                                                                                                    </span>
                                                                                                @endif
                                                                                                @if ($userCnic && $passenger->cnic === $userCnic)
                                                                                                    <span
                                                                                                        class="badge bg-success ms-1">You</span>
                                                                                                @endif
                                                                                            </div>
                                                                                            @if ($seat)
                                                                                                <span
                                                                                                    class="badge bg-secondary">Seat
                                                                                                    #{{ $seat->seat_number }}</span>
                                                                                            @endif
                                                                                        </div>
                                                                                        <div class="row g-2">
                                                                                            @if ($passenger->age)
                                                                                                <div class="col-6">
                                                                                                    <small
                                                                                                        class="text-muted">Age:</small>
                                                                                                    <strong
                                                                                                        class="d-block">{{ $passenger->age }}</strong>
                                                                                                </div>
                                                                                            @endif
                                                                                            @if ($passenger->cnic)
                                                                                                <div class="col-6">
                                                                                                    <small
                                                                                                        class="text-muted">CNIC:</small>
                                                                                                    <strong
                                                                                                        class="d-block">{{ $passenger->cnic }}</strong>
                                                                                                </div>
                                                                                            @endif
                                                                                            @if ($passenger->phone)
                                                                                                <div class="col-6">
                                                                                                    <small
                                                                                                        class="text-muted">Phone:</small>
                                                                                                    <strong
                                                                                                        class="d-block">{{ $passenger->phone }}</strong>
                                                                                                </div>
                                                                                            @endif
                                                                                            @if ($passenger->email)
                                                                                                <div class="col-6">
                                                                                                    <small
                                                                                                        class="text-muted">Email:</small>
                                                                                                    <strong
                                                                                                        class="d-block">{{ $passenger->email }}</strong>
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <strong class="text-muted">All Seats:</strong>
                                                                        <span
                                                                            class="badge bg-secondary ms-2">{{ $booking->seats->pluck('seat_number')->sort()->implode(', ') }}</span>
                                                                    </div>
                                                                </div>

                                                                <!-- Payment Warning (if applicable) -->
                                                                @if (
                                                                    ($booking->status === 'hold' || $booking->payment_status === 'unpaid') &&
                                                                        $booking->reserved_until &&
                                                                        now()->lt($booking->reserved_until))
                                                                    <div class="col-12">
                                                                        <div
                                                                            class="alert alert-warning d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <i
                                                                                    class="bi bi-exclamation-triangle me-2"></i>
                                                                                <strong>Payment Pending:</strong> Complete
                                                                                payment to confirm your booking
                                                                            </div>
                                                                            <a href="{{ route('frontend.bookings.payment', $booking) }}"
                                                                                class="btn btn-warning">
                                                                                <i class="bi bi-credit-card me-2"></i>Pay
                                                                                Now
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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

        .countdown-timer-small {
            font-size: 0.75rem;
        }

        .countdown-timer-small .timer-text-small {
            font-size: 0.85rem;
        }

        .booking-details-row {
            background-color: #f8f9fa;
        }

        .booking-row:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .toggle-details i {
            transition: transform 0.3s ease;
        }

        .toggle-details.active i {
            transform: rotate(90deg);
        }

        @keyframes pulse {

            0%,
            100% {
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
            // Toggle booking details
            $('.toggle-details').on('click', function(e) {
                e.stopPropagation();
                const $row = $(this).closest('.booking-row');
                const $detailsRow = $row.next('.booking-details-row');
                const $icon = $(this).find('i');

                if ($detailsRow.is(':visible')) {
                    $detailsRow.slideUp();
                    $icon.removeClass('bi-chevron-down').addClass('bi-chevron-right');
                    $(this).removeClass('active');
                } else {
                    $detailsRow.slideDown();
                    $icon.removeClass('bi-chevron-right').addClass('bi-chevron-down');
                    $(this).addClass('active');
                }
            });

            // Initialize countdown timers for unpaid bookings
            $('.countdown-timer, .countdown-timer-small').each(function() {
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

                    const timerTextSelector = $timer.hasClass('countdown-timer-small') ?
                        '.timer-text-small' : '.timer-text';

                    if (distance < 0) {
                        $timer.find(timerTextSelector).text('EXPIRED');
                        $timer.addClass('expiring');

                        // Optionally reload the page after expiration
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                        return;
                    }

                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    const timeString = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2,
                        '0');
                    $timer.find(timerTextSelector).text(timeString);

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

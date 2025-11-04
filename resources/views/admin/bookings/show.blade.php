@php
    // Helper functions to safely get enum values
    $bookingStatus = null;
    try {
        $bookingStatus = \App\Enums\BookingStatusEnum::from($booking->status ?? '');
    } catch (\ValueError $e) {
        // Handle non-enum statuses like 'checked_in', 'boarded'
    }
    
    $paymentStatus = null;
    try {
        $paymentStatus = \App\Enums\PaymentStatusEnum::from($booking->payment_status ?? '');
    } catch (\ValueError $e) {
        // Handle non-enum statuses like 'partial'
    }
    
    $channel = null;
    try {
        $channel = \App\Enums\ChannelEnum::from($booking->channel ?? '');
    } catch (\ValueError $e) {
    }
    
    $paymentMethod = null;
    if ($booking->payment_method) {
        try {
            $paymentMethod = \App\Enums\PaymentMethodEnum::from($booking->payment_method);
        } catch (\ValueError $e) {
        }
    }
    
    $activeSeats = $booking->seats->whereNull('cancelled_at');
    $cancelledSeats = $booking->seats->whereNotNull('cancelled_at');
@endphp

<!-- Booking Header -->
<div class="row mb-3">
    <div class="col-md-8">
        <h5 class="fw-bold mb-2">Booking #{{ $booking->booking_number }}</h5>
        <p class="text-muted mb-0">
            Created on {{ $booking->created_at->format('d M Y, H:i A') }}
            @if($booking->bookedByUser)
                by <strong>{{ $booking->bookedByUser->name }}</strong>
            @elseif($booking->user)
                by <strong>{{ $booking->user->name }}</strong>
            @else
                by <strong>System</strong>
            @endif
        </p>
    </div>
    <div class="col-md-4 text-end">
        @if($bookingStatus)
            <span class="badge {{ $bookingStatus->getBadge() }} me-2" style="padding: 8px 12px; font-size: 0.9rem;">
                <i class="{{ $bookingStatus->getIcon() }}"></i> {{ $bookingStatus->getLabel() }}
            </span>
        @else
            <span class="badge bg-secondary me-2" style="padding: 8px 12px; font-size: 0.9rem;">
                {{ ucfirst($booking->status ?? 'Unknown') }}
            </span>
        @endif
        
        @if($paymentStatus)
            <span class="badge {{ $paymentStatus->getBadge() }}" style="padding: 8px 12px; font-size: 0.9rem;">
                <i class="{{ $paymentStatus->getIcon() }}"></i> {{ $paymentStatus->getLabel() }}
            </span>
        @else
            <span class="badge {{ $booking->payment_status === 'partial' ? 'bg-warning' : 'bg-secondary' }}" style="padding: 8px 12px; font-size: 0.9rem;">
                {{ ucfirst($booking->payment_status ?? 'Unknown') }}
            </span>
        @endif
    </div>
</div>

<!-- Trip & Route Details -->
<div class="row mb-3">
    <div class="col-12 mb-3">
        <h6 class="fw-bold mb-3"><i class="bx bx-route"></i> Route Details</h6>
        <div class="card border">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="small text-muted" style="width: 150px;"><i class="bx bx-map"></i> Route:</td>
                                <td class="small">
                                    <strong>{{ $booking->trip?->route?->name ?? 'N/A' }}</strong>
                                    @if($booking->trip?->route?->code)
                                        <span class="badge bg-secondary ms-2">{{ $booking->trip->route->code }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted" style="width: 150px;"><i class="bx bx-calendar"></i> Departure Date:</td>
                                <td class="small">
                                    <strong>{{ $booking->trip?->departure_datetime?->format('d M Y') ?? 'N/A' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="small text-muted"><i class="bx bx-map-pin"></i> From Terminal:</td>
                                <td class="small">
                                    <strong>{{ $booking->fromStop?->terminal?->name ?? 'N/A' }}</strong>
                                    @if($booking->fromStop?->terminal?->code)
                                        <span class="badge bg-info ms-2">{{ $booking->fromStop->terminal->code }}</span>
                                    @endif
                                    @if($booking->fromStop?->terminal?->city)
                                        <small class="text-muted d-block mt-1">{{ $booking->fromStop->terminal->city->name ?? '' }}</small>
                                    @endif
                                </td>
                                <td class="small text-muted"><i class="bx bx-map-pin"></i> To Terminal:</td>
                                <td class="small">
                                    <strong>{{ $booking->toStop?->terminal?->name ?? 'N/A' }}</strong>
                                    @if($booking->toStop?->terminal?->code)
                                        <span class="badge bg-info ms-2">{{ $booking->toStop->terminal->code }}</span>
                                    @endif
                                    @if($booking->toStop?->terminal?->city)
                                        <small class="text-muted d-block mt-1">{{ $booking->toStop->terminal->city->name ?? '' }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="small text-muted"><i class="bx bx-time"></i> Departure Time:</td>
                                <td class="small">
                                    <strong class="text-primary">{{ $booking->trip?->departure_datetime?->format('H:i A') ?? 'N/A' }}</strong>
                                    <small class="text-muted ms-2">({{ $booking->trip?->departure_datetime?->format('d M Y') ?? '' }})</small>
                                </td>
                                <td class="small text-muted"><i class="bx bx-time"></i> Arrival Time:</td>
                                <td class="small">
                                    @if($booking->trip?->arrival_datetime)
                                        <strong class="text-success">{{ $booking->trip->arrival_datetime->format('H:i A') }}</strong>
                                        <small class="text-muted ms-2">({{ $booking->trip->arrival_datetime->format('d M Y') }})</small>
                                    @elseif($booking->toStop?->arrival_at)
                                        <strong class="text-success">{{ \Carbon\Carbon::parse($booking->toStop->arrival_at)->format('H:i A') }}</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @if($booking->trip?->bus)
                                <tr>
                                    <td class="small text-muted"><i class="bx bx-bus"></i> Bus:</td>
                                    <td class="small">
                                        <strong>{{ $booking->trip->bus->name ?? 'N/A' }}</strong>
                                        @if($booking->trip->bus->registration_number)
                                            <span class="badge bg-dark ms-2">{{ $booking->trip->bus->registration_number }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted"><i class="bx bx-user"></i> Driver:</td>
                                    <td class="small">
                                        @if($booking->trip->driver_name)
                                            <strong>{{ $booking->trip->driver_name }}</strong>
                                            @if($booking->trip->driver_phone)
                                                <small class="text-muted d-block mt-1"><i class="bx bx-phone"></i> {{ $booking->trip->driver_phone }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Not Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mb-3">
        <h6 class="fw-bold mb-3"><i class="bx bx-info-circle"></i> Booking Details</h6>
        <div class="card border">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="small text-muted" style="width: 150px;"><i class="bx bx-purchase-tag"></i> Booking Number:</td>
                                        <td class="small"><strong class="text-primary">#{{ $booking->booking_number }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="small text-muted"><i class="bx bx-store"></i> Booking Channel:</td>
                                        <td class="small">
                                            @if($channel)
                                                <span class="badge {{ $channel->getBadge() }}">
                                                    <i class="{{ $channel->getIcon() }}"></i> {{ $channel->getLabel() }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($booking->channel ?? 'N/A') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="small text-muted"><i class="bx bx-user"></i> Booked By:</td>
                                        <td class="small">
                                            @if($booking->bookedByUser)
                                                <strong>{{ $booking->bookedByUser->name }}</strong>
                                            @elseif($booking->user)
                                                <strong>{{ $booking->user->name }}</strong>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="small text-muted"><i class="bx bx-calendar-check"></i> Booking Date:</td>
                                        <td class="small">
                                            <strong>{{ $booking->created_at->format('d M Y, H:i A') }}</strong>
                                        </td>
                                    </tr>
                                    @if($booking->confirmed_at)
                                        <tr>
                                            <td class="small text-muted"><i class="bx bx-check-circle"></i> Confirmed At:</td>
                                            <td class="small">
                                                <strong class="text-success">{{ $booking->confirmed_at->format('d M Y, H:i A') }}</strong>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="small text-muted" style="width: 150px;"><i class="bx bx-chair"></i> Total Seats:</td>
                                        <td class="small">
                                            <span class="badge bg-info">{{ $activeSeats->count() }} active</span>
                                            @if($cancelledSeats->count() > 0)
                                                <span class="badge bg-danger ms-1">{{ $cancelledSeats->count() }} cancelled</span>
                                            @endif
                                            <span class="badge bg-secondary ms-1">{{ $booking->seats->count() }} total</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="small text-muted"><i class="bx bx-group"></i> Total Passengers:</td>
                                        <td class="small">
                                            <strong><span class="badge bg-secondary">{{ $booking->passengers->count() }} passenger(s)</span></strong>
                                        </td>
                                    </tr>
                                    @if($booking->reserved_until)
                                        <tr>
                                            <td class="small text-muted"><i class="bx bx-time-five"></i> Reserved Until:</td>
                                            <td class="small">
                                                <strong class="text-warning">{{ $booking->reserved_until->format('d M Y, H:i A') }}</strong>
                                                @if($booking->reserved_until->isPast())
                                                    <span class="badge bg-danger ms-2">Expired</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    @if($booking->terminal)
                                        <tr>
                                            <td class="small text-muted"><i class="bx bx-building"></i> Terminal:</td>
                                            <td class="small">
                                                <strong>{{ $booking->terminal->name ?? 'N/A' }}</strong>
                                                @if($booking->terminal->code)
                                                    <span class="badge bg-info ms-2">{{ $booking->terminal->code }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="small text-muted"><i class="bx bx-update"></i> Last Updated:</td>
                                        <td class="small">
                                            <strong>{{ $booking->updated_at->format('d M Y, H:i A') }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if($booking->cancelled_at)
                    <hr class="my-3">
                    <div class="alert alert-danger border mb-0">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 small"><strong><i class="bx bx-error-circle"></i> Cancelled On:</strong></p>
                                <p class="mb-0 small">{{ $booking->cancelled_at->format('d M Y, H:i A') }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small"><strong><i class="bx bx-user"></i> Cancelled By:</strong></p>
                                <p class="mb-0 small">
                                    @if($booking->cancelled_by_type)
                                        <span class="badge {{ $booking->cancelled_by_type === 'admin' ? 'bg-danger' : ($booking->cancelled_by_type === 'employee' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ ucfirst($booking->cancelled_by_type) }}
                                        </span>
                                    @endif
                                    @if($booking->cancelledByUser)
                                        <strong class="ms-1">{{ $booking->cancelledByUser->name }}</strong>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4">
                                @if($booking->cancellation_reason)
                                    <p class="mb-1 small"><strong><i class="bx bx-info-circle"></i> Reason:</strong></p>
                                    <p class="mb-0 small">{{ $booking->cancellation_reason }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Seats & Passengers Section -->
<div class="row mb-3">
    <div class="col-12 mb-3">
        <h6 class="fw-bold mb-2"><i class="bx bx-chair"></i> Booked Seats</h6>
        <div class="card border">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">Seat #</th>
                                <th class="small">Gender</th>
                                <th class="small">Fare (PKR)</th>
                                <th class="small">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->seats as $seat)
                                <tr class="{{ $seat->cancelled_at ? 'table-secondary opacity-75' : '' }}">
                                    <td class="small">
                                        <strong>{{ $seat->seat_number }}</strong>
                                    </td>
                                    <td class="small">
                                        <i class="{{ \App\Enums\GenderEnum::getGenderIcon($seat->gender?->value ?? $seat->gender) }}"></i>
                                        {{ \App\Enums\GenderEnum::getGenderName($seat->gender?->value ?? $seat->gender) }}
                                    </td>
                                    <td class="small">{{ number_format($seat->fare, 2) }}</td>
                                    <td class="small">
                                        @if($seat->cancelled_at)
                                            <span class="badge bg-danger">
                                                <i class="bx bx-x-circle"></i> Cancelled
                                            </span>
                                            @if($seat->cancellation_reason)
                                                <br>
                                                <small class="text-danger">
                                                    <i class="bx bx-info-circle"></i> {{ Str::limit($seat->cancellation_reason, 30) }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="badge bg-success">
                                                <i class="bx bx-check-circle"></i> Active
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted small">No seats found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mb-3">
        <h6 class="fw-bold mb-2"><i class="bx bx-group"></i> Passengers Information</h6>
        <div class="card border">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">#</th>
                                <th class="small">Name</th>
                                <th class="small">Gender</th>
                                <th class="small">Age</th>
                                <th class="small">CNIC</th>
                                <th class="small">Phone</th>
                                <th class="small">Email</th>
                                <th class="small">Seat #</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->passengers as $index => $passenger)
                                @php
                                    $passengerSeat = $booking->seats->sortBy('seat_number')->values()->get($index);
                                @endphp
                                <tr>
                                    <td class="small"><strong>{{ $index + 1 }}</strong></td>
                                    <td class="small">
                                        <strong>{{ $passenger->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td class="small">
                                        <i class="{{ \App\Enums\GenderEnum::getGenderIcon($passenger->gender?->value ?? $passenger->gender) }} {{ ($passenger->gender?->value ?? $passenger->gender) === 'male' ? 'text-primary' : 'text-danger' }}"></i>
                                        {{ \App\Enums\GenderEnum::getGenderName($passenger->gender?->value ?? $passenger->gender) }}
                                    </td>
                                    <td class="small">{{ $passenger->age ?? 'N/A' }}</td>
                                    <td class="small">{{ $passenger->cnic ?? 'N/A' }}</td>
                                    <td class="small">
                                        @if($passenger->phone)
                                            <i class="bx bx-phone"></i> {{ $passenger->phone }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($passenger->email)
                                            <i class="bx bx-envelope"></i> {{ $passenger->email }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($passengerSeat)
                                            <span class="badge {{ $passengerSeat->cancelled_at ? 'bg-danger' : 'bg-info' }}">
                                                Seat {{ $passengerSeat->seat_number }}
                                                @if($passengerSeat->cancelled_at)
                                                    <i class="bx bx-x-circle"></i>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted small">No passengers found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fare Breakdown & Payment Summary -->
<div class="row mb-3">
    <div class="col-12 mb-3">
        <h6 class="fw-bold mb-2"><i class="bx bx-calculator"></i> Fare Breakdown & Payment Summary</h6>
        <div class="card border">
            <div class="card-body p-3">
                <div class="row">
                    <!-- Fare Breakdown -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bx bx-receipt"></i> Fare Breakdown</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="small text-muted">Base Fare:</td>
                                    <td class="small text-end"><strong>PKR {{ number_format($booking->total_fare, 2) }}</strong></td>
                                </tr>
                                @if($booking->discount_amount > 0)
                                    <tr>
                                        <td class="small text-muted">Discount:</td>
                                        <td class="small text-end text-danger"><strong>-PKR {{ number_format($booking->discount_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                @if($booking->tax_amount > 0)
                                    <tr>
                                        <td class="small text-muted">Tax/Service Charge:</td>
                                        <td class="small text-end text-success"><strong>+PKR {{ number_format($booking->tax_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="small fw-bold">Total Amount:</td>
                                    <td class="small text-end fw-bold text-success"><strong>PKR {{ number_format($booking->final_amount, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Payment Details -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bx bx-credit-card"></i> Payment Details</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="small text-muted">Payment Method:</td>
                                    <td class="small text-end">
                                        @if($paymentMethod)
                                            <span class="badge {{ $paymentMethod->getBadge() }}">
                                                <i class="{{ $paymentMethod->getIcon() }}"></i> {{ $paymentMethod->getLabel() }}
                                            </span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($booking->payment_method ?? 'Cash') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($booking->online_transaction_id)
                                    <tr>
                                        <td class="small text-muted">Transaction ID:</td>
                                        <td class="small text-end">
                                            <code class="small">{{ $booking->online_transaction_id }}</code>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="small text-muted">Payment Status:</td>
                                    <td class="small text-end">
                                        @if($paymentStatus)
                                            <span class="badge {{ $paymentStatus->getBadge() }}">
                                                <i class="{{ $paymentStatus->getIcon() }}"></i> {{ $paymentStatus->getLabel() }}
                                            </span>
                                        @else
                                            <span class="badge {{ $booking->payment_status === 'partial' ? 'bg-warning' : 'bg-secondary' }}">
                                                {{ ucfirst($booking->payment_status ?? 'Unknown') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @if($booking->payment_received_from_customer)
                                    <tr>
                                        <td class="small text-muted">Amount Received:</td>
                                        <td class="small text-end"><strong>PKR {{ number_format($booking->payment_received_from_customer, 2) }}</strong></td>
                                    </tr>
                                @endif
                                @if($booking->return_after_deduction_from_customer > 0)
                                    <tr class="border-top">
                                        <td class="small text-muted">Return to Customer:</td>
                                        <td class="small text-end fw-bold text-success"><strong>PKR {{ number_format($booking->return_after_deduction_from_customer, 2) }}</strong></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes -->
@if($booking->notes)
    <div class="row mb-3">
        <div class="col-12">
            <h6 class="fw-bold mb-2"><i class="bx bx-note"></i> Notes</h6>
            <div class="alert alert-light border small mb-0">
                {{ $booking->notes }}
            </div>
        </div>
    </div>
@endif

<!-- Booking Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h5 class="fw-bold mb-2">Booking #{{ $booking->booking_number }}</h5>
        <p class="text-muted mb-0">
            Created on {{ $booking->created_at->format('d M Y, H:i A') }}
            by <strong>{{ $booking->user?->name ?? 'N/A' }}</strong>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <span class="badge bg-success me-2" style="padding: 8px 12px; font-size: 0.9rem;">
            {{ ucfirst($booking->status) }}
        </span>
        <span class="badge bg-info" style="padding: 8px 12px; font-size: 0.9rem;">
            {{ ucfirst($booking->payment_status) }} Payment
        </span>
    </div>
</div>

<!-- Trip & Route Details -->
<div class="row mb-4">
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-route"></i> Route Details</h6>
        <div class="card bg-light">
            <div class="card-body">
                <p class="mb-2"><strong>Route:</strong> {{ $booking->trip?->route?->name ?? 'N/A' }}</p>
                <p class="mb-2"><strong>From:</strong> {{ $booking->fromStop?->terminal?->name }} ({{ $booking->fromStop?->terminal?->code }})</p>
                <p class="mb-2"><strong>To:</strong> {{ $booking->toStop?->terminal?->name }} ({{ $booking->toStop?->terminal?->code }})</p>
                <p class="mb-0"><strong>Departure:</strong> {{ $booking->trip?->departure_datetime?->format('d M Y, H:i A') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle"></i> Booking Details</h6>
        <div class="card bg-light">
            <div class="card-body">
                <p class="mb-2"><strong>Booking Channel:</strong> 
                    @if($booking->channel === 'counter')
                        <i class="fas fa-store"></i> Counter
                    @elseif($booking->channel === 'phone')
                        <i class="fas fa-phone"></i> Phone
                    @else
                        <i class="fas fa-globe"></i> Online
                    @endif
                </p>
                <p class="mb-2"><strong>Total Seats:</strong> <span class="badge bg-info">{{ $booking->seats->count() }} seat(s)</span></p>
                <p class="mb-2"><strong>Total Passengers:</strong> <span class="badge bg-secondary">{{ $booking->passengers->count() }} passenger(s)</span></p>
                <p class="mb-0"><strong>Reserved Until:</strong> {{ $booking->reserved_until?->format('d M Y, H:i A') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Seats & Passengers Section -->
<div class="row mb-4">
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-chair"></i> Booked Seats</h6>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Seat #</th>
                                <th>Gender</th>
                                <th>Fare (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->seats as $seat)
                                <tr>
                                    <td>
                                        <strong>{{ $seat->seat_number }}</strong>
                                    </td>
                                    <td>
                                        @if($seat->gender === 'male')
                                            <i class="fas fa-mars"></i> Male
                                        @else
                                            <i class="fas fa-venus"></i> Female
                                        @endif
                                    </td>
                                    <td>{{ number_format($seat->fare, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No seats found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-users"></i> Passengers</h6>
        <div class="card">
            <div class="card-body">
                <div style="max-height: 300px; overflow-y: auto;">
                    @forelse($booking->passengers as $passenger)
                        <div class="mb-3 p-2 border-bottom">
                            <p class="mb-1"><strong>{{ $passenger->name }}</strong> 
                                @if($passenger->gender === 'male')
                                    <i class="fas fa-mars text-primary"></i>
                                @else
                                    <i class="fas fa-venus text-danger"></i>
                                @endif
                            </p>
                            <small class="text-muted">
                                @if($passenger->age) Age: {{ $passenger->age }} | @endif
                                @if($passenger->cnic) CNIC: {{ $passenger->cnic }} @endif
                            </small>
                            @if($passenger->phone)
                                <p class="mb-0 small"><i class="fas fa-phone"></i> {{ $passenger->phone }}</p>
                            @endif
                            @if($passenger->email)
                                <p class="mb-0 small"><i class="fas fa-envelope"></i> {{ $passenger->email }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">No passengers found</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fare Breakdown -->
<div class="row mb-4">
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-calculator"></i> Fare Breakdown</h6>
        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Total Fare:</span>
                        <strong>PKR {{ number_format($booking->total_fare, 2) }}</strong>
                    </div>
                </div>
                @if($booking->discount_amount > 0)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Discount:</span>
                            <strong class="text-danger">-PKR {{ number_format($booking->discount_amount, 2) }}</strong>
                        </div>
                    </div>
                @endif
                @if($booking->tax_amount > 0)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Tax/Service Charge:</span>
                            <strong class="text-success">+PKR {{ number_format($booking->tax_amount, 2) }}</strong>
                        </div>
                    </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Final Amount:</strong>
                    <strong class="text-success" style="font-size: 1.1rem;">PKR {{ number_format($booking->final_amount, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-credit-card"></i> Payment Details</h6>
        <div class="card">
            <div class="card-body">
                <p class="mb-2"><strong>Payment Method:</strong> 
                    <span class="badge bg-info">{{ ucfirst($booking->payment_method ?? 'Cash') }}</span>
                </p>
                @if($booking->online_transaction_id)
                    <p class="mb-2"><strong>Transaction ID:</strong> 
                        <code>{{ $booking->online_transaction_id }}</code>
                    </p>
                @endif
                <p class="mb-2"><strong>Payment Status:</strong> 
                    <span class="badge {{ $booking->payment_status === 'paid' ? 'bg-success' : ($booking->payment_status === 'unpaid' ? 'bg-danger' : 'bg-warning') }}">
                        {{ ucfirst($booking->payment_status) }}
                    </span>
                </p>
                @if($booking->payment_received_from_customer)
                    <p class="mb-2"><strong>Amount Received:</strong> 
                        <strong>PKR {{ number_format($booking->payment_received_from_customer, 2) }}</strong>
                    </p>
                @endif
                @if($booking->return_after_deduction_from_customer > 0)
                    <p class="mb-0"><strong>Return to Customer:</strong> 
                        <strong class="text-success">PKR {{ number_format($booking->return_after_deduction_from_customer, 2) }}</strong>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Notes -->
@if($booking->notes)
    <div class="row mb-4">
        <div class="col-12">
            <h6 class="fw-bold mb-3"><i class="fas fa-sticky-note"></i> Notes</h6>
            <div class="alert alert-light">
                {{ $booking->notes }}
            </div>
        </div>
    </div>
@endif

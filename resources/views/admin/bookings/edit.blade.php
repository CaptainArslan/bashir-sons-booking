@extends('admin.layouts.app')

@section('title', 'Edit Booking')

@section('content')
@php
    // Get the actual departure time from the trip stop that matches the booking's from terminal
    $fromTripStop = $booking->trip?->stops?->firstWhere('terminal_id', $booking->fromStop?->terminal_id);
    $departureTime = $fromTripStop?->departure_at ?? $booking->trip?->departure_datetime;
    $departurePassed = $departureTime && $departureTime->isPast();
    $formDisabled = $departurePassed ? 'disabled' : '';
    $isBookingCancelled = ($booking->status === 'cancelled' || $booking->status === \App\Enums\BookingStatusEnum::CANCELLED->value);
@endphp

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Booking Management</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Booking #{{ $booking->booking_number }}</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="card mb-4 border">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-edit text-primary"></i> Edit Booking #{{ $booking->booking_number }}
                @if($departurePassed)
                    <span class="badge bg-warning text-dark ms-2">
                        <i class="fas fa-lock"></i> Trip Departed - Read Only
                    </span>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted">Booking Date</small>
                    <p class="mb-0"><strong>{{ $booking->created_at->format('d M Y, H:i A') }}</strong></p>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Route</small>
                    <p class="mb-0"><strong>{{ $booking->trip?->route?->name ?? 'N/A' }}</strong></p>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Total Seats</small>
                    <p class="mb-0">
                        <span class="badge bg-info">{{ $booking->seats->whereNull('cancelled_at')->count() }} active</span>
                        @if($booking->seats->whereNotNull('cancelled_at')->count() > 0)
                            <span class="badge bg-danger">{{ $booking->seats->whereNotNull('cancelled_at')->count() }} cancelled</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Current Status</small>
                    <p class="mb-0">    
                        <span class="badge {{ \App\Enums\BookingStatusEnum::from($booking->status ?? '')->getBadge() }}">
                            <i class="{{ \App\Enums\BookingStatusEnum::from($booking->status ?? '')->getIcon() }}"></i> {{ \App\Enums\BookingStatusEnum::from($booking->status ?? '')->getLabel() }}
                        </span>
                    </p>
                </div>
            </div>
            @if($booking->cancelled_at)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-danger border mb-0">
                            <h6 class="fw-bold mb-2"><i class="bx bx-error-circle"></i> Booking Cancelled</h6>
                            <p class="mb-1 small"><strong>Cancelled On:</strong> {{ $booking->cancelled_at->format('d M Y, H:i A') }}</p>
                            <p class="mb-1 small"><strong>Cancelled By:</strong>
                                @if($booking->cancelled_by_type)
                                    <span class="badge {{ $booking->cancelled_by_type === 'admin' ? 'bg-danger' : ($booking->cancelled_by_type === 'employee' ? 'bg-warning' : 'bg-secondary') }}">
                                        {{ ucfirst($booking->cancelled_by_type) }}
                                    </span>
                                @endif
                                @if($booking->cancelledByUser)
                                    - <strong>{{ $booking->cancelledByUser->name }}</strong>
                                @endif
                            </p>
                            @if($booking->cancellation_reason)
                                <p class="mb-0 small"><strong>Reason:</strong> {{ $booking->cancellation_reason }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <form id="bookingEditForm" method="POST" action="{{ route('admin.bookings.update', $booking) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Route Information -->
                <div class="card mb-4 border">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-route text-info"></i> Route Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Route</label>
                                <input type="text" class="form-control" value="{{ $booking->trip?->route?->name ?? 'N/A' }}" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Departure Date</label>
                                <input type="text" class="form-control" value="{{ $booking->trip?->departure_datetime?->format('d M Y') ?? 'N/A' }}" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">From Terminal</label>
                                <input type="text" class="form-control" value="{{ $booking->fromStop?->terminal?->name }} ({{ $booking->fromStop?->terminal?->code }})" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">To Terminal</label>
                                <input type="text" class="form-control" value="{{ $booking->toStop?->terminal?->name }} ({{ $booking->toStop?->terminal?->code }})" disabled>
                            </div>
                            <div class="col-md-12 mb-0">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea class="form-control" name="notes" rows="3" maxlength="500" placeholder="Add any special notes..." {{ $formDisabled }}>{{ $booking->notes }}</textarea>
                                <small class="form-text text-muted">Max 500 characters</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Seats -->
                <div class="card mb-4 border">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-chair text-info"></i> Booked Seats
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Seat #</th>
                                        <th>Gender</th>
                                        <th>Fare (PKR)</th>
                                        <th>Tax (PKR)</th>
                                        <th>Total (PKR)</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($booking->seats as $seat)
                                        <tr class="{{ $seat->cancelled_at ? 'table-secondary opacity-75' : '' }}">
                                            <td>
                                                <strong>{{ $seat->seat_number }}</strong>
                                            </td>
                                            <td>
                                                <i class="{{ \App\Enums\GenderEnum::getGenderIcon($seat->gender?->value ?? $seat->gender) }}"></i>
                                                {{ \App\Enums\GenderEnum::getGenderName($seat->gender?->value ?? $seat->gender) }}
                                            </td>
                                            <td>{{ number_format($seat->fare, 2) }}</td>
                                            <td>{{ number_format($seat->tax_amount, 2) }}</td>
                                            <td><strong>{{ number_format($seat->final_amount, 2) }}</strong></td>
                                            <td>
                                                @if($seat->cancelled_at)
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times-circle"></i> Cancelled
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">{{ $seat->cancelled_at->format('d M Y, H:i') }}</small>
                                                    @if($seat->cancellation_reason)
                                                        <br>
                                                        <small class="text-danger">
                                                            <i class="fas fa-info-circle"></i> Reason: {{ $seat->cancellation_reason }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Active
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$seat->cancelled_at)
                                                    @if(!$departurePassed)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger cancel-seat-btn d-flex align-items-center gap-1" 
                                                                data-seat-id="{{ $seat->id }}"
                                                                data-seat-number="{{ $seat->seat_number }}"
                                                                data-departure-passed="false"
                                                                title="Cancel this seat">
                                                            <i class="bx bx-trash"></i> 
                                                            <span>Cancel</span>
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">
                                                            <i class="bx bx-lock"></i> Trip Departed
                                                        </span>
                                                    @endif
                                                @else
                                                    @if($isBookingCancelled)
                                                        <span class="text-muted small">
                                                            <i class="bx bx-lock"></i> Booking Cancelled
                                                        </span>
                                                    @elseif(!$departurePassed)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success restore-seat-btn d-flex align-items-center gap-1" 
                                                                data-seat-id="{{ $seat->id }}"
                                                                data-seat-number="{{ $seat->seat_number }}"
                                                                title="Restore this seat">
                                                            <i class="bx bx-refresh"></i> 
                                                            <span>Restore</span>
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">
                                                            <i class="bx bx-lock"></i> Cannot restore
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No seats found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Passengers Information -->
                <div class="card mb-4 border">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-users text-info"></i> Passengers Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>CNIC</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody id="passengersContainer">
                                    @forelse($booking->passengers as $index => $passenger)
                                        <tr>
                                            <td>
                                                <strong>{{ $index + 1 }}</strong>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="passengers[{{ $index }}][name]" 
                                                       value="{{ $passenger->name }}" 
                                                       required>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm" 
                                                        name="passengers[{{ $index }}][gender]">
                                                    <option value="">Select Gender</option>
                                                    @foreach(\App\Enums\GenderEnum::getGenders() as $genderValue)
                                                        @php
                                                            $genderName = \App\Enums\GenderEnum::getGenderName($genderValue);
                                                            $currentGender = $passenger->gender?->value ?? $passenger->gender;
                                                        @endphp
                                                        <option value="{{ $genderValue }}" {{ $currentGender === $genderValue ? 'selected' : '' }}>
                                                            {{ $genderName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm" 
                                                       name="passengers[{{ $index }}][age]" 
                                                       value="{{ $passenger->age }}" 
                                                       min="1" 
                                                       max="120">
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="passengers[{{ $index }}][cnic]" 
                                                       value="{{ $passenger->cnic }}" 
                                                       placeholder="34101-1111111-1" 
                                                       maxlength="15">
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="passengers[{{ $index }}][phone]" 
                                                       value="{{ $passenger->phone }}" 
                                                       placeholder="0317-7777777" 
                                                       maxlength="12">
                                            </td>
                                            <td>
                                                <input type="email" 
                                                       class="form-control form-control-sm" 
                                                       name="passengers[{{ $index }}][email]" 
                                                       value="{{ $passenger->email }}" 
                                                       maxlength="100">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No passengers found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Booking Status -->
                <div class="card mb-4 border">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle text-success"></i> Booking Status
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($departurePassed)
                            <div class="alert alert-warning mb-3 border">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Departure time has passed.</strong> This booking is read-only and cannot be modified.
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status" id="bookingStatusSelect" required>
                                <option value="">Select Status</option>
                                @foreach($bookingStatuses as $status)
                                    <option value="{{ $status->value }}" {{ $booking->status === $status->value ? 'selected' : '' }}>
                                        {{ $status->getLabel() }}
                                    </option>
                                @endforeach
                            </select>
                            @if($departurePassed)
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Most status changes are restricted after departure, but cancellation is allowed for record-keeping.
                                </small>
                            @endif
                        </div>
                        @if($booking->status === 'hold' && !$departurePassed)
                            <div class="mb-0">
                                <label class="form-label fw-bold">Reserved Until</label>
                                <input type="datetime-local" class="form-control" name="reserved_until" value="{{ $booking->reserved_until?->format('Y-m-d\TH:i') }}" {{ $formDisabled }}>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card mb-4 border">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-credit-card text-success"></i> Payment Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Status</label>
                            <select class="form-select" name="payment_status" required {{ $formDisabled }}>
                                <option value="">Select Status</option>
                                @foreach($paymentStatuses as $status)
                                    <option value="{{ $status->value }}" {{ $booking->payment_status === $status->value ? 'selected' : '' }}>
                                        {{ $status->getLabel() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Show payment method options for phone bookings or when payment method needs to be updated --}}
                        @if($booking->channel === 'phone' || $booking->payment_method)
                            <div class="mb-3" id="paymentMethodField">
                                <label class="form-label fw-bold">Payment Method</label>
                                <select class="form-select" name="payment_method" id="paymentMethodSelect" onchange="toggleTransactionIdField()" {{ $formDisabled }}>
                                    <option value="">Select Method</option>
                                    @foreach($paymentMethods as $method)
                                        @if($method['value'] !== 'other')
                                            <option value="{{ $method['value'] }}" {{ $booking->payment_method === $method['value'] ? 'selected' : '' }}>
                                                {{ $method['label'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @if($booking->channel === 'phone' && !$departurePassed)
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle"></i> Customer can pay via any method when they arrive.
                                    </small>
                                @endif
                            </div>
                            
                            {{-- Transaction ID field (for non-cash payments) --}}
                            <div class="mb-3" id="transactionIdField" style="display: {{ ($booking->payment_method && $booking->payment_method !== 'cash') ? 'block' : 'none' }};">
                                <label class="form-label fw-bold">Transaction ID</label>
                                <input type="text" class="form-control" name="online_transaction_id" id="transactionIdInput" 
                                    value="{{ $booking->online_transaction_id }}" maxlength="100" 
                                    placeholder="Enter transaction ID" {{ $formDisabled }}>
                            </div>
                            
                            {{-- Amount Received field (for cash payments) --}}
                            <div class="mb-3" id="amountReceivedField" style="display: {{ ($booking->payment_method === 'cash') ? 'block' : 'none' }};">
                                <label class="form-label fw-bold">Amount Received (PKR)</label>
                                <input type="number" class="form-control" name="amount_received" id="amountReceivedInput" 
                                    value="{{ $booking->payment_received_from_customer ?? 0 }}" 
                                    min="0" step="0.01" placeholder="0.00"
                                    onchange="calculateReturnAmount()" {{ $formDisabled }}>
                            </div>
                            
                            {{-- Return Amount display --}}
                            <div id="returnAmountDiv" style="display: {{ ($booking->payment_received_from_customer ?? 0) > $booking->final_amount ? 'block' : 'none' }};">
                                <div class="alert alert-success mb-0 border">
                                    <strong>Return: PKR <span id="returnAmountDisplay">{{ number_format(max(0, ($booking->payment_received_from_customer ?? 0) - $booking->final_amount), 2) }}</span></strong>
                                </div>
                            </div>
                        @else
                            {{-- Hide payment method if not phone booking and no payment method set --}}
                            <input type="hidden" name="payment_method" value="{{ $booking->payment_method ?? 'cash' }}">
                        @endif
                    </div>
                </div>

                <!-- Fare Summary -->
                <div class="card mb-4 border">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-calculator text-success"></i> Fare Summary
                        </h6>
                    </div>
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
                                    <span>Tax/Service:</span>
                                    <strong class="text-success">+PKR {{ number_format($booking->tax_amount, 2) }}</strong>
                                </div>
                            </div>
                        @endif
                        <hr class="my-3">
                        <div class="d-flex justify-content-between mb-3">
                            <strong class="fs-5">Final Amount:</strong>
                            <strong class="text-success fs-5">PKR {{ number_format($booking->final_amount, 2) }}</strong>
                        </div>

                        @if($booking->channel === 'counter')
                            <hr>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span>Amount Received:</span>
                                    <strong>PKR {{ number_format($booking->payment_received_from_customer ?? 0, 2) }}</strong>
                                </div>
                            </div>
                            @if($booking->return_after_deduction_from_customer > 0)
                                <div class="d-flex justify-content-between">
                                    <span>Return:</span>
                                    <strong class="text-success">PKR {{ number_format($booking->return_after_deduction_from_customer, 2) }}</strong>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                @if(!$departurePassed)
                    @can('edit bookings')
                        <div class="card border">
                            <div class="card-body d-flex gap-2">
                                <button type="submit" class="btn btn-success flex-grow-1">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary flex-grow-1">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="card border">
                            <div class="card-body">
                                <div class="alert alert-warning mb-0 border">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>You do not have permission to edit bookings.</strong>
                                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                                        <i class="fas fa-arrow-left"></i> Back to Bookings
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endcan
                @else
                    <div class="card border">
                        <div class="card-body">
                            <div class="alert alert-info mb-0 border">
                                <i class="fas fa-info-circle"></i> 
                                <strong>This booking cannot be modified</strong> as the trip has already departed. 
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-arrow-left"></i> Back to Bookings
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bookingEditForm');
        const departurePassed = {{ $departurePassed ? 'true' : 'false' }};
        
        // Prevent form submission if departure has passed
        if (departurePassed) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Update Booking',
                    text: 'This booking cannot be modified as the trip has already departed.',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            });
            return; // Don't set up other event listeners
        }
        
        const statusSelect = form.querySelector('select[name="status"]');
        let cancellationReason = null;

        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const reservedUntilDiv = form.querySelector('input[name="reserved_until"]')?.parentElement;
                if (this.value === 'hold' && reservedUntilDiv) {
                    reservedUntilDiv.style.display = 'block';
                } else if (reservedUntilDiv) {
                    reservedUntilDiv.style.display = 'none';
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get status from select
            const statusSelect = document.getElementById('bookingStatusSelect');
            const selectedStatus = statusSelect?.value;
            const wasCancelled = '{{ $booking->status }}' === 'cancelled';
            
            // If status is being changed to cancelled, ask for reason
            if (selectedStatus === 'cancelled' && !wasCancelled) {
                Swal.fire({
                    title: 'Cancel Booking?',
                    text: 'Please provide a reason for cancelling this booking:',
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Enter cancellation reason (optional)',
                    inputAttributes: {
                        'aria-label': 'Cancellation reason'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, cancel booking',
                    cancelButtonText: 'No, keep it',
                    inputValidator: (value) => {
                        // Reason is optional, so no validation needed
                        return Promise.resolve();
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        cancellationReason = result.value || null;
                        submitForm();
                    }
                });
            } else {
                submitForm();
            }
        });

        function submitForm() {
            const formData = new FormData(form);
            
            // Get the selected status from the select
            const statusSelect = document.getElementById('bookingStatusSelect');
            const selectedStatus = statusSelect?.value;
            
            // Ensure status is always sent
            if (selectedStatus) {
                formData.set('status', selectedStatus);
            }
            
            // Add cancellation reason if provided
            if (cancellationReason !== null) {
                formData.append('cancellation_reason', cancellationReason);
            }
            
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Booking updated successfully!',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = '{{ route("admin.bookings.index") }}';
                    });
                },
                error: function(error) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                    const message = error.responseJSON?.message || error.responseJSON?.error || 'Failed to update booking';
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: message,
                        confirmButtonColor: '#d33'
                    });

                    if (error.responseJSON?.errors) {
                        console.log('Validation Errors:', error.responseJSON.errors);
                    }
                }
            });
        }
    });

    // ========================================
    // TOGGLE TRANSACTION ID FIELD
    // ========================================
    function toggleTransactionIdField() {
        const paymentMethod = document.getElementById('paymentMethodSelect')?.value || 'cash';
        const transactionIdField = document.getElementById('transactionIdField');
        const transactionIdInput = document.getElementById('transactionIdInput');
        const amountReceivedField = document.getElementById('amountReceivedField');
        const amountReceivedInput = document.getElementById('amountReceivedInput');
        const returnAmountDiv = document.getElementById('returnAmountDiv');

        if (paymentMethod === 'cash') {
            // Cash payment: show Amount Received, hide Transaction ID
            if (transactionIdField) {
                transactionIdField.style.display = 'none';
                if (transactionIdInput) {
                    transactionIdInput.required = false;
                    transactionIdInput.value = '';
                }
            }
            if (amountReceivedField) {
                amountReceivedField.style.display = 'block';
                if (amountReceivedInput) {
                    amountReceivedInput.required = false;
                }
            }
        } else {
            // Non-cash payment: show Transaction ID, hide Amount Received
            if (transactionIdField) {
                transactionIdField.style.display = 'block';
                if (transactionIdInput) {
                    transactionIdInput.required = true;
                }
            }
            if (amountReceivedField) {
                amountReceivedField.style.display = 'none';
                if (amountReceivedInput) {
                    amountReceivedInput.required = false;
                    amountReceivedInput.value = '0';
                }
            }
            if (returnAmountDiv) {
                returnAmountDiv.style.display = 'none';
            }
        }
    }

    // ========================================
    // CALCULATE RETURN AMOUNT
    // ========================================
    function calculateReturnAmount() {
        const amountReceived = parseFloat(document.getElementById('amountReceivedInput')?.value || 0);
        const finalAmount = {{ $booking->final_amount }};
        const returnAmount = Math.max(0, amountReceived - finalAmount);
        const returnAmountDiv = document.getElementById('returnAmountDiv');
        const returnAmountDisplay = document.getElementById('returnAmountDisplay');

        if (returnAmountDiv && returnAmountDisplay) {
            if (returnAmount > 0) {
                returnAmountDisplay.textContent = returnAmount.toFixed(2);
                returnAmountDiv.style.display = 'block';
            } else {
                returnAmountDiv.style.display = 'none';
            }
        }
    }

    // ========================================
    // CANCEL/RESTORE SEAT HANDLERS
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        const departurePassed = {{ $departurePassed ? 'true' : 'false' }};

        // Cancel seat handler - only available before departure
        document.querySelectorAll('.cancel-seat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const seatId = this.getAttribute('data-seat-id');
                const seatNumber = this.getAttribute('data-seat-number');

                Swal.fire({
                    title: 'Cancel Seat?',
                    html: `
                        <div class="text-start">
                            <p class="mb-3">You are about to cancel <strong>Seat ${seatNumber}</strong>.</p>
                            <p class="mb-2 text-danger"><strong>Please provide a reason for cancellation:</strong></p>
                        </div>
                    `,
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Enter cancellation reason (required)',
                    inputAttributes: {
                        'aria-label': 'Cancellation reason',
                        'rows': 4,
                        'maxlength': 500
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="bx bx-trash"></i> Yes, Cancel Seat',
                    cancelButtonText: '<i class="bx bx-x"></i> No, Keep It',
                    inputValidator: (value) => {
                        if (!value || value.trim().length === 0) {
                            return 'Cancellation reason is required. Please provide a reason.';
                        }
                        if (value.trim().length < 5) {
                            return 'Please provide a more detailed reason (at least 5 characters).';
                        }
                        return null;
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value && result.value.trim().length >= 5) {
                        cancelSeat(seatId, seatNumber, result.value.trim());
                    }
                });
            });
        });

        // Restore seat handler
        document.querySelectorAll('.restore-seat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const seatId = this.getAttribute('data-seat-id');
                const seatNumber = this.getAttribute('data-seat-number');

                Swal.fire({
                    title: 'Restore Seat?',
                    html: `
                        <div class="text-start">
                            <p class="mb-2">Are you sure you want to restore <strong>Seat ${seatNumber}</strong>?</p>
                            <p class="mb-0 text-info"><i class="fas fa-info-circle"></i> This action will recalculate the booking totals.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="bx bx-refresh"></i> Yes, Restore Seat',
                    cancelButtonText: '<i class="bx bx-x"></i> No, Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        restoreSeat(seatId, seatNumber);
                    }
                });
            });
        });
    });

    function cancelSeat(seatId, seatNumber, cancellationReason) {
        const btn = document.querySelector(`.cancel-seat-btn[data-seat-id="${seatId}"]`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
        }

        $.ajax({
            url: `{{ route('admin.bookings.seats.cancel', ['booking' => $booking, 'seat' => '__SEAT_ID__']) }}`.replace('__SEAT_ID__', seatId),
            type: 'POST',
            data: {
                cancellation_reason: cancellationReason
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Seat Cancelled',
                    text: `Seat ${seatNumber} has been cancelled successfully.`,
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    // Reload the page to show updated totals and seat status
                    window.location.reload();
                });
            },
            error: function(error) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bx bx-trash"></i> <span>Cancel</span>';
                }
                const message = error.responseJSON?.message || error.responseJSON?.error || 'Failed to cancel seat';
                Swal.fire({
                    icon: 'error',
                    title: 'Cancellation Failed',
                    text: message,
                    confirmButtonColor: '#d33'
                });
            }
        });
    }

    function restoreSeat(seatId, seatNumber) {
        const btn = document.querySelector(`.restore-seat-btn[data-seat-id="${seatId}"]`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';
        }

        $.ajax({
            url: `{{ route('admin.bookings.seats.restore', ['booking' => $booking, 'seat' => '__SEAT_ID__']) }}`.replace('__SEAT_ID__', seatId),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            success: function(response) {
                const requiresStatusUpdate = response.requires_status_update || false;
                
                if (requiresStatusUpdate) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Seat Restored',
                        html: `
                            <div class="text-start">
                                <p class="mb-3">Seat ${seatNumber} has been restored successfully.</p>
                                <div class="alert alert-warning mb-0">
                                    <strong><i class="fas fa-exclamation-triangle"></i> Action Required:</strong><br>
                                    Please update the following fields:
                                    <ul class="mb-0 mt-2">
                                        <li>Booking Status</li>
                                        <li>Payment Status</li>
                                        <li>Payment Method</li>
                                    </ul>
                                </div>
                            </div>
                        `,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Go to Booking Status',
                        showCancelButton: true,
                        cancelButtonText: 'Reload Page'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Scroll to booking status section
                            const statusSelect = document.getElementById('bookingStatusSelect');
                            if (statusSelect) {
                                // Find the parent card
                                const statusCard = statusSelect.closest('.card');
                                if (statusCard) {
                                    statusCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                                // Highlight and focus the status select
                                setTimeout(() => {
                                    statusSelect.focus();
                                    statusSelect.style.border = '2px solid #ffc107';
                                    statusSelect.style.boxShadow = '0 0 10px rgba(255, 193, 7, 0.5)';
                                    setTimeout(() => {
                                        statusSelect.style.border = '';
                                        statusSelect.style.boxShadow = '';
                                    }, 3000);
                                }, 300);
                            } else {
                                window.location.reload();
                            }
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Seat Restored',
                        text: `Seat ${seatNumber} has been restored successfully.`,
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // Reload the page to show updated totals and seat status
                        window.location.reload();
                    });
                }
            },
            error: function(error) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bx bx-refresh"></i> <span>Restore</span>';
                }
                const message = error.responseJSON?.message || error.responseJSON?.error || 'Failed to restore seat';
                Swal.fire({
                    icon: 'error',
                    title: 'Restore Failed',
                    text: message,
                    confirmButtonColor: '#d33'
                });
            }
        });
    }
</script>
@endsection

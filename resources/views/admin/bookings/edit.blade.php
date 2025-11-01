@extends('admin.layouts.app')

@section('title', 'Edit Booking')

@section('content')
@php
    $departureTime = $booking->trip?->departure_datetime;
    $departurePassed = $departureTime && $departureTime->isPast();
    $formDisabled = $departurePassed ? 'disabled' : '';
@endphp

<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-edit"></i> Edit Booking #{{ $booking->booking_number }}
                @if($departurePassed)
                    <span class="badge bg-warning ms-2">
                        <i class="fas fa-lock"></i> Trip Departed - Read Only
                    </span>
                @endif
            </h5>
        </div>
        <div class="card-body bg-light">
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
                    <p class="mb-0"><span class="badge bg-info">{{ $booking->seats->count() }} seat(s)</span></p>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Current Status</small>
                    <p class="mb-0">
                        <span class="badge {{ $booking->status === 'confirmed' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <form id="bookingEditForm" method="POST" action="{{ route('admin.bookings.update', $booking) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Route Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-route"></i> Route Information</h6>
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
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-chair"></i> Booked Seats</h6>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($booking->seats as $seat)
                                        <tr>
                                            <td><strong>{{ $seat->seat_number }}</strong></td>
                                            <td>
                                                @if($seat->gender === 'male')
                                                    <i class="fas fa-mars"></i> Male
                                                @else
                                                    <i class="fas fa-venus"></i> Female
                                                @endif
                                            </td>
                                            <td>{{ number_format($seat->fare, 2) }}</td>
                                            <td>{{ number_format($seat->tax_amount, 2) }}</td>
                                            <td><strong>{{ number_format($seat->final_amount, 2) }}</strong></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No seats found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Passengers Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-users"></i> Passengers Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row" id="passengersContainer">
                            @forelse($booking->passengers as $index => $passenger)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-user"></i> Passenger {{ $index + 1 }}
                                                @if($passenger->gender === 'male')
                                                    <i class="fas fa-mars text-primary"></i>
                                                @else
                                                    <i class="fas fa-venus text-danger"></i>
                                                @endif
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Name</label>
                                                <input type="text" class="form-control form-control-sm" name="passengers[{{ $index }}][name]" value="{{ $passenger->name }}" required {{ $formDisabled }}>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Gender</label>
                                                <select class="form-select form-select-sm" name="passengers[{{ $index }}][gender]" {{ $formDisabled }}>
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ $passenger->gender === 'male' ? 'selected' : '' }}>👨 Male</option>
                                                    <option value="female" {{ $passenger->gender === 'female' ? 'selected' : '' }}>👩 Female</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Age</label>
                                                <input type="number" class="form-control form-control-sm" name="passengers[{{ $index }}][age]" value="{{ $passenger->age }}" min="1" max="120" {{ $formDisabled }}>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">CNIC</label>
                                                <input type="text" class="form-control form-control-sm" name="passengers[{{ $index }}][cnic]" value="{{ $passenger->cnic }}" maxlength="20" {{ $formDisabled }}>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Phone</label>
                                                <input type="tel" class="form-control form-control-sm" name="passengers[{{ $index }}][phone]" value="{{ $passenger->phone }}" maxlength="20" {{ $formDisabled }}>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label small fw-bold">Email</label>
                                                <input type="email" class="form-control form-control-sm" name="passengers[{{ $index }}][email]" value="{{ $passenger->email }}" maxlength="100" {{ $formDisabled }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted">No passengers found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Booking Status -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Booking Status</h6>
                    </div>
                    <div class="card-body">
                        @if($departurePassed)
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Departure time has passed.</strong> This booking is read-only and cannot be modified.
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status" required {{ $formDisabled }}>
                                <option value="">Select Status</option>
                                @foreach(\App\Enums\BookingStatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}" {{ $booking->status === $status->value ? 'selected' : '' }}>
                                        {{ $status->getLabel() }}
                                    </option>
                                @endforeach
                                {{-- Additional statuses not in enum but used in system --}}
                                <option value="checked_in" {{ $booking->status === 'checked_in' ? 'selected' : '' }}>🔵 Checked In</option>
                                <option value="boarded" {{ $booking->status === 'boarded' ? 'selected' : '' }}>🛫 Boarded</option>
                            </select>
                            @if($formDisabled)
                                <input type="hidden" name="status" value="{{ $booking->status }}">
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
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-credit-card"></i> Payment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Status</label>
                            <select class="form-select" name="payment_status" required {{ $formDisabled }}>
                                <option value="">Select Status</option>
                                <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>✅ Paid</option>
                                <option value="unpaid" {{ $booking->payment_status === 'unpaid' ? 'selected' : '' }}>❌ Unpaid</option>
                                <option value="partial" {{ $booking->payment_status === 'partial' ? 'selected' : '' }}>⚠️ Partial</option>
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
                                <div class="alert alert-success mb-0">
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
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-calculator"></i> Fare Summary</h6>
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
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Final Amount:</strong>
                            <strong class="text-success" style="font-size: 1.1rem;">PKR {{ number_format($booking->final_amount, 2) }}</strong>
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
                    <div class="card shadow-sm">
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
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
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

            const formData = new FormData(this);
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
        });
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
</script>
@endsection

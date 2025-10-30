@extends('admin.layouts.app')

@section('title', 'Edit Booking')

@section('content')
<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-edit"></i> Edit Booking #{{ $booking->booking_number }}
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
                                <textarea class="form-control" name="notes" rows="3" maxlength="500" placeholder="Add any special notes...">{{ $booking->notes }}</textarea>
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
                                                <input type="text" class="form-control form-control-sm" name="passengers[{{ $index }}][name]" value="{{ $passenger->name }}" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Gender</label>
                                                <select class="form-select form-select-sm" name="passengers[{{ $index }}][gender]">
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ $passenger->gender === 'male' ? 'selected' : '' }}>👨 Male</option>
                                                    <option value="female" {{ $passenger->gender === 'female' ? 'selected' : '' }}>👩 Female</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Age</label>
                                                <input type="number" class="form-control form-control-sm" name="passengers[{{ $index }}][age]" value="{{ $passenger->age }}" min="1" max="120">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">CNIC</label>
                                                <input type="text" class="form-control form-control-sm" name="passengers[{{ $index }}][cnic]" value="{{ $passenger->cnic }}" maxlength="20">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Phone</label>
                                                <input type="tel" class="form-control form-control-sm" name="passengers[{{ $index }}][phone]" value="{{ $passenger->phone }}" maxlength="20">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label small fw-bold">Email</label>
                                                <input type="email" class="form-control form-control-sm" name="passengers[{{ $index }}][email]" value="{{ $passenger->email }}" maxlength="100">
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
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Select Status</option>
                                <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                                <option value="hold" {{ $booking->status === 'hold' ? 'selected' : '' }}>⏱️ On Hold</option>
                                <option value="checked_in" {{ $booking->status === 'checked_in' ? 'selected' : '' }}>🔵 Checked In</option>
                                <option value="boarded" {{ $booking->status === 'boarded' ? 'selected' : '' }}>🛫 Boarded</option>
                                <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            </select>
                        </div>
                        @if($booking->status === 'hold')
                            <div class="mb-0">
                                <label class="form-label fw-bold">Reserved Until</label>
                                <input type="datetime-local" class="form-control" name="reserved_until" value="{{ $booking->reserved_until?->format('Y-m-d\TH:i') }}">
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
                            <select class="form-select" name="payment_status" required>
                                <option value="">Select Status</option>
                                <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>✅ Paid</option>
                                <option value="unpaid" {{ $booking->payment_status === 'unpaid' ? 'selected' : '' }}>❌ Unpaid</option>
                                <option value="partial" {{ $booking->payment_status === 'partial' ? 'selected' : '' }}>⚠️ Partial</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Method</label>
                            <select class="form-select" name="payment_method">
                                <option value="">Select Method</option>
                                <option value="cash" {{ $booking->payment_method === 'cash' ? 'selected' : '' }}>💵 Cash</option>
                                <option value="card" {{ $booking->payment_method === 'card' ? 'selected' : '' }}>💳 Card</option>
                                <option value="mobile_wallet" {{ $booking->payment_method === 'mobile_wallet' ? 'selected' : '' }}>📱 Mobile Wallet</option>
                                <option value="bank_transfer" {{ $booking->payment_method === 'bank_transfer' ? 'selected' : '' }}>🏦 Bank Transfer</option>
                            </select>
                        </div>
                        @if($booking->online_transaction_id)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Transaction ID</label>
                                <input type="text" class="form-control" name="online_transaction_id" value="{{ $booking->online_transaction_id }}" maxlength="100">
                            </div>
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
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bookingEditForm');
        const statusSelect = form.querySelector('name="status"');

        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const reservedUntilField = form.querySelector('name="reserved_until"')?.parentElement;
                if (this.value === 'hold' && reservedUntilField) {
                    reservedUntilField.style.display = 'block';
                } else if (reservedUntilField) {
                    reservedUntilField.style.display = 'none';
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
                    alert('Booking updated successfully!');
                    window.location.href = '{{ route("admin.bookings.index") }}';
                },
                error: function(error) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                    const message = error.responseJSON?.message || 'Failed to update booking';
                    alert(message);

                    if (error.responseJSON?.errors) {
                        console.log('Validation Errors:', error.responseJSON.errors);
                    }
                }
            });
        });
    });
</script>
@endsection

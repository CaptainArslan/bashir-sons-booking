@extends('admin.layouts.app')

@section('title', 'Passenger Details')

@section('styles')
    <style>
        .passenger-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .passenger-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .passenger-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }

        .seat-badge {
            background: #0d6efd;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .trip-summary {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }

        .fare-summary-box {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            position: sticky;
            top: 20px;
        }

        .fare-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
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
                    <li class="breadcrumb-item active" aria-current="page">Passenger Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-lg-8">
            <!-- Trip Summary -->
            <div class="trip-summary">
                <h6 class="text-primary mb-2"><i class="bx bx-bus me-1"></i>Trip Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Route:</small>
                        <p class="mb-1"><strong>{{ $trip->route->name }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Departure:</small>
                        <p class="mb-1"><strong>{{ \Carbon\Carbon::parse($trip->departure_datetime)->format('d M Y, h:i A') }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">From:</small>
                        <p class="mb-0"><strong>{{ $fromStop->terminal->name }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">To:</small>
                        <p class="mb-0"><strong>{{ $toStop->terminal->name }}</strong></p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.bookings.store') }}" method="POST" id="booking-form">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                <input type="hidden" name="from_stop_id" value="{{ $fromStop->id }}">
                <input type="hidden" name="to_stop_id" value="{{ $toStop->id }}">
                <input type="hidden" name="terminal_id" value="{{ $fromStop->terminal_id }}">
                <input type="hidden" name="total_fare" value="{{ $totalFare }}">

                <!-- Passenger Details for Each Seat -->
                @foreach ($selectedSeats as $index => $seatNumber)
                    <div class="passenger-card card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Passenger {{ $index + 1 }}</span>
                            <span class="seat-badge">Seat {{ $seatNumber }}</span>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="seats[{{ $index }}][seat_number]" value="{{ $seatNumber }}">
                            <input type="hidden" name="seats[{{ $index }}][fare]" value="{{ $farePerSeat }}">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("seats.$index.passenger_name") is-invalid @enderror"
                                        name="seats[{{ $index }}][passenger_name]"
                                        value="{{ old("seats.$index.passenger_name") }}"
                                        placeholder="Enter full name" required>
                                    @error("seats.$index.passenger_name")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CNIC <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error("seats.$index.passenger_cnic") is-invalid @enderror"
                                        name="seats[{{ $index }}][passenger_cnic]"
                                        value="{{ old("seats.$index.passenger_cnic") }}"
                                        placeholder="XXXXX-XXXXXXX-X" required
                                        pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}">
                                    @error("seats.$index.passenger_cnic")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select @error("seats.$index.passenger_gender") is-invalid @enderror"
                                        name="seats[{{ $index }}][passenger_gender]" required>
                                        <option value="">Select</option>
                                        <option value="male" {{ old("seats.$index.passenger_gender") == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old("seats.$index.passenger_gender") == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old("seats.$index.passenger_gender") == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error("seats.$index.passenger_gender")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control @error("seats.$index.passenger_age") is-invalid @enderror"
                                        name="seats[{{ $index }}][passenger_age]"
                                        value="{{ old("seats.$index.passenger_age") }}"
                                        placeholder="Age" min="1" max="150">
                                    @error("seats.$index.passenger_age")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control @error("seats.$index.passenger_phone") is-invalid @enderror"
                                        name="seats[{{ $index }}][passenger_phone]"
                                        value="{{ old("seats.$index.passenger_phone") }}"
                                        placeholder="03XX-XXXXXXX">
                                    @error("seats.$index.passenger_phone")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Contact Person Details -->
                <div class="card border-success mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-user-circle me-1"></i>Contact Person Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('passenger_contact_name') is-invalid @enderror"
                                    name="passenger_contact_name"
                                    value="{{ old('passenger_contact_name') }}"
                                    placeholder="Contact person name" required>
                                @error('passenger_contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('passenger_contact_phone') is-invalid @enderror"
                                    name="passenger_contact_phone"
                                    value="{{ old('passenger_contact_phone') }}"
                                    placeholder="03XX-XXXXXXX" required>
                                @error('passenger_contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control @error('passenger_contact_email') is-invalid @enderror"
                                    name="passenger_contact_email"
                                    value="{{ old('passenger_contact_email') }}"
                                    placeholder="email@example.com">
                                @error('passenger_contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                    name="notes" rows="2"
                                    placeholder="Any special instructions or notes">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <!-- Fare Summary -->
            <div class="fare-summary-box">
                <h5 class="mb-3"><i class="bx bx-receipt me-1"></i>Booking Summary</h5>

                <div class="fare-row">
                    <span>Selected Seats:</span>
                    <strong>{{ count($selectedSeats) }} Seats</strong>
                </div>
                <div class="fare-row">
                    <span>Seats:</span>
                    <strong>{{ implode(', ', $selectedSeats) }}</strong>
                </div>
                <div class="fare-row">
                    <span>Fare per Seat:</span>
                    <strong>PKR {{ number_format($farePerSeat, 2) }}</strong>
                </div>

                <hr style="border-color: rgba(255,255,255,0.3);">

                <div class="fare-row">
                    <strong>Total Fare:</strong>
                    <strong style="font-size: 1.2rem;">PKR {{ number_format($totalFare, 2) }}</strong>
                </div>

                <!-- Booking Type and Payment -->
                <div class="mt-4 p-3" style="background: rgba(255,255,255,0.2); border-radius: 6px;">
                    <div class="mb-3">
                        <label class="form-label text-white">Booking Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="booking_type" form="booking-form" required>
                            <option value="">Select Type</option>
                            <option value="counter">Counter Booking</option>
                            <option value="phone">Phone Booking</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_method" form="booking-form" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_wallet">Mobile Wallet</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Payment Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_status" form="booking-form" required>
                            <option value="">Select Status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Discount Amount</label>
                        <input type="number" class="form-control" name="discount_amount"
                            form="booking-form" min="0" max="{{ $totalFare }}"
                            placeholder="0.00" step="0.01" value="0">
                    </div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="submit" form="booking-form" class="btn btn-light btn-lg">
                        <i class="bx bx-check-circle me-1"></i>Complete Booking
                    </button>
                    <button type="button" class="btn btn-outline-light" onclick="history.back()">
                        <i class="bx bx-arrow-back me-1"></i>Back
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-format CNIC input
            $('input[name*="passenger_cnic"]').on('input', function() {
                let val = this.value.replace(/\D/g, '');
                if (val.length > 5) {
                    val = val.slice(0, 5) + '-' + val.slice(5);
                }
                if (val.length > 13) {
                    val = val.slice(0, 13) + '-' + val.slice(13, 14);
                }
                this.value = val;
            });

            // Auto-format phone input
            $('input[type="tel"]').on('input', function() {
                let val = this.value.replace(/\D/g, '');
                if (val.length > 4) {
                    val = val.slice(0, 4) + '-' + val.slice(4, 11);
                }
                this.value = val;
            });

            // Form validation
            $('#booking-form').on('submit', function(e) {
                const bookingType = $('[name="booking_type"]').val();
                const paymentMethod = $('[name="payment_method"]').val();
                const paymentStatus = $('[name="payment_status"]').val();

                if (!bookingType || !paymentMethod || !paymentStatus) {
                    e.preventDefault();
                    alert('Please select booking type, payment method, and payment status.');
                    return false;
                }
            });
        });
    </script>
@endsection


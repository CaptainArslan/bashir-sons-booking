@extends('admin.layouts.app')

@section('title', 'Booking Details')

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
                    <li class="breadcrumb-item active" aria-current="page">Booking #{{ $booking->booking_number }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-receipt me-2"></i>Booking #{{ $booking->booking_number }}</h5>
                </div>
                <div class="card-body">
                    <!-- Status Badge -->
                    <div class="mb-3">
                        <span class="badge bg-{{ $booking->status->value === 'confirmed' ? 'success' : ($booking->status->value === 'cancelled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($booking->status->value) }}
                        </span>
                        <span class="badge bg-info">{{ ucfirst($booking->type->value) }}</span>
                    </div>

                    <!-- Trip Information -->
                    <h6 class="border-bottom pb-2 mb-3">Trip Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Route:</small>
                            <p><strong>{{ $booking->trip->route->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Bus:</small>
                            <p><strong>{{ $booking->trip->bus ? $booking->trip->bus->registration_number : 'Not Assigned' }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">From:</small>
                            <p><strong>{{ $booking->fromStop->terminal->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">To:</small>
                            <p><strong>{{ $booking->toStop->terminal->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Departure:</small>
                            <p><strong>{{ \Carbon\Carbon::parse($booking->trip->departure_datetime)->format('d M Y, h:i A') }}</strong></p>
                        </div>
                    </div>

                    <!-- Passenger Details -->
                    <h6 class="border-bottom pb-2 mb-3">Passenger Details ({{ $booking->total_passengers }} passengers)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Seat</th>
                                    <th>Name</th>
                                    <th>CNIC</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Phone</th>
                                    <th>Fare</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->bookingSeats as $seat)
                                    <tr>
                                        <td><span class="badge bg-primary">{{ $seat->seat_number }}</span></td>
                                        <td>{{ $seat->passenger_name }}</td>
                                        <td>{{ $seat->passenger_cnic }}</td>
                                        <td>{{ ucfirst($seat->passenger_gender) }}</td>
                                        <td>{{ $seat->passenger_age ?? '-' }}</td>
                                        <td>{{ $seat->passenger_phone ?? '-' }}</td>
                                        <td>PKR {{ number_format($seat->fare, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Contact Information -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Contact Information</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Contact Name:</small>
                            <p><strong>{{ $booking->passenger_contact_name }}</strong></p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Contact Phone:</small>
                            <p><strong>{{ $booking->passenger_contact_phone }}</strong></p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Contact Email:</small>
                            <p><strong>{{ $booking->passenger_contact_email ?? '-' }}</strong></p>
                        </div>
                    </div>

                    @if ($booking->notes)
                        <div class="alert alert-info">
                            <strong>Notes:</strong> {{ $booking->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bx bx-money me-1"></i>Payment Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Fare:</span>
                        <strong>PKR {{ number_format($booking->total_fare, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <strong class="text-danger">- PKR {{ number_format($booking->discount_amount, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Final Amount:</strong>
                        <strong class="text-success fs-5">PKR {{ number_format($booking->final_amount, 2) }}</strong>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Payment Method:</small>
                        <p class="mb-0"><strong>{{ ucfirst($booking->payment_method) }}</strong></p>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Payment Status:</small>
                        <p class="mb-0">
                            <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </p>
                    </div>

                    @if ($booking->confirmed_at)
                        <div class="mb-2">
                            <small class="text-muted">Confirmed At:</small>
                            <p class="mb-0"><strong>{{ $booking->confirmed_at->format('d M Y, h:i A') }}</strong></p>
                        </div>
                    @endif

                    @if ($booking->reserved_until)
                        <div class="alert alert-warning mt-3">
                            <small><strong>Reserved Until:</strong><br>{{ $booking->reserved_until->format('d M Y, h:i A') }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back to List
                        </a>
                        @if (!$booking->isCancelled() && $booking->canBeCancelled())
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#cancelModal">
                                <i class="bx bx-x-circle me-1"></i>Cancel Booking
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    @if (!$booking->isCancelled() && $booking->canBeCancelled())
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Cancel Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to cancel this booking?</p>
                            <div class="mb-3">
                                <label class="form-label">Cancellation Reason</label>
                                <textarea class="form-control" name="cancellation_reason" rows="3"
                                    placeholder="Enter reason for cancellation"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Cancel Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

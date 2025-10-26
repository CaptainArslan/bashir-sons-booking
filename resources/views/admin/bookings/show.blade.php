@extends('admin.layouts.app')

@section('title', 'Booking Details')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">
            <i class="ti ti-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Booking #{{ $booking->booking_number }}</h5>
                </div>
                <div class="card-body">
                    <!-- Booking Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Status:</strong> <span
                                class="badge bg-{{ $booking->status->color() }}">{{ $booking->status->label() }}</span><br>
                            <strong>Type:</strong> <span
                                class="badge bg-{{ $booking->type->color() }}">{{ $booking->type->label() }}</span><br>
                            <strong>Booking Date:</strong> {{ $booking->created_at->format('M d, Y h:i A') }}
                        </div>
                        <div class="col-md-6 text-end">
                            <h3 class="text-success">₨{{ number_format($booking->final_amount, 0) }}</h3>
                            <small class="text-muted">Total Amount</small>
                        </div>
                    </div>

                    <!-- Trip Details -->
                    <h6 class="border-bottom pb-2">Trip Details</h6>
                    <p>
                        <strong>Route:</strong> {{ $booking->trip->route->code }} - {{ $booking->trip->route->name }}<br>
                        <strong>Departure:</strong> {{ $booking->trip->departure_datetime->format('l, M d, Y \a\t h:i A') }}<br>
                        <strong>From:</strong> {{ $booking->fromStop->terminal->name }}<br>
                        <strong>To:</strong> {{ $booking->toStop->terminal->name }}
                    </p>

                    <!-- Passenger Details -->
                    <h6 class="border-bottom pb-2 mt-4">Passenger Details</h6>
                    <p>
                        <strong>Name:</strong> {{ $booking->passenger_contact_name }}<br>
                        <strong>Phone:</strong> {{ $booking->passenger_contact_phone }}<br>
                        <strong>Email:</strong> {{ $booking->passenger_contact_email ?? 'N/A' }}
                    </p>

                    <!-- Seats -->
                    <h6 class="border-bottom pb-2 mt-4">Seat Information</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Seat</th>
                                    <th>Passenger Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Fare</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->bookingSeats as $seat)
                                    <tr>
                                        <td><strong>{{ $seat->seat_number }}</strong></td>
                                        <td>{{ $seat->passenger_name }}</td>
                                        <td>{{ $seat->passenger_age ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($seat->passenger_gender ?? 'N/A') }}</td>
                                        <td>₨{{ number_format($seat->fare, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if ($booking->status === \App\Enums\BookingStatusEnum::Pending)
                        <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ti ti-check"></i> Confirm Booking
                            </button>
                        </form>
                    @endif

                    @if ($booking->canBeCancelled())
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                            data-bs-target="#cancelModal">
                            <i class="ti ti-x"></i> Cancel Booking
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
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
                        <div class="mb-3">
                            <label class="form-label">Cancellation Reason</label>
                            <textarea name="reason" class="form-control" rows="3"></textarea>
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
@endsection


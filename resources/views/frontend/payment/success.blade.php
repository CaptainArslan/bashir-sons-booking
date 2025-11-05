@extends('frontend.layouts.app')

@section('title', 'Booking Confirmed')

@section('content')
    <section class="py-5 bg-light" style="min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 text-center">
                        <div class="card-body p-5">
                            <div class="mb-4">
                                <div class="success-icon mb-3" style="font-size: 5rem; color: #28a745;">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <h2 class="fw-bold mb-3">Booking Confirmed!</h2>
                                <p class="text-muted fs-5">Your payment has been received and your booking is confirmed.</p>
                            </div>

                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-4">
                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <strong>Booking Number:</strong><br>
                                            <span class="fs-5 fw-bold text-primary">{{ $booking->booking_number }}</span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Total Amount Paid:</strong><br>
                                            <span class="fs-5 fw-bold text-success">{{ $booking->currency }} {{ number_format($booking->final_amount, 2) }}</span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Payment Method:</strong><br>
                                            <span class="text-capitalize">{{ $booking->payment_method }}</span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Transaction ID:</strong><br>
                                            <span>{{ $booking->online_transaction_id ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="{{ route('profile.bookings') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-ticket-perforated me-2"></i>View My Bookings
                                </a>
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-house me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


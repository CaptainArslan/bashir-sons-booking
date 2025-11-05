@extends('frontend.layouts.app')

@section('title', 'Booking Expired')

@section('content')
    <section class="py-5 bg-light" style="min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 text-center">
                        <div class="card-body p-5">
                            <div class="mb-4">
                                <div class="expired-icon mb-3" style="font-size: 5rem; color: #dc3545;">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <h2 class="fw-bold mb-3">Booking Expired</h2>
                                <p class="text-muted fs-5">This booking has expired as payment was not completed within the time limit.</p>
                            </div>

                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-4">
                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <strong>Booking Number:</strong><br>
                                            <span class="fs-5">{{ $booking->booking_number }}</span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Amount:</strong><br>
                                            <span class="fs-5">{{ $booking->currency }} {{ number_format($booking->final_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> The seats have been released. Please create a new booking to secure your seats again.
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search me-2"></i>Create New Booking
                                </a>
                                <a href="{{ route('profile.bookings') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-ticket-perforated me-2"></i>My Bookings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


@extends('frontend.layouts.app')

@section('title', 'Our Services')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-5">Our Services</h1>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-bus-front display-4 text-primary mb-3"></i>
                            <h5 class="card-title">Bus Transportation</h5>
                            <p class="card-text">Comfortable and reliable bus services across Pakistan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-ticket-perforated display-4 text-primary mb-3"></i>
                            <h5 class="card-title">Online Booking</h5>
                            <p class="card-text">Easy online ticket booking with instant confirmation.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-headset display-4 text-primary mb-3"></i>
                            <h5 class="card-title">24/7 Support</h5>
                            <p class="card-text">Round-the-clock customer support for your convenience.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

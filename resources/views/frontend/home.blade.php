@extends('frontend.layouts.app')

@section('title', 'Home')

@section('styles')
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="hero position-relative py-5" style="background: #f8f9fa;">
        <div class="container">
            <!-- Hero Text -->
            <div class="row align-items-center justify-content-center text-center">
                <div class="col-lg-8 col-md-10">
                    <div class="hero-text mb-4">
                        <h1 class="fw-bold">
                            Timely & Trusted Transport Solutions for Pakistan’s Major Cities
                        </h1>
                        <p class="text-muted mb-0">
                            From city to city, we provide convenient transportation for all your needs.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Card (Floating) -->
        <div class="search-card-box">
            <div class="card p-3 shadow-sm border-0">
                <div class="row g-3 align-items-end">
                    <div class="col-md">
                        <label class="theme-label" for="toCity">To</label>
                        <select id="toCity" class="form-select">
                            <option selected disabled>Select destination</option>
                            <option>Lahore</option>
                            <option>Islamabad</option>
                        </select>
                    </div>

                    <div class="col-md">
                        <label class="theme-label" for="fromCity">From</label>
                        <select id="fromCity" class="form-select">
                            <option selected disabled>Select departure</option>
                            <option>Islamabad</option>
                            <option>Karachi</option>
                        </select>
                    </div>

                    <div class="col-md">
                        <label class="theme-label" for="travelDate">Date</label>
                        <input id="travelDate" type="date" class="form-control" placeholder="Choose a date">
                    </div>

                    <div class="col-md">
                        <label class="theme-label" for="passengers">Passengers</label>
                        <select id="passengers" class="form-select">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                        </select>
                    </div>

                    <div class="col-md-auto">
                        <label class="theme-label d-block">&nbsp;</label>
                        <button class="btn text-light bg-blue px-4">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script>
        console.log('Home');
    </script>
@endsection

@extends('frontend.layouts.app')

@section('title', 'Home')

@section('styles')
    <style>
        /* Search Box */
        .search-card-box {
            position: absolute;
            left: 50%;
            bottom: -45px;
            transform: translateX(-50%);
            width: 85%;
            z-index: 10;
        }

        .search-card-box .card {
            border-radius: 20px;
            border: none;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        /* Form fields */
        .search-card-box .form-control,
        .search-card-box .form-select {
            border: 2px solid var(--border-color, #e0e0e0);
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.95rem;
            color: #23262F;
            height: 45px;
        }

        .search-card-box .input-group-text {
            border: 2px solid var(--border-color, #e0e0e0);
            border-right: none;
            border-radius: 12px 0 0 12px;
            background: #fff;
            height: 45px;
        }

        /* Search Button */
        .search-card-box .btn.bg-blue {
            background-color: var(--primary-color, #007bff);
            transition: 0.3s;
            height: 45px;
        }

        .search-card-box .btn.bg-blue:hover {
            background-color: #0056b3;
        }

        /* Responsive Layouts */
        @media (max-width: 992px) {
            .search-card-box {
                width: 95%;
                bottom: -30px;
            }
        }

        @media (max-width: 768px) {
            .search-card-box {
                position: relative;
                transform: none;
                left: 0;
                bottom: 0;
                width: 100%;
                margin-top: 25px;
            }

            .search-card-box .card {
                padding: 1.5rem;
            }

            .search-card-box .input-group {
                flex-wrap: nowrap;
            }
        }

        @media (max-width: 576px) {
            .search-card-box .btn.bg-blue {
                width: 100%;
                margin-top: 10px;
            }

            .search-card-box .card {
                border-radius: 14px;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">

            </div>
            <div class="col-lg-8 col-sm-8 col-8">
                <div class="hero-text">
                    <h1 class="fw-bold">Timely & Trusted Transport Solutions for Pakistan’s Major Cities</h1>
                    <p>From city to city, we provide convenient transportation for all your needs.</p>
                </div>
            </div>
            <div class="col-lg-12">
                <!-- Search Card -->
                <div class="search-card-box">
                    <div class="card p-4 shadow-lg border-0 rounded-4">
                        <form>
                            <div class="row g-3 align-items-end justify-content-center">
                                <!-- From -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="theme-label fw-semibold">From</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-2">
                                            <i class="bi bi-geo-alt text-primary"></i>
                                        </span>
                                        <select class="form-select">
                                            <option selected disabled>Select City</option>
                                            <option>Islamabad</option>
                                            <option>Karachi</option>
                                            <option>Lahore</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- To -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="theme-label fw-semibold">To</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-2">
                                            <i class="bi bi-geo text-primary"></i>
                                        </span>
                                        <select class="form-select">
                                            <option selected disabled>Select City</option>
                                            <option>Islamabad</option>
                                            <option>Karachi</option>
                                            <option>Lahore</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="col-lg-2 col-md-6">
                                    <label class="theme-label fw-semibold">Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-2">
                                            <i class="bi bi-calendar-event text-primary"></i>
                                        </span>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>

                                <!-- Passengers -->
                                <div class="col-lg-2 col-md-6">
                                    <label class="theme-label fw-semibold">Passengers</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-2">
                                            <i class="bi bi-person text-primary"></i>
                                        </span>
                                        <select class="form-select">
                                            <option>1</option>
                                            <option>2</option>
                                            <option>3</option>
                                            <option>4</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Search Button -->
                                <div class="col-lg-2 col-md-12 d-grid">
                                    <button type="submit" class="btn bg-blue text-white fw-semibold rounded-3 py-2">
                                        <i class="bi bi-search me-2"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>
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

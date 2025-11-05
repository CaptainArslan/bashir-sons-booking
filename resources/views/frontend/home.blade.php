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
                    <div class="card p-4 shadow-md border-0 rounded-4">
                        <form id="search-form" action="{{ route('frontend.bookings.trips') }}" method="GET">
                            <div class="row g-3 align-items-end justify-content-center">
                                <!-- From -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="theme-label fw-semibold">From</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-2">
                                            <i class="bi bi-geo-alt text-primary"></i>
                                        </span>
                                        <select class="form-select" name="from_terminal_id" id="from_terminal_id" required>
                                            <option value="" selected disabled>Select Terminal</option>
                                            @foreach ($terminals as $terminal)
                                                <option value="{{ $terminal->id }}">{{ $terminal->name }} ({{ $terminal->city->name }})</option>
                                            @endforeach
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
                                        <select class="form-select" name="to_terminal_id" id="to_terminal_id" required disabled>
                                            <option value="" selected disabled>Select Destination</option>
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
                                        <input type="date" class="form-control" name="date" id="travel_date" required value="{{ $minDate }}" min="{{ $minDate }}" max="{{ $maxDate }}">
                                    </div>
                                </div>

                                <!-- Passengers -->
                                <div class="col-lg-2 col-md-6">
                                    <label class="theme-label fw-semibold">Passengers</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-2">
                                            <i class="bi bi-person text-primary"></i>
                                        </span>
                                        <input
                                            type="number"
                                            class="form-control"
                                            name="passengers"
                                            id="passengers"
                                            min="1"
                                            max="10"
                                            value="1"
                                            required
                                        >
                                    </div>
                                </div>

                                <!-- Search Button -->
                                <div class="col-lg-2 col-md-12 d-grid">
                                    <button type="submit" class="btn bg-blue text-white fw-semibold rounded-3 py-2" id="search-btn">
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

    <!-- Features -->
    <section class="features py-5 mt-5">
        <div class="container d-flex justify-content-center gap-3">
            <div class="card-custom">
                <div class="icon-wrap mx-auto mb-2">
                    <img src="{{ asset('frontend/assets/img/Frame 1369.svg') }}" alt="">
                </div>
                <div>
                    <h6>Download Our App</h6>
                    <small>In iOS & Android mobile app</small>
                </div>
            </div>
            <div class="card-custom">
                <div class="icon-wrap mx-auto mb-2">
                    <img src="{{ asset('frontend/assets/img/Ringer Volume.svg') }}" alt="">
                </div>
                <div>
                    <h6>Help Center</h6>
                    <small>Contact our live support team</small>
                </div>
            </div>
            <div class="card-custom">
                <div class="icon-wrap mx-auto mb-2">
                    <img src="{{ asset('frontend/assets/img/Delivery Time.svg') }}" alt="">
                </div>
                <div>
                    <h6>Advance Booking</h6>
                    <small>Reserve booking days in advance</small>
                </div>
            </div>

        </div>
    </section>

    <!-- Solutions -->
    <section id="services" class="py-5">
        <div class="container text-center">
            <h2 class="section-title">Comprehensive Solutions <br> for All Your Needs</h2>
            <div class="row mt-4 g-4">
                <div class="col-md-3">
                    <div class="h-100">
                        <img src="{{ asset('frontend/assets/img/image.png') }}" class="card-img-top" alt="Cargo">
                        <div class="card-body">
                            <h6>Cargo Services</h6>
                            <a href="#" class="btn btn-sm btn-search">Need more info?</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100">
                        <img src="{{ asset('frontend/assets/img/bus watermark free.png') }}" class="card-img-top"
                            alt="Transport">
                        <div class="card-body">
                            <h6>Transport Services</h6>
                            <a href="#" class="btn btn-sm btn-search">Need more info?</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100">
                        <img src="{{ asset('frontend/assets/img/image-1.png') }}" class="card-img-top"
                            alt="Logistics">
                        <div class="card-body">
                            <h6>Logistics Solutions</h6>
                            <a href="#" class="btn btn-sm btn-search">Need more info?</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="h-100">
                        <img src="{{ asset('frontend/assets/img/image-2.png') }}" class="card-img-top"
                            alt="Oil & Gas">
                        <div class="card-body">
                            <h6>Oil &amp; Gas</h6>
                            <a href="#" class="btn btn-sm btn-search">Need more info?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Use Section -->
    <section class="py-5 bg-white whyuse">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="{{ asset('frontend/assets/img/DSC_0026.png') }}" class="img-fluid rounded shadow-sm"
                        alt="Why Us">
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold">Why Use Bashir Sons?</h2>
                    <p>Enjoy more benefits than ever with our fast, reliable, and modern service. From the very beginning we
                        offer top-tier amenities to make every journey better.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <img src="{{ asset('frontend/assets/img/solar_shield-check-bold.svg') }}"
                                alt="">
                            Lowest Fares
                        </li>
                        <li class="mb-2">
                            <img src="{{ asset('frontend/assets/img/solar_shield-check-bold.svg') }}"
                                alt="">
                            Excellent Customer Support
                        </li>
                        <li class="mb-2"> <img
                                src="{{ asset('frontend/assets/img/solar_shield-check-bold.svg') }}"
                                alt="">
                            Punctual Departures</li>
                        <li class="mb-2"> <img
                                src="{{ asset('frontend/assets/img/solar_shield-check-bold.svg') }}"
                                alt="">
                            Luxury & Comfort</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 stat">
                    <div>
                        <h3>1.5M</h3>
                        <p>Happy Customer</p>
                    </div>
                </div>
                <div class="col-md-3 stat">
                    <div>
                        <h3>60+</h3>
                        <p>Years of Experience</p>
                    </div>
                </div>
                <div class="col-md-3 stat">
                    <div>
                        <h3>10+</h3>
                        <p> Terminals</p>
                    </div>
                </div>
                <div class="col-md-3 stat">
                    <div>
                        <h3>500+</h3>
                        <p>Vehicles</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking CTA -->
    <section class="booking">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="{{ asset('frontend/assets/img/image-3.png') }}" class="img-fluid rounded shadow-sm"
                        alt="Support">
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold">Plan Ahead with Advance Booking</h2>
                    <p>Secure your seat in advance with our hassle-free booking service for all routes.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><img
                                src="{{ asset('frontend/assets/img/solar_shield-check-bold.svg') }}"
                                alt=""> Guaranteed Seat</li>
                        <li class="mb-2"><img
                                src="{{ asset('frontend/assets/img/solar_shield-check-bold.svg') }}"
                                alt=""> Save Time</li>
                        <li class="mb-2"><img
                                src="{{ asset('frontend/assets/img/solar_shield-check-bold.svg') }}"
                                alt=""> Easy Booking</li>
                    </ul>
                    <button class="btn btn-cta mt-3">Call Now</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonial py-5">
        <div class="container text-center">
            <div class="heading-testimonial">
                <h2 class="fw-bold mb-4 text-left">What people<br>are saying</h2>
                <div class="google"> <img src="{{ asset('frontend/assets/img/Rectangle.png') }}" alt="">
                    <p>Google Rating <br>
                        4.9(1,300+)</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card p-4">
                        <div class="stars mb-2">★★★★★</div>
                        <p>I booked online and they showed up right on time. The team was super friendly and cleared
                            everything out in no time. Great service at a great price—highly recommended!</p>
                        <p class="fw-bold mb-0">— Jessica L.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-4">
                        <div class="stars mb-2">★★★★★</div>
                        <p>I booked online and they showed up right on time. The team was super friendly and cleared
                            everything out in no time. Great service at a great price—highly recommended!</p>
                        <p class="fw-bold mb-0">— Ahmed K.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq py-5">
        <div class="container py-5">
            <!-- Section Title -->
            <div class="text-center mb-5">
                <h2 class="faq-heading mb-2">Frequently Asked Question</h2>
                <p class="faq-subtitle">
                    Find answers to commonly asked questions about our services, routes,
                    and facilities.
                </p>
            </div>

            <!-- FAQ Grid: 3 rows × 2 columns -->
            <div class="row gx-4 gy-4">
                <!-- Row 1, Col 1 -->
                <div class="col-md-6">
                    <div class="faq-card">
                        <div class="faq-item">
                            <img src="{{ asset('frontend/assets/img/icon.svg') }}" alt="">
                            <div>
                                <h5 class="faq-question">What facilities are available during the journey?</h5>
                                <p class="faq-answer">
                                    We offer movies and entertainment, refreshments, spacious
                                    luggage compartments, and free Wi-Fi to make your journey
                                    comfortable.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 1, Col 2 -->
                <div class="col-md-6">
                    <div class="faq-card">
                        <div class="faq-item">
                            <img src="{{ asset('frontend/assets/img/icon.svg') }}" alt="">
                            <div>
                                <h5 class="faq-question">
                                    How can I get updated information about fares and schedules?
                                </h5>
                                <p class="faq-answer">
                                    You can visit our terminal, check our website, or call our UAN
                                    at <strong>041-111-737-737</strong> for the latest fare and
                                    schedule details.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2, Col 1 -->
                <div class="col-md-6">
                    <div class="faq-card">
                        <div class="faq-item">
                            <img src="{{ asset('frontend/assets/img/icon.svg') }}" alt="">
                            <div>
                                <h5 class="faq-question">
                                    What should I do if I lose an item during travel?
                                </h5>
                                <p class="faq-answer">
                                    If you lose any belongings, please contact our support team
                                    immediately. Our staff ensures lost items are safely returned
                                    to their owners.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2, Col 2 -->
                <div class="col-md-6">
                    <div class="faq-card">
                        <div class="faq-item">
                            <img src="{{ asset('frontend/assets/img/icon.svg') }}" alt="">
                            <div>
                                <h5 class="faq-question">What routes do Bashir Sons cover?</h5>
                                <p class="faq-answer">
                                    We provide transport services across major cities, including
                                    Lahore, Toba Tek Singh, and Rajhana.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3, Col 1 -->
                <div class="col-md-6">
                    <div class="faq-card">
                        <div class="faq-item">
                            <img src="{{ asset('frontend/assets/img/icon.svg') }}" alt="">
                            <div>
                                <h5 class="faq-question">Does Bashir Sons provide advance ticket booking?</h5>
                                <p class="faq-answer">
                                    Yes, we offer an easy and convenient advance ticket booking
                                    service for all routes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3, Col 2 -->
                <div class="col-md-6">
                    <div class="faq-card">
                        <div class="faq-item">
                            <img src="{{ asset('frontend/assets/img/icon.svg') }}" alt="">
                            <div>
                                <h5 class="faq-question">How can I book my tickets?</h5>
                                <p class="faq-answer">
                                    You can book your tickets online through our website or by
                                    calling our UAN: <strong>041-111-737-737</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- .row -->
        </div>
        <!-- .container -->
    </section>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            $('#travel_date').attr('min', today);

            // Handle From Terminal Change
            $('#from_terminal_id').on('change', function() {
                const fromTerminalId = $(this).val();
                const toSelect = $('#to_terminal_id');
                
                // Reset To Terminal
                toSelect.html('<option value="" selected disabled>Select Destination</option>');
                toSelect.prop('disabled', true);

                if (fromTerminalId) {
                    fetchRouteStops(fromTerminalId);
                }
            });

            // Fetch Route Stops (To Terminals)
            function fetchRouteStops(fromTerminalId) {
                $.ajax({
                    url: "{{ route('frontend.route-stops') }}",
                    type: 'GET',
                    data: {
                        from_terminal_id: fromTerminalId
                    },
                    success: function(response) {
                        const toSelect = $('#to_terminal_id');
                        
                        if (response.route_stops && response.route_stops.length > 0) {
                            toSelect.html('<option value="" selected disabled>Select Destination</option>');
                            
                            response.route_stops.forEach(function(stop) {
                                toSelect.append(
                                    `<option value="${stop.terminal_id}">${stop.terminal.name} (${stop.terminal.code})</option>`
                                );
                            });
                            
                            toSelect.prop('disabled', false);
                        } else {
                            toSelect.html('<option value="" selected disabled>No destinations available</option>');
                            Swal.fire({
                                icon: 'info',
                                title: 'No Routes Found',
                                text: 'No active routes found for the selected terminal.',
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    },
                    error: function(xhr) {
                        const toSelect = $('#to_terminal_id');
                        toSelect.html('<option value="" selected disabled>Error loading destinations</option>');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Load Destinations',
                            text: xhr.responseJSON?.error || 'Unable to fetch available destinations. Please try again.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }

            // Handle Form Submission - Basic validation only
            $('#search-form').on('submit', function(e) {
                const fromTerminalId = $('#from_terminal_id').val();
                const toTerminalId = $('#to_terminal_id').val();
                const date = $('#travel_date').val();
                
                // Basic validation
                if (!fromTerminalId || !toTerminalId || !date) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please select From, To, and Date before searching.',
                        confirmButtonColor: '#ffc107'
                    });
                    return false;
                }

                // Check if terminals are different
                if (fromTerminalId === toTerminalId) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Selection',
                        text: 'From and To terminals must be different.',
                        confirmButtonColor: '#ffc107'
                    });
                    return false;
                }

                // Allow form to submit normally
                return true;
            });
        });
    </script>
@endsection

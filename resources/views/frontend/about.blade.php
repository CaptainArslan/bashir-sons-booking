@extends('frontend.layouts.app')

@section('title', 'About Us')

@section('content')
    <section class="solutions">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <img src="{{ asset('frontend/assets/img/img-33.png') }}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h1 class="solution-heading">
                        Reliable Travel & Cargo Solutions — Since Day One
                    </h1>
                    <div class="card-solutions">
                        <div>
                            <div class="circle">
                                <img src="{{ asset('frontend/assets/img/icon-vision.png') }}" alt="">
                            </div>
                        </div>
                        <div class="text-description">
                            <h5>Our Vision</h5>
                            <p>To be Pakistan’s most trusted name in intercity travel and cargo, delivering safe,
                                affordable, and comfortable journeys with unmatched customer care.</p>
                        </div>
                    </div>
                    <div class="card-solutions">
                        <div>
                            <div class="circle">
                                <img src="{{ asset('frontend/assets/img/icon-mission.png') }}" alt="">
                            </div>
                        </div>
                        <div class="text-description">
                            <h5>Our Mission</h5>
                            <p>We are committed to providing top-quality transportation and cargo services that combine
                                convenience, innovation, and reliability — ensuring every passenger and parcel reaches its
                                destination safely and on time.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('services') }}">Our Services <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--  -->

    <!-- Stats -->
    <section class="stats aboutus">
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

    <!-- HTML -->
    <section class="video">
        <a href="https://www.youtube.com/watch?v=UfeIRvP6_pE" class="video__link" aria-label="Play video">
            <img src="{{ asset('frontend/assets/img/bus.avif') }}" alt="Video preview" class="video__thumb" />
            <span class="video__play-icon"></span>
        </a>
        <p class="mt-4">
            Bashir Sons is a trusted name in intercity travel and cargo services, proudly connecting 8–10 major cities
            including Lahore, Faisalabad, Gojra, Samundri, Shorkot, Toba Tek Singh, Kamalia, Rajana, Hafizabad, and
            Gujranwala. With a commitment to affordable fares, timely departures, and excellent customer service, we ensure
            safe and comfortable journeys for all. Our modern bus fleet and SMS-tracked cargo service set us apart in the
            industry. Whether you're traveling or sending goods — Bashir Sons is your reliable partner.
        </p>
    </section>

    <section class="sections">
        <div class="container">
            <div class="row section-1">
                <div class="col-lg-7">
                    <div class="title-sections">
                        Reliable and Fast Cargo Services
                    </div>
                    <p class="description-sections">
                        Send your parcels, gifts, and goods across Pakistan with confidence. Our SMS tracking ensures peace
                        of mind from dispatch to delivery. Trusted by thousands for secure and timely cargo handling.
                    </p>
                </div>
                <div class="col-lg-5 right-image">
                    <img src="{{ asset('frontend/assets/img/section-1.png') }}" class="img-fluid" alt="">
                </div>
            </div>
            <div class="row section-1 mt-5">
                <div class="col-lg-5">
                    <img src="{{ asset('frontend/assets/img/section-2.png') }}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-7">
                    <div class="title-sections">
                        Premium Intercity Transport Services
                    </div>
                    <p class="description-sections">
                        Travel in comfort with our luxury and economy class buses. We operate across major cities like
                        Lahore, Faisalabad, and Multan. Enjoy the lowest fares with on-time departures and modern amenities.
                    </p>
                </div>

            </div>
            <div class="row section-1">
                <div class="col-lg-7">
                    <div class="title-sections">
                        Nationwide Logistics and Freight Solutions
                    </div>
                    <p class="description-sections">
                        From light parcels to heavy machinery—we move it all. Equipped with a wide fleet of trucks,
                        containers, and trollies. Our logistics network spans all major commercial routes in Pakistan.
                    </p>
                </div>
                <div class="col-lg-5 right-image">
                    <img src="{{ asset('frontend/assets/img/section-3.png') }}" class="img-fluid" alt="">
                </div>
            </div>
            <div class="row section-1 mt-5">
                <div class="col-lg-5">
                    <img src="{{ asset('frontend/assets/img/section-4.png') }}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-7">
                    <div class="title-sections">
                        Quality Oil & Gas Distribution Services
                    </div>
                    <p class="description-sections">
                        Fuel your journey with our premium petroleum products. We operate petrol stations at key highway and
                        urban locations. Committed to safety, quality, and uninterrupted supply.
                    </p>
                </div>

            </div>
        </div>
    </section>
@endsection

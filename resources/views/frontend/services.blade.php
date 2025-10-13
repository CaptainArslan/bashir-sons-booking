@extends('frontend.layouts.app')

@section('title', 'Our Services')

@section('content')
    <section class="box-blue">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Search Card -->
                    <div class="search-card-box">
                        <div class="card p-4 shadow-md border-0 rounded-4">
                            <form action="{{ route('bookings') }}" method="GET">
                                <div class="row g-3 align-items-end justify-content-center">
                                    <!-- From -->
                                    <div class="col-lg-3 col-md-6">
                                        <label class="theme-label fw-semibold">From</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-2">
                                                <i class="bi bi-geo-alt text-primary"></i>
                                            </span>
                                            <select class="form-select">
                                                <option selected disabled>select from</option>
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
                                                <option selected disabled>select to</option>
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
        </div>
    </section>

    <section class="widgets py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <!-- Widget 1 -->
                <div class="col-12">
                    <a href="#" class="card widget-card h-100 text-center border-0 p-4">
                        <div class="d-flex">
                            <div class="mb-3">
                                <i class="bi bi-bus-front"></i>
                            </div>
                            <div class="info">
                                <h4 class="fw-bold mb-1 text-primary">Executive Class</h4>
                                <p class="location mb-0"><strong>FSD-LHR</strong> <span
                                        class="d-inline ms-3 text-muted">01:00 PM</span></p>
                                <p class="text-muted mb-0">
                                    Refereshments | Movies & Entertainment | Max 30Kg per seat
                                </p>
                            </div>
                            <div class="price">

                                <p class="text-muted mb-0">
                                    477
                                </p>
                                <h3>399</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="#" class="card widget-card h-100 text-center border-0 p-4">
                        <div class="d-flex">
                            <div class="mb-3">
                                <i class="bi bi-bus-front"></i>
                            </div>
                            <div class="info">
                                <h4 class="fw-bold mb-1 text-primary">Executive Class</h4>
                                <p class="location mb-0"><strong>FSD-LHR</strong> <span
                                        class="d-inline ms-3 text-muted">01:00 PM</span></p>
                                <p class="text-muted mb-0">
                                    Refereshments | Movies & Entertainment | Max 30Kg per seat
                                </p>
                            </div>
                            <div class="price">

                                <p class="text-muted mb-0">
                                    477
                                </p>
                                <h3>399</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="#" class="card widget-card h-100 text-center border-0 p-4">
                        <div class="d-flex">
                            <div class="mb-3">
                                <i class="bi bi-bus-front"></i>
                            </div>
                            <div class="info">
                                <h4 class="fw-bold mb-1 text-primary">Executive Class</h4>
                                <p class="location mb-0"><strong>FSD-LHR</strong> <span
                                        class="d-inline ms-3 text-muted">01:00 PM</span></p>
                                <p class="text-muted mb-0">
                                    Refereshments | Movies & Entertainment | Max 30Kg per seat
                                </p>
                            </div>
                            <div class="price">

                                <p class="text-muted mb-0">
                                    477
                                </p>
                                <h3>399</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="#" class="card widget-card h-100 text-center border-0 p-4">
                        <div class="d-flex">
                            <div class="mb-3">
                                <i class="bi bi-bus-front"></i>
                            </div>
                            <div class="info">
                                <h4 class="fw-bold mb-1 text-primary">Executive Class</h4>
                                <p class="location mb-0"><strong>FSD-LHR</strong> <span
                                        class="d-inline ms-3 text-muted">01:00 PM</span></p>
                                <p class="text-muted mb-0">
                                    Refereshments | Movies & Entertainment | Max 30Kg per seat
                                </p>
                            </div>
                            <div class="price">

                                <p class="text-muted mb-0">
                                    477
                                </p>
                                <h3>399</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="#" class="card widget-card h-100 text-center border-0 p-4">
                        <div class="d-flex">
                            <div class="mb-3">
                                <i class="bi bi-bus-front"></i>
                            </div>
                            <div class="info">
                                <h4 class="fw-bold mb-1 text-primary">Executive Class</h4>
                                <p class="location mb-0"><strong>FSD-LHR</strong> <span
                                        class="d-inline ms-3 text-muted">01:00 PM</span></p>
                                <p class="text-muted mb-0">
                                    Refereshments | Movies & Entertainment | Max 30Kg per seat
                                </p>
                            </div>
                            <div class="price">

                                <p class="text-muted mb-0">
                                    477
                                </p>
                                <h3>399</h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection

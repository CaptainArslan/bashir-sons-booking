@extends('admin.layouts.app')

@section('title', 'Advance Booking Settings')

@section('content')
    <style>
        .status-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        .status-card.enabled {
            border-left-color: #28a745;
        }
        .status-card.disabled {
            border-left-color: #dc3545;
        }
        .switch-container {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .days-input-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
        }
        @if($settings && $settings->advance_booking_enable)
        .days-input-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        @endif
        .form-switch .form-check-input {
            width: 3.5rem;
            height: 1.75rem;
        }
        .form-switch .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 fw-bold">
                                <i class="bx bx-calendar-check me-2"></i>
                                Advance Booking Settings
                            </h4>
                            <p class="text-muted mb-0 mt-1">Configure advance booking preferences for your transport system</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Advance Booking</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <!-- Status Overview Cards -->
            <div class="row mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card status-card {{ $settings && $settings->advance_booking_enable ? 'enabled' : 'disabled' }} shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md bg-{{ $settings && $settings->advance_booking_enable ? 'success' : 'danger' }}-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx {{ $settings && $settings->advance_booking_enable ? 'bx-check-circle' : 'bx-x-circle' }} font-size-24 text-{{ $settings && $settings->advance_booking_enable ? 'success' : 'danger' }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">Status</p>
                                    <h5 class="mb-0">
                                        <span class="badge bg-{{ $settings && $settings->advance_booking_enable ? 'success' : 'danger' }}">
                                            {{ $settings && $settings->advance_booking_enable ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-calendar font-size-24 text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">Allowed Days</p>
                                    <h4 class="mb-0 fw-bold text-primary">{{ $settings && $settings->advance_booking_days ? $settings->advance_booking_days : 7 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-time-five font-size-24 text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">Booking Type</p>
                                    <h6 class="mb-0 fw-bold">
                                        {{ $settings && $settings->advance_booking_enable ? 'Advance' : 'Same Day' }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-cog font-size-24 text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">Configuration</p>
                                    <h6 class="mb-0 fw-bold">Active</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="bx bx-slider-alt text-primary font-size-18"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 fw-bold">Configuration Settings</h5>
                                    <p class="text-muted mb-0 small">Manage advance booking preferences</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-check-circle font-size-20 me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong>Success!</strong> {{ session('success') }}
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-error-circle font-size-20 me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong>Error!</strong> {{ session('error') }}
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.advance-booking.update') }}">
                                @csrf
                                @method('PUT')

                                <!-- Enable/Disable Switch -->
                                <div class="switch-container mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <label class="form-label fw-bold mb-1">
                                                <i class="bx bx-toggle-right me-2"></i>
                                                Advance Booking Status
                                            </label>
                                            <p class="text-muted small mb-0">Toggle to enable or disable advance booking</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="advance_booking_enable"
                                                name="advance_booking_enable" value="1"
                                                {{ $settings && $settings->advance_booking_enable ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold ms-2" for="advance_booking_enable">
                                                <span class="badge bg-{{ $settings && $settings->advance_booking_enable ? 'success' : 'secondary' }} px-3 py-2">
                                                    {{ $settings && $settings->advance_booking_enable ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="alert alert-{{ $settings && $settings->advance_booking_enable ? 'success' : 'secondary' }}-subtle mb-0">
                                        <i class="bx bx-info-circle me-2"></i>
                                        {{ $settings && $settings->advance_booking_enable 
                                            ? 'Customers can book tickets in advance up to ' . ($settings->advance_booking_days ?? 7) . ' days.'
                                            : 'Only same-day bookings are currently allowed. Enable advance booking to allow future date reservations.' }}
                                    </div>
                                </div>

                                <!-- Days Input -->
                                <div class="days-input-card mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="bx bx-calendar font-size-18 text-white"></i>
                                        </div>
                                        <div>
                                            <label class="form-label text-white fw-bold mb-1">
                                                Number of Days Allowed
                                                <span class="badge bg-light text-dark ms-2">Required</span>
                                            </label>
                                            <p class="text-white-50 mb-0 small">Set how many days in advance customers can book</p>
                                        </div>
                                    </div>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white border-0">
                                            <i class="bx bx-calendar-check text-primary"></i>
                                        </span>
                                        <input type="number" 
                                            class="form-control form-control-lg border-0 shadow-sm" 
                                            id="advance_booking_days"
                                            name="advance_booking_days"
                                            value="{{ $settings && $settings->advance_booking_days ? $settings->advance_booking_days : 7 }}"
                                            min="1" max="365" 
                                            required 
                                            placeholder="Enter days (1-365)">
                                        <span class="input-group-text bg-white border-0 fw-bold text-primary">days</span>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-white-50">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Range: 1-365 days. You can configure this value before enabling advance booking.
                                        </small>
                                    </div>
                                    @error('advance_booking_days')
                                        <div class="alert alert-light mt-2 mb-0">
                                            <i class="bx bx-error-circle me-2"></i>
                                            <strong>Error:</strong> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Save Button -->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-lg px-4">
                                        <i class="bx bx-save me-2"></i>
                                        Save Settings
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="bx bx-info-circle text-info font-size-18"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 fw-bold">Information</h5>
                                    <p class="text-muted mb-0 small">How it works</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    When Enabled
                                </h6>
                                <ul class="list-unstyled ps-3 mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-right-arrow-circle text-primary me-2"></i>
                                        Customers can book tickets for future dates
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-right-arrow-circle text-primary me-2"></i>
                                        Booking window: Up to <strong>{{ $settings && $settings->advance_booking_days ? $settings->advance_booking_days : 7 }}</strong> days
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-right-arrow-circle text-primary me-2"></i>
                                        Based on system availability
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bx bx-x-circle text-danger me-2"></i>
                                    When Disabled
                                </h6>
                                <ul class="list-unstyled ps-3 mb-0">
                                    <li class="mb-2">
                                        <i class="bx bx-right-arrow-circle text-secondary me-2"></i>
                                        Only same-day bookings allowed
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-right-arrow-circle text-secondary me-2"></i>
                                        Future date selection blocked
                                    </li>
                                </ul>
                            </div>

                            <div class="alert alert-light border mb-0">
                                <div class="d-flex align-items-start">
                                    <i class="bx bx-bulb text-warning font-size-20 me-2 mt-1"></i>
                                    <div>
                                        <strong class="d-block mb-1">Pro Tip</strong>
                                        <small class="text-muted">
                                            Configure the number of days before enabling to ensure smooth setup. You can adjust this anytime.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


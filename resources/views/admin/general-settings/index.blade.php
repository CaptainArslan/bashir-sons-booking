@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('content')
    <style>
        .info-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        .info-card.company {
            border-left-color: #0d6efd;
        }
        .info-card.contact {
            border-left-color: #198754;
        }
        .info-card.social {
            border-left-color: #dc3545;
        }
        .info-card.media {
            border-left-color: #ffc107;
        }
        .info-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 160px;
        }
        .info-value {
            color: #212529;
        }
        .social-icon {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            margin-right: 8px;
        }
        .empty-state {
            padding: 4rem 2rem;
        }
        .empty-state-icon {
            font-size: 5rem;
            opacity: 0.3;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 fw-bold">
                                <i class="bx bx-cog me-2"></i>
                                General Settings
                            </h4>
                            <p class="text-muted mb-0 mt-1">Manage your company information and configuration</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">General Settings</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if($settings)
                <div class="row">
                    <!-- Company Information -->
                    <div class="col-xl-6 mb-4">
                        <div class="card info-card company shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="bx bx-building text-primary font-size-18"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold">Company Information</h5>
                                        <p class="text-muted mb-0 small">Business details and address</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-buildings me-2 text-primary"></i>
                                            Company Name:
                                        </span>
                                        <span class="info-value">{{ $settings->company_name }}</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-envelope me-2 text-primary"></i>
                                            Email:
                                        </span>
                                        <span class="info-value">{{ $settings->email }}</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-phone me-2 text-primary"></i>
                                            Phone:
                                        </span>
                                        <span class="info-value">{{ $settings->phone }}</span>
                                    </div>
                                </div>
                                @if($settings->alternate_phone)
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-phone-call me-2 text-muted"></i>
                                            Alternate Phone:
                                        </span>
                                        <span class="info-value">{{ $settings->alternate_phone }}</span>
                                    </div>
                                </div>
                                @endif
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-map me-2 text-primary"></i>
                                            Address:
                                        </span>
                                        <span class="info-value">{{ $settings->address }}</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-map-pin me-2 text-primary"></i>
                                            Location:
                                        </span>
                                        <span class="info-value">
                                            {{ $settings->city }}, {{ $settings->state }}, {{ $settings->country }}
                                            @if($settings->postal_code)
                                                - {{ $settings->postal_code }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @if($settings->website_url)
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-globe me-2 text-primary"></i>
                                            Website:
                                        </span>
                                        <span class="info-value">
                                            <a href="{{ $settings->website_url }}" target="_blank" class="text-decoration-none">
                                                {{ $settings->website_url }}
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($settings->tagline)
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-quote-left me-2 text-primary"></i>
                                            Tagline:
                                        </span>
                                        <span class="info-value fst-italic">{{ $settings->tagline }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Support -->
                    <div class="col-xl-6 mb-4">
                        <div class="card info-card contact shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="bx bx-headphone text-success font-size-18"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold">Contact & Support</h5>
                                        <p class="text-muted mb-0 small">Customer support information</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($settings->support_email)
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-envelope me-2 text-success"></i>
                                            Support Email:
                                        </span>
                                        <span class="info-value">
                                            <a href="mailto:{{ $settings->support_email }}" class="text-decoration-none">
                                                {{ $settings->support_email }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($settings->support_phone)
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-phone me-2 text-success"></i>
                                            Support Phone:
                                        </span>
                                        <span class="info-value">{{ $settings->support_phone }}</span>
                                    </div>
                                </div>
                                @endif
                                @if($settings->business_hours)
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-time me-2 text-success"></i>
                                            Business Hours:
                                        </span>
                                        <span class="info-value">{{ $settings->business_hours }}</span>
                                    </div>
                                </div>
                                @endif
                                <div class="info-item">
                                    <div class="d-flex">
                                        <span class="info-label">
                                            <i class="bx bx-money me-2 text-success"></i>
                                            Mobile Wallet Tax:
                                        </span>
                                        <span class="info-value">PKR {{ number_format($settings->mobile_wallet_tax ?? 40, 0) }} per seat</span>
                                    </div>
                                </div>
                                @if(!$settings->support_email && !$settings->support_phone && !$settings->business_hours)
                                <div class="text-center text-muted py-3">
                                    <i class="bx bx-info-circle me-2"></i>
                                    No support information configured
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    @if($settings->facebook_url || $settings->instagram_url || $settings->twitter_url || $settings->linkedin_url || $settings->youtube_url)
                    <div class="col-xl-6 mb-4">
                        <div class="card info-card social shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="bx bx-share-alt text-danger font-size-18"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold">Social Media</h5>
                                        <p class="text-muted mb-0 small">Social media links and profiles</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($settings->facebook_url)
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <span class="info-label">
                                            <span class="social-icon bg-primary text-white">
                                                <i class="bx bxl-facebook"></i>
                                            </span>
                                            Facebook:
                                        </span>
                                        <span class="info-value">
                                            <a href="{{ $settings->facebook_url }}" target="_blank" class="text-decoration-none">
                                                Visit Profile
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($settings->instagram_url)
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <span class="info-label">
                                            <span class="social-icon bg-danger text-white" style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%) !important;">
                                                <i class="bx bxl-instagram"></i>
                                            </span>
                                            Instagram:
                                        </span>
                                        <span class="info-value">
                                            <a href="{{ $settings->instagram_url }}" target="_blank" class="text-decoration-none">
                                                Visit Profile
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($settings->twitter_url)
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <span class="info-label">
                                            <span class="social-icon bg-info text-white">
                                                <i class="bx bxl-twitter"></i>
                                            </span>
                                            Twitter:
                                        </span>
                                        <span class="info-value">
                                            <a href="{{ $settings->twitter_url }}" target="_blank" class="text-decoration-none">
                                                Visit Profile
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($settings->linkedin_url)
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <span class="info-label">
                                            <span class="social-icon bg-primary text-white">
                                                <i class="bx bxl-linkedin"></i>
                                            </span>
                                            LinkedIn:
                                        </span>
                                        <span class="info-value">
                                            <a href="{{ $settings->linkedin_url }}" target="_blank" class="text-decoration-none">
                                                Visit Profile
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($settings->youtube_url)
                                <div class="info-item">
                                    <div class="d-flex align-items-center">
                                        <span class="info-label">
                                            <span class="social-icon bg-danger text-white">
                                                <i class="bx bxl-youtube"></i>
                                            </span>
                                            YouTube:
                                        </span>
                                        <span class="info-value">
                                            <a href="{{ $settings->youtube_url }}" target="_blank" class="text-decoration-none">
                                                Visit Channel
                                                <i class="bx bx-link-external ms-1"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Media Files -->
                    @if($settings->logo || $settings->favicon)
                    <div class="col-xl-6 mb-4">
                        <div class="card info-card media shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="bx bx-image text-warning font-size-18"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold">Media Files</h5>
                                        <p class="text-muted mb-0 small">Company logo and favicon</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if($settings->logo)
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold mb-2">
                                            <i class="bx bx-image me-2"></i>
                                            Company Logo
                                        </label>
                                        <div class="border rounded p-3 bg-light text-center">
                                            <img src="{{ Storage::url($settings->logo) }}" alt="Company Logo" class="img-fluid" style="max-height: 150px;" onerror="this.src='{{ asset('images/placeholder.png') }}'; this.onerror=null;">
                                        </div>
                                    </div>
                                    @endif
                                    @if($settings->favicon)
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold mb-2">
                                            <i class="bx bx-bookmark me-2"></i>
                                            Favicon
                                        </label>
                                        <div class="border rounded p-3 bg-light text-center">
                                            <img src="{{ Storage::url($settings->favicon) }}" alt="Favicon" class="img-fluid" style="max-height: 100px;" onerror="this.src='{{ asset('images/placeholder.png') }}'; this.onerror=null;">
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Action Button -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.general-settings.edit', $settings->id) }}" class="btn btn-primary btn-lg px-4">
                                <i class="bx bx-edit me-2"></i>
                                Edit Settings
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body empty-state text-center">
                                <div class="empty-state-icon text-muted mb-3">
                                    <i class="bx bx-cog"></i>
                                </div>
                                <h4 class="mb-3">No Settings Found</h4>
                                <p class="text-muted mb-4">General settings have not been configured yet. Please create your company settings to get started.</p>
                                <a href="{{ route('admin.general-settings.create') }}" class="btn btn-primary btn-lg px-4">
                                    <i class="bx bx-plus me-2"></i>
                                    Create Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Create General Settings')

@section('content')
    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid;
        }
        .form-section.company {
            border-left-color: #0d6efd;
        }
        .form-section.media {
            border-left-color: #ffc107;
        }
        .form-section.social {
            border-left-color: #dc3545;
        }
        .form-section.support {
            border-left-color: #198754;
        }
        .section-header {
            display: flex;
            align-items-center;
            margin-bottom: 1.5rem;
        }
        .section-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        .section-icon.company {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .section-icon.media {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .section-icon.social {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .section-icon.support {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .preview-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            background: #f8f9fa;
            margin-top: 0.5rem;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .preview-container img {
            max-width: 100%;
            max-height: 120px;
            border-radius: 4px;
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
                                <i class="bx bx-plus-circle me-2"></i>
                                Create General Settings
                            </h4>
                            <p class="text-muted mb-0 mt-1">Configure your company information and settings</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.general-settings.index') }}">General Settings</a></li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('error'))
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

            <form action="{{ route('admin.general-settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Company Information -->
                        <div class="form-section company">
                            <div class="section-header">
                                <div class="section-icon company text-white">
                                    <i class="bx bx-building font-size-24"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Company Information</h5>
                                    <p class="text-muted mb-0 small">Basic company details and address</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label required-field">Company Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary-subtle border-0">
                                            <i class="bx bx-building text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                            id="company_name" name="company_name" 
                                            placeholder="Enter Company Name" value="{{ old('company_name') }}" required>
                                    </div>
                                    @error('company_name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label required-field">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary-subtle border-0">
                                            <i class="bx bx-envelope text-primary"></i>
                                        </span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                            id="email" name="email" 
                                            placeholder="Enter Email" value="{{ old('email') }}" required>
                                    </div>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label required-field">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary-subtle border-0">
                                            <i class="bx bx-phone text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                            id="phone" name="phone" 
                                            placeholder="Enter Phone" value="{{ old('phone') }}" required>
                                    </div>
                                    @error('phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="alternate_phone" class="form-label">Alternate Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="bx bx-phone-call text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control @error('alternate_phone') is-invalid @enderror" 
                                            id="alternate_phone" name="alternate_phone" 
                                            placeholder="Enter Alternate Phone" value="{{ old('alternate_phone') }}">
                                    </div>
                                    @error('alternate_phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label required-field">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary-subtle border-0 align-items-start pt-3">
                                            <i class="bx bx-map text-primary"></i>
                                        </span>
                                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                            id="address" name="address" rows="2" 
                                            placeholder="Enter Address" required>{{ old('address') }}</textarea>
                                    </div>
                                    @error('address')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="city" class="form-label required-field">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                        id="city" name="city" 
                                        placeholder="Enter City" value="{{ old('city') }}" required>
                                    @error('city')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="state" class="form-label required-field">State</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                        id="state" name="state" 
                                        placeholder="Enter State" value="{{ old('state') }}" required>
                                    @error('state')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="country" class="form-label required-field">Country</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                        id="country" name="country" 
                                        placeholder="Enter Country" value="{{ old('country') }}" required>
                                    @error('country')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                        id="postal_code" name="postal_code" 
                                        placeholder="Enter Postal Code" value="{{ old('postal_code') }}">
                                    @error('postal_code')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="website_url" class="form-label">Website URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="bx bx-globe text-muted"></i>
                                        </span>
                                        <input type="url" class="form-control @error('website_url') is-invalid @enderror" 
                                            id="website_url" name="website_url" 
                                            placeholder="https://example.com" value="{{ old('website_url') }}">
                                    </div>
                                    @error('website_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="tagline" class="form-label">Tagline</label>
                                    <input type="text" class="form-control @error('tagline') is-invalid @enderror" 
                                        id="tagline" name="tagline" 
                                        placeholder="Enter Company Tagline" value="{{ old('tagline') }}">
                                    @error('tagline')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Media -->
                        <div class="form-section media">
                            <div class="section-header">
                                <div class="section-icon media text-white">
                                    <i class="bx bx-image font-size-24"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Media Files</h5>
                                    <p class="text-muted mb-0 small">Upload company logo and favicon</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="logo" class="form-label">Company Logo</label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                        id="logo" name="logo" accept="image/*">
                                    <div class="form-text">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Supported formats: JPEG, PNG, JPG, GIF, WebP. Maximum size: 2MB
                                    </div>
                                    @error('logo')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="favicon" class="form-label">Favicon</label>
                                    <input type="file" class="form-control @error('favicon') is-invalid @enderror" 
                                        id="favicon" name="favicon" accept="image/*">
                                    <div class="form-text">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Supported formats: JPEG, PNG, JPG, GIF, ICO, WebP. Maximum size: 1MB
                                    </div>
                                    @error('favicon')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="form-section social">
                            <div class="section-header">
                                <div class="section-icon social text-white">
                                    <i class="bx bx-share-alt font-size-24"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Social Media</h5>
                                    <p class="text-muted mb-0 small">Social media links and profiles</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white border-0">
                                            <i class="bx bxl-facebook"></i>
                                        </span>
                                        <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" 
                                            id="facebook_url" name="facebook_url" 
                                            placeholder="https://facebook.com/yourpage" value="{{ old('facebook_url') }}">
                                    </div>
                                    @error('facebook_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0" style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);">
                                            <i class="bx bxl-instagram text-white"></i>
                                        </span>
                                        <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" 
                                            id="instagram_url" name="instagram_url" 
                                            placeholder="https://instagram.com/yourpage" value="{{ old('instagram_url') }}">
                                    </div>
                                    @error('instagram_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="twitter_url" class="form-label">Twitter URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info text-white border-0">
                                            <i class="bx bxl-twitter"></i>
                                        </span>
                                        <input type="url" class="form-control @error('twitter_url') is-invalid @enderror" 
                                            id="twitter_url" name="twitter_url" 
                                            placeholder="https://twitter.com/yourpage" value="{{ old('twitter_url') }}">
                                    </div>
                                    @error('twitter_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white border-0">
                                            <i class="bx bxl-linkedin"></i>
                                        </span>
                                        <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" 
                                            id="linkedin_url" name="linkedin_url" 
                                            placeholder="https://linkedin.com/company/yourpage" value="{{ old('linkedin_url') }}">
                                    </div>
                                    @error('linkedin_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="youtube_url" class="form-label">YouTube URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-danger text-white border-0">
                                            <i class="bx bxl-youtube"></i>
                                        </span>
                                        <input type="url" class="form-control @error('youtube_url') is-invalid @enderror" 
                                            id="youtube_url" name="youtube_url" 
                                            placeholder="https://youtube.com/channel/yourchannel" value="{{ old('youtube_url') }}">
                                    </div>
                                    @error('youtube_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Support Information -->
                        <div class="form-section support">
                            <div class="section-header">
                                <div class="section-icon support text-white">
                                    <i class="bx bx-headphone font-size-24"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Support Information</h5>
                                    <p class="text-muted mb-0 small">Customer support contact details</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="support_email" class="form-label">Support Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success-subtle border-0">
                                            <i class="bx bx-envelope text-success"></i>
                                        </span>
                                        <input type="email" class="form-control @error('support_email') is-invalid @enderror" 
                                            id="support_email" name="support_email" 
                                            placeholder="support@example.com" value="{{ old('support_email') }}">
                                    </div>
                                    @error('support_email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="support_phone" class="form-label">Support Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success-subtle border-0">
                                            <i class="bx bx-phone text-success"></i>
                                        </span>
                                        <input type="text" class="form-control @error('support_phone') is-invalid @enderror" 
                                            id="support_phone" name="support_phone" 
                                            placeholder="Enter Support Phone" value="{{ old('support_phone') }}">
                                    </div>
                                    @error('support_phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="business_hours" class="form-label">Business Hours</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success-subtle border-0 align-items-start pt-3">
                                            <i class="bx bx-time text-success"></i>
                                        </span>
                                        <textarea class="form-control @error('business_hours') is-invalid @enderror" 
                                            id="business_hours" name="business_hours" rows="2" 
                                            placeholder="e.g., Monday - Friday: 9:00 AM - 6:00 PM">{{ old('business_hours') }}</textarea>
                                    </div>
                                    @error('business_hours')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.general-settings.index') }}" class="btn btn-light btn-lg px-4">
                                        <i class="bx bx-arrow-back me-2"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg px-4">
                                        <i class="bx bx-save me-2"></i>
                                        Create Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

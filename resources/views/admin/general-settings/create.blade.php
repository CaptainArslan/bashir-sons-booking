@extends('admin.layouts.app')

@section('title', 'Create General Settings')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Content Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.general-settings.index') }}">General Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Settings</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-12">
            <form action="{{ route('admin.general-settings.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Create General Settings</h5>
                        
                        <!-- Company Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">Company Information</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name"
                                    name="company_name" placeholder="Enter Company Name" value="{{ old('company_name') }}" required>
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" placeholder="Enter Email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                    name="phone" placeholder="Enter Phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="alternate_phone" class="form-label">Alternate Phone</label>
                                <input type="text" class="form-control @error('alternate_phone') is-invalid @enderror" id="alternate_phone"
                                    name="alternate_phone" placeholder="Enter Alternate Phone" value="{{ old('alternate_phone') }}">
                                @error('alternate_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                    name="address" rows="2" placeholder="Enter Address" required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                                    name="city" placeholder="Enter City" value="{{ old('city') }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state"
                                    name="state" placeholder="Enter State" value="{{ old('state') }}" required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" id="country"
                                    name="country" placeholder="Enter Country" value="{{ old('country') }}" required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code"
                                    name="postal_code" placeholder="Enter Postal Code" value="{{ old('postal_code') }}">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="website_url" class="form-label">Website URL</label>
                                <input type="url" class="form-control @error('website_url') is-invalid @enderror" id="website_url"
                                    name="website_url" placeholder="Enter Website URL" value="{{ old('website_url') }}">
                                @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="tagline" class="form-label">Tagline</label>
                                <input type="text" class="form-control @error('tagline') is-invalid @enderror" id="tagline"
                                    name="tagline" placeholder="Enter Company Tagline" value="{{ old('tagline') }}">
                                @error('tagline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Media -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">Media</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="logo" class="form-label">Logo</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo"
                                    name="logo" accept="image/*" onchange="previewImage(this, 'logo-preview')">
                                <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF, WebP. Maximum size: 2MB</div>
                                <div id="logo-preview" class="mt-2" style="display: none;">
                                    <strong>Preview:</strong><br>
                                    <img id="logo-preview-img" src="" alt="Logo Preview" class="img-thumbnail mt-2" style="max-width: 200px; max-height: 100px;">
                                </div>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="favicon" class="form-label">Favicon</label>
                                <input type="file" class="form-control @error('favicon') is-invalid @enderror" id="favicon"
                                    name="favicon" accept="image/*" onchange="previewImage(this, 'favicon-preview')">
                                <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF, ICO, WebP. Maximum size: 1MB</div>
                                <div id="favicon-preview" class="mt-2" style="display: none;">
                                    <strong>Preview:</strong><br>
                                    <img id="favicon-preview-img" src="" alt="Favicon Preview" class="img-thumbnail mt-2" style="max-width: 100px; max-height: 100px;">
                                </div>
                                @error('favicon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">Social Media</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="facebook_url" class="form-label">Facebook URL</label>
                                <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" id="facebook_url"
                                    name="facebook_url" placeholder="Enter Facebook URL" value="{{ old('facebook_url') }}">
                                @error('facebook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="instagram_url" class="form-label">Instagram URL</label>
                                <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" id="instagram_url"
                                    name="instagram_url" placeholder="Enter Instagram URL" value="{{ old('instagram_url') }}">
                                @error('instagram_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                <input type="url" class="form-control @error('twitter_url') is-invalid @enderror" id="twitter_url"
                                    name="twitter_url" placeholder="Enter Twitter URL" value="{{ old('twitter_url') }}">
                                @error('twitter_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" id="linkedin_url"
                                    name="linkedin_url" placeholder="Enter LinkedIn URL" value="{{ old('linkedin_url') }}">
                                @error('linkedin_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="youtube_url" class="form-label">YouTube URL</label>
                                <input type="url" class="form-control @error('youtube_url') is-invalid @enderror" id="youtube_url"
                                    name="youtube_url" placeholder="Enter YouTube URL" value="{{ old('youtube_url') }}">
                                @error('youtube_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Support Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">Support Information</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="support_email" class="form-label">Support Email</label>
                                <input type="email" class="form-control @error('support_email') is-invalid @enderror" id="support_email"
                                    name="support_email" placeholder="Enter Support Email" value="{{ old('support_email') }}">
                                @error('support_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="support_phone" class="form-label">Support Phone</label>
                                <input type="text" class="form-control @error('support_phone') is-invalid @enderror" id="support_phone"
                                    name="support_phone" placeholder="Enter Support Phone" value="{{ old('support_phone') }}">
                                @error('support_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="business_hours" class="form-label">Business Hours</label>
                                <textarea class="form-control @error('business_hours') is-invalid @enderror" id="business_hours"
                                    name="business_hours" rows="2" placeholder="Enter Business Hours">{{ old('business_hours') }}</textarea>
                                @error('business_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.general-settings.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Settings
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

@section('scripts')
<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById(previewId + '-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endsection

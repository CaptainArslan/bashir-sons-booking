@extends('admin.layouts.app')

@section('title', 'Create Discount')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create Discount</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.discounts.index') }}">Discounts</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="bx bx-discount me-2"></i>
                            Create New Discount
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.discounts.store') }}" id="discount-form">
                            @csrf

                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Basic Information
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label fw-semibold">Discount Title <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('title') is-invalid @enderror" 
                                               id="title" 
                                               name="title" 
                                               value="{{ old('title') }}" 
                                               placeholder="e.g., Weekend Special, Early Bird Offer" 
                                               required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="route_id" class="form-label fw-semibold">Route <span class="text-danger">*</span></label>
                                        <select class="form-select @error('route_id') is-invalid @enderror" 
                                                id="route_id" 
                                                name="route_id" 
                                                required>
                                            <option value="">Choose a route...</option>
                                            @foreach($routes as $route)
                                                <option value="{{ $route->id }}" {{ old('route_id') == $route->id ? 'selected' : '' }}>
                                                    {{ $route->name }} ({{ $route->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('route_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Discount Details -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-calculator me-1"></i>
                                        Discount Details
                                    </h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="discount_type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('discount_type') is-invalid @enderror" 
                                                id="discount_type" 
                                                name="discount_type" 
                                                required>
                                            <option value="">Select type</option>
                                            <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                            <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                        @error('discount_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="value" class="form-label fw-semibold">Value <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control @error('value') is-invalid @enderror" 
                                                   id="value" 
                                                   name="value" 
                                                   value="{{ old('value') }}" 
                                                   step="0.01" 
                                                   min="0" 
                                                   placeholder="0.00" 
                                                   required>
                                            <span class="input-group-text" id="value-suffix">₹</span>
                                        </div>
                                        @error('value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Platforms</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="is_android" name="is_android" value="1" {{ old('is_android') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_android">
                                                    <i class="bx bxl-android me-1"></i>Android
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="is_ios" name="is_ios" value="1" {{ old('is_ios') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_ios">
                                                    <i class="bx bxl-apple me-1"></i>iOS
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="is_web" name="is_web" value="1" {{ old('is_web') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_web">
                                                    <i class="bx bx-globe me-1"></i>Web
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="is_counter" name="is_counter" value="1" {{ old('is_counter') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_counter">
                                                    <i class="bx bx-store me-1"></i>Counter
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Validity Period -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-calendar me-1"></i>
                                        Validity Period
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="starts_at" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" 
                                               class="form-control @error('starts_at') is-invalid @enderror" 
                                               id="starts_at" 
                                               name="starts_at" 
                                               value="{{ old('starts_at') }}" 
                                               required>
                                        @error('starts_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="ends_at" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                        <input type="date" 
                                               class="form-control @error('ends_at') is-invalid @enderror" 
                                               id="ends_at" 
                                               name="ends_at" 
                                               value="{{ old('ends_at') }}" 
                                               required>
                                        @error('ends_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="start_time" class="form-label fw-semibold">Start Time</label>
                                        <input type="time" 
                                               class="form-control @error('start_time') is-invalid @enderror" 
                                               id="start_time" 
                                               name="start_time" 
                                               value="{{ old('start_time') }}">
                                        @error('start_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="end_time" class="form-label fw-semibold">End Time</label>
                                        <input type="time" 
                                               class="form-control @error('end_time') is-invalid @enderror" 
                                               id="end_time" 
                                               name="end_time" 
                                               value="{{ old('end_time') }}">
                                        @error('end_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-toggle-right me-1"></i>
                                        Status
                                    </h6>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_active">
                                            Active Discount
                                        </label>
                                    </div>
                                    <div class="form-text text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Enable this discount immediately after creation
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.discounts.index') }}" class="btn btn-light">
                                            <i class="bx bx-arrow-back me-1"></i>
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i>
                                            Create Discount
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            Quick Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info alert-sm">
                            <h6 class="alert-heading">Discount Types</h6>
                            <ul class="mb-0 small">
                                <li><strong>Fixed:</strong> Flat amount discount (e.g., ₹50 off)</li>
                                <li><strong>Percentage:</strong> Percentage-based discount (e.g., 15% off)</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning alert-sm">
                            <h6 class="alert-heading">Platform Selection</h6>
                            <p class="mb-0 small">Choose which platforms this discount will be available on. You can select multiple platforms.</p>
                        </div>
                        
                        <div class="alert alert-success alert-sm">
                            <h6 class="alert-heading">Time Restrictions</h6>
                            <p class="mb-0 small">Optional time restrictions allow you to limit when the discount can be used during the day.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    $('#starts_at').attr('min', today);
    $('#ends_at').attr('min', today);

    // Update end date minimum when start date changes
    $('#starts_at').on('change', function() {
        $('#ends_at').attr('min', $(this).val());
    });

    // Update end time minimum when start time changes
    $('#start_time').on('change', function() {
        $('#end_time').attr('min', $(this).val());
    });

    // Dynamic value suffix based on discount type
    $('#discount_type').on('change', function() {
        const type = $(this).val();
        const suffix = $('#value-suffix');
        
        if (type === 'percentage') {
            suffix.text('%');
            $('#value').attr('max', '100');
        } else {
            suffix.text('₹');
            $('#value').removeAttr('max');
        }
    });

    // Form validation enhancement
    $('#discount-form').on('submit', function(e) {
        const discountType = $('#discount_type').val();
        const value = parseFloat($('#value').val());
        
        if (discountType === 'percentage' && value > 100) {
            e.preventDefault();
            toastr.error('Percentage discount cannot exceed 100%');
            return false;
        }
        
        if (value <= 0) {
            e.preventDefault();
            toastr.error('Discount value must be greater than 0');
            return false;
        }
    });

    // Auto-select all platforms by default
    $('input[name="is_android"], input[name="is_ios"], input[name="is_web"], input[name="is_counter"]').prop('checked', true);

    // Platform selection validation
    $('input[name^="is_"]').on('change', function() {
        const checkedPlatforms = $('input[name^="is_"]:checked').length;
        if (checkedPlatforms === 0) {
            toastr.warning('Please select at least one platform');
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush

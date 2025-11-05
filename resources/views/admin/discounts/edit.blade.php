@extends('admin.layouts.app')

@section('title', 'Edit Discount')

@section('styles')
<style>
    .discount-card {
        border-left: 4px solid #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header-info h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .form-control, .form-select {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .row {
        margin-bottom: 0.5rem;
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
    }
    
    .info-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-left: 4px solid #2196f3;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    
    .info-box p {
        margin: 0;
        font-size: 0.85rem;
        color: #1976d2;
    }
    
    .section-divider {
        border-top: 1px solid #e9ecef;
        margin: 1rem 0;
        padding-top: 1rem;
    }
    
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Discount Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.discounts.index') }}">Discounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Discount</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card discount-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Discount</h5>
                </div>
                
                <form action="{{ route('admin.discounts.update', $discount) }}" method="POST" class="row g-3" id="discount-form">
                    @csrf
                    @method('PUT')
                    
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

                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Update discount information including type, value, validity period, and applicable platforms. Changes will affect all future bookings using this discount.</p>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-info-circle me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="title" class="form-label">
                                    Discount Title 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title"
                                       name="title" 
                                       placeholder="e.g., Weekend Special, Early Bird Offer" 
                                       value="{{ old('title', $discount->title) }}" 
                                       required
                                       autofocus>
                                @error('title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="route_id" class="form-label">
                                    Route 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('route_id') is-invalid @enderror" 
                                        id="route_id" 
                                        name="route_id" 
                                        required>
                                    <option value="">Select Route</option>
                                    @foreach($routes as $route)
                                        <option value="{{ $route->id }}" {{ old('route_id', $discount->route_id) == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('route_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Discount Details -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-calculator me-1"></i>Discount Details
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="discount_type" class="form-label">
                                    Discount Type 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('discount_type') is-invalid @enderror" 
                                        id="discount_type" 
                                        name="discount_type" 
                                        required>
                                    <option value="">Select Discount Type</option>
                                    <option value="fixed" {{ old('discount_type', $discount->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                    <option value="percentage" {{ old('discount_type', $discount->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="value" class="form-label">
                                    Discount Value 
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('value') is-invalid @enderror" 
                                           id="value"
                                           name="value" 
                                           placeholder="0.00" 
                                           value="{{ old('value', $discount->value) }}" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                    <span class="input-group-text" id="value-suffix">PKR</span>
                                </div>
                                @error('value')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label">Platforms</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_android" name="is_android" value="1" {{ old('is_android', $discount->is_android) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_android">
                                            <i class="bx bxl-android me-1"></i>Android
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_ios" name="is_ios" value="1" {{ old('is_ios', $discount->is_ios) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_ios">
                                            <i class="bx bxl-apple me-1"></i>iOS
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_web" name="is_web" value="1" {{ old('is_web', $discount->is_web) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_web">
                                            <i class="bx bx-globe me-1"></i>Web
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_counter" name="is_counter" value="1" {{ old('is_counter', $discount->is_counter) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_counter">
                                            <i class="bx bx-store me-1"></i>Counter
                                        </label>
                                    </div>
                                </div>
                                @error('platforms')
                                    <div class="text-danger d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Validity Period -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-calendar me-1"></i>Validity Period
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <label for="starts_at" class="form-label">
                                    Start Date 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('starts_at') is-invalid @enderror" 
                                       id="starts_at"
                                       name="starts_at" 
                                       value="{{ old('starts_at', $discount->starts_at ? $discount->starts_at->format('Y-m-d') : '') }}" 
                                       required>
                                @error('starts_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="ends_at" class="form-label">
                                    End Date 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('ends_at') is-invalid @enderror" 
                                       id="ends_at"
                                       name="ends_at" 
                                       value="{{ old('ends_at', $discount->ends_at ? $discount->ends_at->format('Y-m-d') : '') }}" 
                                       required>
                                @error('ends_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" 
                                       class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time"
                                       name="start_time" 
                                       value="{{ old('start_time', $discount->start_time ? $discount->start_time->format('H:i') : '') }}">
                                @error('start_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" 
                                       class="form-control @error('end_time') is-invalid @enderror" 
                                       id="end_time"
                                       name="end_time" 
                                       value="{{ old('end_time', $discount->end_time ? $discount->end_time->format('H:i') : '') }}">
                                @error('end_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-toggle-right me-1"></i>Status
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $discount->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Discount
                                    </label>
                                </div>
                                <div class="form-text">Check this box to make the discount active</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.discounts.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Update Discount
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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
            suffix.text('PKR');
            $('#value').removeAttr('max');
        }
    }).trigger('change'); // Trigger on load to set initial suffix

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

    // Platform selection validation
    $('input[name^="is_"]').on('change', function() {
        const checkedPlatforms = $('input[name^="is_"]:checked').length;
        if (checkedPlatforms === 0) {
            toastr.warning('Please select at least one platform');
        }
    });
});
</script>
@endsection

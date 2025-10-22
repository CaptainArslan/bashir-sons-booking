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
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Discount Information</h4>
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

                        <form method="POST" action="{{ route('admin.discounts.store') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Discount Title <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('title') is-invalid @enderror" 
                                               id="title" 
                                               name="title" 
                                               value="{{ old('title') }}" 
                                               placeholder="Enter discount title" 
                                               required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="route_id" class="form-label">Route <span class="text-danger">*</span></label>
                                        <select class="form-select @error('route_id') is-invalid @enderror" 
                                                id="route_id" 
                                                name="route_id" 
                                                required>
                                            <option value="">Select a route</option>
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

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('discount_type') is-invalid @enderror" 
                                                id="discount_type" 
                                                name="discount_type" 
                                                required>
                                            <option value="">Select discount type</option>
                                            <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Flat Amount</option>
                                            <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                        @error('discount_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control @error('value') is-invalid @enderror" 
                                               id="value" 
                                               name="value" 
                                               value="{{ old('value') }}" 
                                               step="0.01" 
                                               min="0" 
                                               placeholder="Enter discount value" 
                                               required>
                                        @error('value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Platforms</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_android" name="is_android" value="1" {{ old('is_android') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_android">Android</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_ios" name="is_ios" value="1" {{ old('is_ios') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_ios">iOS</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_web" name="is_web" value="1" {{ old('is_web') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_web">Web</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_counter" name="is_counter" value="1" {{ old('is_counter') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_counter">Counter</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="starts_at" class="form-label">Start Date <span class="text-danger">*</span></label>
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ends_at" class="form-label">End Date <span class="text-danger">*</span></label>
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
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_time" class="form-label">Start Time</label>
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_time" class="form-label">End Time</label>
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

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active Discount
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Check this box to make the discount active immediately after creation.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>
                                            Back to List
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
});
</script>
@endpush

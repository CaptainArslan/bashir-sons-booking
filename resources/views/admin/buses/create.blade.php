@extends('admin.layouts.app')

@section('title', 'Create Bus')

@section('styles')
<style>
    .bus-card {
        border-left: 4px solid #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header-info {
        /* background: #495057; */
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 4px 4px 0 0;
    }
    
    .card-header-info h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.15rem;
        font-size: 0.85rem;
    }
    
    .form-control, .form-select {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 4px;
    }
    
    .card-body {
        padding: 0.75rem !important;
    }
    
    .row {
        margin-bottom: 0.35rem;
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
    }
    
    .info-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-left: 3px solid #6c757d;
        padding: 0.4rem 0.6rem;
        border-radius: 4px;
        margin-bottom: 0.75rem;
    }
    
    .info-box p {
        margin: 0;
        font-size: 0.8rem;
        color: #495057;
        line-height: 1.4;
    }
    
    .section-divider {
        border-top: 1px solid #e9ecef;
        margin: 0.5rem 0;
        padding-top: 0.5rem;
    }
    
    .section-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        padding-bottom: 0.35rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .form-text {
        font-size: 0.7rem;
        color: #6c757d;
        margin-top: 0.15rem;
    }
    
    .facilities-container {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 0.4rem;
        background: #fff;
    }
    
    .facility-item {
        padding: 0.15rem 0.4rem;
        margin: 0;
    }
    
    .facility-item .form-check-label {
        font-size: 0.875rem;
        color: #495057;
        cursor: pointer;
    }
    
    .facility-item .form-check-input:checked ~ .form-check-label {
        color: #212529;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.buses.index') }}">Buses</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Bus</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card bus-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New Bus</h5>
                </div>
                
                <form action="{{ route('admin.buses.store') }}" method="POST" class="row g-3">
                    @csrf
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Enter complete bus information including registration details, type, layout, and facilities. This information helps customers identify and book buses.</p>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-bus me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Bus Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name"
                                       name="name" 
                                       placeholder="Enter Bus Name" 
                                       value="{{ old('name') }}" 
                                       required
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">
                                    Registration Number
                                </label>
                                <input type="text" 
                                       class="form-control @error('registration_number') is-invalid @enderror" 
                                       id="registration_number"
                                       name="registration_number" 
                                       placeholder="Enter Registration Number (e.g., ABC-123) - Optional" 
                                       value="{{ old('registration_number') }}" 
                                       style="text-transform: uppercase;">
                                <div class="form-text">Enter in format: ABC-123 (will be converted to uppercase). This field is optional.</div>
                                @error('registration_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="model" class="form-label">
                                    Model 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('model') is-invalid @enderror" 
                                       id="model"
                                       name="model" 
                                       placeholder="Enter Bus Model" 
                                       value="{{ old('model') }}" 
                                       required>
                                @error('model')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="color" class="form-label">
                                    Color 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('color') is-invalid @enderror" 
                                       id="color"
                                       name="color" 
                                       placeholder="Enter Bus Color" 
                                       value="{{ old('color') }}" 
                                       required>
                                @error('color')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description"
                                          name="description" 
                                          rows="3" 
                                          placeholder="Enter bus description (optional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Bus Configuration -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-cog me-1"></i>Bus Configuration
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="bus_type_id" class="form-label">
                                    Bus Type 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('bus_type_id') is-invalid @enderror" 
                                        id="bus_type_id" 
                                        name="bus_type_id" 
                                        required>
                                    <option value="">Select Bus Type</option>
                                    @foreach ($busTypes as $busType)
                                        <option value="{{ $busType->id }}" {{ old('bus_type_id') == $busType->id ? 'selected' : '' }}>
                                            {{ $busType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bus_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="total_seats" class="form-label">
                                    Total Seats 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('total_seats') is-invalid @enderror" 
                                       id="total_seats" 
                                       name="total_seats" 
                                       placeholder="Enter total number of seats"
                                       value="{{ old('total_seats') }}" 
                                       min="1"
                                       required>
                                @error('total_seats')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="status" class="form-label">
                                    Status 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="">Select Status</option>
                                    @foreach (\App\Enums\BusEnum::cases() as $status)
                                        <option value="{{ $status->value }}" {{ old('status') == $status->value ? 'selected' : '' }}>
                                            {{ $status->getName() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Facilities Selection -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-star me-1"></i>Facilities
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="facilities-container">
                                    <div class="row g-2">
                                        @foreach ($facilities as $facility)
                                            <div class="col-md-3 col-sm-4 col-6">
                                                <div class="form-check facility-item">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="facilities[]" 
                                                           value="{{ $facility->id }}" 
                                                           id="facility_{{ $facility->id }}"
                                                           {{ in_array($facility->id, old('facilities', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="facility_{{ $facility->id }}">
                                                        <i class="{{ $facility->icon }} me-1"></i>{{ $facility->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('facilities')
                                    <div class="text-danger d-block mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.buses.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.buses.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Create Bus
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
        // Initialize Select2 for select boxes
        $('#bus_type_id').select2({
            width: 'resolve'
        });
        // Bus layout removed - using total_seats directly
        $('#status').select2({
            width: 'resolve'
        });
    });
</script>
@endsection
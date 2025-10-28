@extends('admin.layouts.app')

@section('title', 'Edit Bus')

@section('styles')
<style>
    .bus-card {
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
    
    .bus-info-card {
        border-left: 3px solid #0dcaf0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .stats-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
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
    
    .facility-group {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 3px solid #0dcaf0;
        transition: all 0.3s ease;
    }
    
    .facility-group:hover {
        background: #e9ecef;
        transform: translateX(2px);
    }
    
    .facility-group .form-check-input:checked ~ .form-check-label {
        color: #0d6efd;
        font-weight: 600;
    }
    
    .facility-group .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
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
                    <li class="breadcrumb-item active" aria-current="page">Edit Bus</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card bus-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Bus: {{ $bus->name }}</h5>
                </div>
                
                <form action="{{ route('admin.buses.update', $bus->id) }}" method="POST" class="row g-3">
                    @method('PUT')
                    @csrf
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Updating bus information will affect all routes and bookings using this bus. Please review carefully before saving changes.</p>
                        </div>
                        
                        <!-- Bus Information Card -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card bus-info-card">
                                    <div class="card-body" style="padding: 0.75rem;">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Bus ID:</strong> 
                                                    <span class="badge bg-secondary">{{ $bus->id }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Registration:</strong> 
                                                    <span class="badge bg-info">{{ $bus->registration_number }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Current Status:</strong> 
                                                    <span class="badge bg-{{ $bus->status->getStatusColor($bus->status->value) }} stats-badge">
                                                        {{ $bus->status->getName() }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Seats:</strong> 
                                                    <span class="badge bg-success">{{ $bus->busLayout->total_seats ?? 'N/A' }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Type:</strong> 
                                                    <span class="badge bg-primary">{{ $bus->busType->name ?? 'N/A' }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Created:</strong> 
                                                    {{ $bus->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                       value="{{ old('name', $bus->name) }}" 
                                       required
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">
                                    Registration Number 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('registration_number') is-invalid @enderror" 
                                       id="registration_number"
                                       name="registration_number" 
                                       placeholder="Enter Registration Number (e.g., ABC-123)" 
                                       value="{{ old('registration_number', $bus->registration_number) }}" 
                                       style="text-transform: uppercase;" 
                                       required>
                                <div class="form-text">Enter in format: ABC-123 (will be converted to uppercase)</div>
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
                                       value="{{ old('model', $bus->model) }}" 
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
                                       value="{{ old('color', $bus->color) }}" 
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
                                          placeholder="Enter bus description (optional)">{{ old('description', $bus->description) }}</textarea>
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
                                        <option value="{{ $busType->id }}" 
                                            {{ old('bus_type_id', $bus->bus_type_id) == $busType->id ? 'selected' : '' }}>
                                            {{ $busType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bus_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="bus_layout_id" class="form-label">
                                    Bus Layout 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('bus_layout_id') is-invalid @enderror" 
                                        id="bus_layout_id" 
                                        name="bus_layout_id" 
                                        required>
                                    <option value="">Select Bus Layout</option>
                                    @foreach ($busLayouts as $busLayout)
                                        <option value="{{ $busLayout->id }}" 
                                            {{ old('bus_layout_id', $bus->bus_layout_id) == $busLayout->id ? 'selected' : '' }}>
                                            {{ $busLayout->name }} ({{ $busLayout->total_seats }} seats)
                                        </option>
                                    @endforeach
                                </select>
                                @error('bus_layout_id')
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
                                        <option value="{{ $status->value }}" 
                                            {{ old('status', $bus->status->value) == $status->value ? 'selected' : '' }}>
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
                                <div class="row">
                                    @foreach ($facilities as $facility)
                                        <div class="col-md-4 mb-2">
                                            <div class="facility-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="facilities[]" 
                                                           value="{{ $facility->id }}" 
                                                           id="facility_{{ $facility->id }}"
                                                           {{ in_array($facility->id, old('facilities', $bus->facilities->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="facility_{{ $facility->id }}">
                                                        <i class="{{ $facility->icon }} me-2"></i>{{ $facility->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('facilities')
                                    <div class="text-danger d-block">{{ $message }}</div>
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
                                    <i class="bx bx-save me-1"></i>Update Bus
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
        $('#bus_layout_id').select2({
            width: 'resolve'
        });
        $('#status').select2({
            width: 'resolve'
        });
    });
</script>
@endsection

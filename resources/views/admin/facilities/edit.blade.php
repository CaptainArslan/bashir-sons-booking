@extends('admin.layouts.app')

@section('title', 'Edit Facility')

@section('styles')
<style>
    .facility-card {
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
    
    .facility-info-card {
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
    
    .icon-preview-box {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
        border-left: 4px solid #ffc107;
        padding: 0.75rem;
        border-radius: 6px;
        margin-top: 0.5rem;
    }
    
    .icon-preview-box p {
        margin: 0;
        font-size: 0.85rem;
        color: #856404;
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
        <div class="breadcrumb-title pe-3">Bus Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.facilities.index') }}">Facilities</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Facility</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card facility-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Facility: {{ $facility->name }}</h5>
                </div>
                
                <form action="{{ route('admin.facilities.update', $facility->id) }}" method="POST" class="row g-3">
                    @method('PUT')
                    @csrf
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Updating facility information will affect all buses using this facility. Please review carefully before saving changes.</p>
                        </div>
                        
                        <!-- Facility Information Card -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card facility-info-card">
                                    <div class="card-body" style="padding: 0.75rem;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Facility ID:</strong> 
                                                    <span class="badge bg-secondary">{{ $facility->id }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Current Status:</strong> 
                                                    <span class="badge bg-{{ $facility->status->getStatusColor($facility->status->value) }} stats-badge">
                                                        {{ $facility->status->getName() }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Current Icon:</strong> 
                                                    <span><i class="{{ $facility->icon }}"></i></span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Created:</strong> 
                                                    {{ $facility->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-building me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Facility Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name"
                                       name="name" 
                                       placeholder="Enter Facility Name" 
                                       value="{{ old('name', $facility->name) }}" 
                                       required
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
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
                                    @foreach (\App\Enums\FacilityEnum::cases() as $status)
                                        <option value="{{ $status->value }}" 
                                            {{ old('status', $facility->status->value) == $status->value ? 'selected' : '' }}>
                                            {{ $status->getName() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
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
                                          placeholder="Enter facility description (optional)">{{ old('description', $facility->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Icon Configuration -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-palette me-1"></i>Icon Configuration
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <label for="icon" class="form-label">
                                    Icon Class 
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control @error('icon') is-invalid @enderror" 
                                           id="icon"
                                           name="icon" 
                                           placeholder="e.g., bx bx-wifi, bx bx-air-conditioning" 
                                           value="{{ old('icon', $facility->icon) }}" 
                                           required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="showIconPreview()">
                                        <i class="bx bx-preview me-1"></i>Preview
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Use Boxicons classes (e.g., bx bx-wifi, bx bx-air-conditioning, bx bx-tv, bx bx-music)
                                </div>
                                @error('icon')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Icon Preview -->
                        <div class="row">
                            <div class="col-12">
                                <div id="icon-preview" class="icon-preview-box">
                                    <p>
                                        <i class="bx bx-eye me-1"></i>
                                        <strong>Icon Preview:</strong> 
                                        <span id="preview-icon"><i class="{{ $facility->icon }} me-2" style="font-size: 1.5rem;"></i><code>{{ $facility->icon }}</code></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.facilities.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.facilities.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Update Facility
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
function showIconPreview() {
    const iconInput = document.getElementById('icon');
    const iconValue = iconInput.value.trim();
    const previewDiv = document.getElementById('icon-preview');
    const previewIcon = document.getElementById('preview-icon');
    
    if (iconValue) {
        previewIcon.innerHTML = `<i class="${iconValue} me-2" style="font-size: 1.5rem;"></i><code>${iconValue}</code>`;
        previewDiv.style.display = 'block';
    } else {
        previewDiv.style.display = 'none';
    }
}

// Auto-preview on input change
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon');
    iconInput.addEventListener('input', showIconPreview);
});
</script>
@endsection

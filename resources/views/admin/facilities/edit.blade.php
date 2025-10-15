@extends('admin.layouts.app')

@section('title', 'Edit Facility')

@section('styles')
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
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.facilities.update', $facility->id) }}" method="POST" class="row g-3">
                @method('PUT')
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit Facility</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Facility Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter Facility Name" value="{{ old('name', $facility->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @if (old('status', $facility->status->value) == $status) selected @endif>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3" placeholder="Enter facility description">{{ old('description', $facility->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="icon" class="form-label">Icon Class <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon"
                                        name="icon" placeholder="e.g., bx bx-wifi, bx bx-air-conditioning" value="{{ old('icon', $facility->icon) }}" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="showIconPreview()">
                                        <i class="bx bx-preview"></i> Preview
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Use Boxicons classes (e.g., bx bx-wifi, bx bx-air-conditioning, bx bx-tv, bx bx-music)
                                </div>
                                <div id="icon-preview" class="mt-2">
                                    <strong>Preview:</strong> <span id="preview-icon"><i class="{{ $facility->icon }} me-2"></i>{{ $facility->icon }}</span>
                                </div>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.facilities.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Update Facility
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
function showIconPreview() {
    const iconInput = document.getElementById('icon');
    const iconValue = iconInput.value.trim();
    const previewDiv = document.getElementById('icon-preview');
    const previewIcon = document.getElementById('preview-icon');
    
    if (iconValue) {
        previewIcon.innerHTML = `<i class="${iconValue} me-2"></i>${iconValue}`;
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

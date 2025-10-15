@extends('admin.layouts.app')

@section('title', 'Create Bus Layout')

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
                    <li class="breadcrumb-item"><a href="{{ route('admin.bus-layouts.index') }}">Bus Layouts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Bus Layout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.bus-layouts.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Create Bus Layout</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Layout Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter Layout Name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3" placeholder="Enter layout description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="total_rows" class="form-label">Total Rows <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('total_rows') is-invalid @enderror" id="total_rows"
                                    name="total_rows" placeholder="Enter total rows" value="{{ old('total_rows') }}" min="1" max="50" required>
                                @error('total_rows')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="total_columns" class="form-label">Total Columns <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('total_columns') is-invalid @enderror" id="total_columns"
                                    name="total_columns" placeholder="Enter total columns" value="{{ old('total_columns') }}" min="1" max="10" required>
                                @error('total_columns')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Note:</strong> Total seats will be calculated automatically (Rows × Columns)
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.bus-layouts.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Bus Layout
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
document.addEventListener('DOMContentLoaded', function() {
    const totalRowsInput = document.getElementById('total_rows');
    const totalColumnsInput = document.getElementById('total_columns');
    
    function updateSeatCalculation() {
        const rows = parseInt(totalRowsInput.value) || 0;
        const columns = parseInt(totalColumnsInput.value) || 0;
        const totalSeats = rows * columns;
        
        // Update the info alert
        const alertDiv = document.querySelector('.alert-info');
        if (alertDiv && totalSeats > 0) {
            alertDiv.innerHTML = `
                <i class="bx bx-info-circle me-2"></i>
                <strong>Note:</strong> Total seats will be calculated automatically: <strong>${totalSeats} seats</strong> (${rows} rows × ${columns} columns)
            `;
        }
    }
    
    totalRowsInput.addEventListener('input', updateSeatCalculation);
    totalColumnsInput.addEventListener('input', updateSeatCalculation);
    
    // Initial calculation
    updateSeatCalculation();
});
</script>
@endsection

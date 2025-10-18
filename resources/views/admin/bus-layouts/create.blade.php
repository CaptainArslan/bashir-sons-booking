@extends('admin.layouts.app')

@section('title', 'Create Bus Layout')

@section('styles')
<style>
    .bus-layout-card {
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
    
    .calculation-box {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-left: 4px solid #28a745;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    
    .calculation-box p {
        margin: 0;
        font-size: 0.85rem;
        color: #155724;
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
    
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">
                        <i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bus-layouts.index') }}">Bus Layouts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Bus Layout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card bus-layout-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New Bus Layout</h5>
                </div>
                
                <form action="{{ route('admin.bus-layouts.store') }}" method="POST" class="row g-3">
                    @csrf
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Enter the layout name and seat configuration. The total number of seats will be calculated automatically based on rows and columns.</p>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-building me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Layout Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name"
                                       name="name" 
                                       placeholder="Enter Layout Name" 
                                       value="{{ old('name') }}" 
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
                                    @foreach (\App\Enums\BusLayoutEnum::cases() as $status)
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
                        
                        <div class="row">
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description"
                                          name="description" 
                                          rows="3" 
                                          placeholder="Enter layout description (optional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Seat Configuration -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-chair me-1"></i>Seat Configuration
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="total_rows" class="form-label">
                                    Total Rows 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('total_rows') is-invalid @enderror" 
                                       id="total_rows"
                                       name="total_rows" 
                                       placeholder="Enter total rows" 
                                       value="{{ old('total_rows') }}" 
                                       min="1" 
                                       max="50" 
                                       required>
                                <div class="form-text">Enter number of rows (1-50)</div>
                                @error('total_rows')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="total_columns" class="form-label">
                                    Total Columns 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('total_columns') is-invalid @enderror" 
                                       id="total_columns"
                                       name="total_columns" 
                                       placeholder="Enter total columns" 
                                       value="{{ old('total_columns') }}" 
                                       min="1" 
                                       max="10" 
                                       required>
                                <div class="form-text">Enter number of columns (1-10)</div>
                                @error('total_columns')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Seat Calculation Display -->
                        <div class="row">
                            <div class="col-12">
                                <div class="calculation-box" id="seat-calculation">
                                    <p>
                                        <i class="bx bx-calculator me-1"></i>
                                        <strong>Total Seats:</strong> <span id="total-seats">0 seats</span>
                                        <span id="calculation-detail" class="ms-2"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.bus-layouts.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.bus-layouts.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Create Bus Layout
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
document.addEventListener('DOMContentLoaded', function() {
    const totalRowsInput = document.getElementById('total_rows');
    const totalColumnsInput = document.getElementById('total_columns');
    const totalSeatsSpan = document.getElementById('total-seats');
    const calculationDetailSpan = document.getElementById('calculation-detail');
    
    function updateSeatCalculation() {
        const rows = parseInt(totalRowsInput.value) || 0;
        const columns = parseInt(totalColumnsInput.value) || 0;
        const totalSeats = rows * columns;
        
        if (totalSeats > 0) {
            totalSeatsSpan.textContent = `${totalSeats} seats`;
            calculationDetailSpan.textContent = `(${rows} rows × ${columns} columns)`;
            calculationDetailSpan.style.display = 'inline';
        } else {
            totalSeatsSpan.textContent = '0 seats';
            calculationDetailSpan.textContent = '';
            calculationDetailSpan.style.display = 'none';
        }
    }
    
    // Event listeners
    totalRowsInput.addEventListener('input', updateSeatCalculation);
    totalColumnsInput.addEventListener('input', updateSeatCalculation);
    
    // Initial calculation
    updateSeatCalculation();
});
</script>
@endsection

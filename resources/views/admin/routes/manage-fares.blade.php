@extends('admin.layouts.app')

@section('title', 'Manage Fares - ' . $route->name)

@section('styles')
<style>
    .fare-card {
        border-left: 4px solid #28a745;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header-success h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .route-info-card {
        border-left: 3px solid #17a2b8;
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
    
    .fare-table {
        font-size: 0.875rem;
    }
    
    .fare-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-top: none;
    }
    
    .form-control, .form-select {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
    }
    
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .route-path {
        font-weight: 600;
        color: #495057;
    }
    
    .distance-info {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .fare-input {
        min-width: 100px;
    }
    
    .discount-input {
        min-width: 80px;
    }
    
    .status-badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Route Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manage Fares</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-12">
            <div class="card fare-card">
                <div class="card-header-success">
                    <h5><i class="bx bx-money me-2"></i>Manage Fares - {{ $route->name }}</h5>
                </div>
                
                <div class="card-body">
                    <!-- Route Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card route-info-card">
                                <div class="card-body" style="padding: 0.75rem;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Route Code:</strong> 
                                                <span class="badge bg-secondary">{{ $route->code }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Direction:</strong> 
                                                <span class="badge bg-{{ $route->direction === 'forward' ? 'success' : 'warning' }} stats-badge">
                                                    {{ ucfirst($route->direction) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Total Stops:</strong> 
                                                <span class="badge bg-info stats-badge">{{ $stops->count() }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Currency:</strong> 
                                                <span class="badge bg-primary stats-badge">{{ $route->base_currency }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="info-box">
                        <p><i class="bx bx-info-circle me-1"></i><strong>Instructions:</strong> Set fares for all possible combinations of stops on this route. The system will automatically calculate final fares based on discounts. Existing fares will be updated, and new combinations will be created.</p>
                    </div>

                    <!-- Route Path Visualization -->
                    <div class="section-title">
                        <i class="bx bx-map me-1"></i>Route Path
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center flex-wrap">
                                @foreach($stops as $index => $stop)
                                    <div class="d-flex align-items-center">
                                        <div class="text-center">
                                            <div class="badge bg-primary mb-1">{{ $stop->sequence }}</div>
                                            <div class="small text-muted">{{ $stop->terminal->name }}</div>
                                            <div class="small text-muted">{{ $stop->terminal->city->name }}</div>
                                        </div>
                                        @if($index < $stops->count() - 1)
                                            <i class="bx bx-right-arrow-alt mx-2 text-muted"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Fare Management Form -->
                    <form action="{{ route('admin.routes.fares.store', $route->id) }}" method="POST" id="faresForm">
                        @csrf
                        
                        <div class="section-title">
                            <i class="bx bx-calculator me-1"></i>Fare Configuration
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered fare-table">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Route Segment</th>
                                        <th width="10%">Distance</th>
                                        <th width="15%">Base Fare</th>
                                        <th width="10%">Discount Type</th>
                                        <th width="10%">Discount Value</th>
                                        <th width="15%">Final Fare</th>
                                        <th width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stopCombinations as $index => $combination)
                                        @php
                                            $fareKey = $combination['from_terminal_id'] . '-' . $combination['to_terminal_id'];
                                            $existingFare = $existingFares->get($fareKey);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="route-path">
                                                    {{ $combination['from_terminal']->name }} → {{ $combination['to_terminal']->name }}
                                                </div>
                                                <div class="distance-info">
                                                    {{ $combination['from_terminal']->city->name }} → {{ $combination['to_terminal']->city->name }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $combination['distance'] }} km
                                                </span>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ $route->base_currency }}</span>
                                                    <input type="number" 
                                                           class="form-control fare-input base-fare" 
                                                           name="fares[{{ $index }}][base_fare]" 
                                                           value="{{ old('fares.' . $index . '.base_fare', $existingFare?->base_fare ?? '') }}"
                                                           step="0.01" 
                                                           min="0" 
                                                           required>
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-select discount-type" name="fares[{{ $index }}][discount_type]">
                                                    <option value="">None</option>
                                                    <option value="flat" {{ old('fares.' . $index . '.discount_type', $existingFare?->discount_type?->value) == 'flat' ? 'selected' : '' }}>Flat</option>
                                                    <option value="percent" {{ old('fares.' . $index . '.discount_type', $existingFare?->discount_type?->value) == 'percent' ? 'selected' : '' }}>Percent</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control discount-input discount-value" 
                                                       name="fares[{{ $index }}][discount_value]" 
                                                       value="{{ old('fares.' . $index . '.discount_value', $existingFare?->discount_value ?? '') }}"
                                                       step="0.01" 
                                                       min="0">
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ $route->base_currency }}</span>
                                                    <input type="number" 
                                                           class="form-control fare-input final-fare" 
                                                           name="fares[{{ $index }}][final_fare]" 
                                                           readonly>
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-select" name="fares[{{ $index }}][status]">
                                                    <option value="active" {{ old('fares.' . $index . '.status', $existingFare?->status?->value ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('fares.' . $index . '.status', $existingFare?->status?->value ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <!-- Hidden fields for terminal IDs -->
                                        <input type="hidden" name="fares[{{ $index }}][from_terminal_id]" value="{{ $combination['from_terminal_id'] }}">
                                        <input type="hidden" name="fares[{{ $index }}][to_terminal_id]" value="{{ $combination['to_terminal_id'] }}">
                                        <input type="hidden" name="fares[{{ $index }}][currency]" value="{{ $route->base_currency }}">
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="{{ route('admin.routes.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Routes
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary px-4" onclick="resetForm()">
                                    <i class="bx bx-refresh me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bx bx-save me-1"></i>Save All Fares
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate final fare when base fare, discount type, or discount value changes
    function calculateFinalFare(row) {
        const baseFareInput = row.querySelector('.base-fare');
        const discountTypeSelect = row.querySelector('.discount-type');
        const discountValueInput = row.querySelector('.discount-value');
        const finalFareInput = row.querySelector('.final-fare');
        
        const baseFare = parseFloat(baseFareInput.value) || 0;
        const discountType = discountTypeSelect.value;
        const discountValue = parseFloat(discountValueInput.value) || 0;
        
        let finalFare = baseFare;
        
        if (discountType && discountValue > 0) {
            if (discountType === 'flat') {
                finalFare = Math.max(0, baseFare - discountValue);
            } else if (discountType === 'percent') {
                finalFare = Math.max(0, baseFare - (baseFare * discountValue / 100));
            }
        }
        
        finalFareInput.value = finalFare.toFixed(2);
    }
    
    // Add event listeners to all fare calculation inputs
    document.querySelectorAll('tbody tr').forEach(row => {
        const baseFareInput = row.querySelector('.base-fare');
        const discountTypeSelect = row.querySelector('.discount-type');
        const discountValueInput = row.querySelector('.discount-value');
        
        [baseFareInput, discountTypeSelect, discountValueInput].forEach(input => {
            if (input) {
                input.addEventListener('input', () => calculateFinalFare(row));
                input.addEventListener('change', () => calculateFinalFare(row));
            }
        });
        
        // Calculate initial values
        calculateFinalFare(row);
    });
    
    // Enable/disable discount value input based on discount type
    document.querySelectorAll('.discount-type').forEach(select => {
        const discountValueInput = select.closest('tr').querySelector('.discount-value');
        
        select.addEventListener('change', function() {
            if (this.value) {
                discountValueInput.disabled = false;
                discountValueInput.required = true;
            } else {
                discountValueInput.disabled = true;
                discountValueInput.required = false;
                discountValueInput.value = '';
            }
            calculateFinalFare(select.closest('tr'));
        });
        
        // Set initial state
        if (select.value) {
            discountValueInput.disabled = false;
            discountValueInput.required = true;
        } else {
            discountValueInput.disabled = true;
            discountValueInput.required = false;
        }
    });
});

function resetForm() {
    if (confirm('Are you sure you want to reset all fare values? This will clear all entered data.')) {
        document.getElementById('faresForm').reset();
        
        // Recalculate all final fares
        document.querySelectorAll('tbody tr').forEach(row => {
            const finalFareInput = row.querySelector('.final-fare');
            if (finalFareInput) {
                finalFareInput.value = '';
            }
        });
    }
}

// Form validation
document.getElementById('faresForm').addEventListener('submit', function(e) {
    const baseFareInputs = document.querySelectorAll('.base-fare');
    let hasValidFares = false;
    
    baseFareInputs.forEach(input => {
        if (input.value && parseFloat(input.value) > 0) {
            hasValidFares = true;
        }
    });
    
    if (!hasValidFares) {
        e.preventDefault();
        alert('Please enter at least one valid fare amount.');
        return false;
    }
});
</script>
@endsection

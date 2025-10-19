@extends('admin.layouts.app')

@section('title', 'Create New Fare')

@section('styles')
<style>
    .fare-card {
        border-left: 4px solid #fd7e14;
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header-info h5 {
        margin: 0;
        font-weight: 600;
    }
    
    .form-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-section:hover {
        background: #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .info-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .info-box i {
        color: #fd7e14;
    }
    
    .preview-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid #fd7e14;
        text-align: center;
    }
    
    .fare-display {
        font-size: 1.5rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 0.5rem;
    }
    
    .route-preview-box {
        background: linear-gradient(135deg, #fff3cd 0%, #d1ecf1 100%);
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid #17a2b8;
        text-align: center;
    }
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Fare Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.fares.index') }}">Fares</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Fare</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card fare-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New Fare</h5>
                </div>
                
                <form action="{{ route('admin.fares.store') }}" method="POST" id="fare-form">
                    @csrf
                    
                    <div class="card-body p-4">
                        <!-- Terminal Selection Section -->
                        <div class="form-section">
                            <h6 class="mb-3">
                                <i class="bx bx-map-pin me-2"></i>Terminal Selection
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="from_terminal_id" class="form-label">
                                        From Terminal <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('from_terminal_id') is-invalid @enderror" 
                                            id="from_terminal_id" name="from_terminal_id" required>
                                        <option value="">Select From Terminal</option>
                                        @foreach($terminals as $terminal)
                                            <option value="{{ $terminal->id }}" 
                                                    {{ old('from_terminal_id') == $terminal->id ? 'selected' : '' }}
                                                    data-city="{{ $terminal->city->name }}">
                                                {{ $terminal->name }} ({{ $terminal->city->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_terminal_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="to_terminal_id" class="form-label">
                                        To Terminal <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('to_terminal_id') is-invalid @enderror" 
                                            id="to_terminal_id" name="to_terminal_id" required>
                                        <option value="">Select To Terminal</option>
                                        @foreach($terminals as $terminal)
                                            <option value="{{ $terminal->id }}" 
                                                    {{ old('to_terminal_id') == $terminal->id ? 'selected' : '' }}
                                                    data-city="{{ $terminal->city->name }}">
                                                {{ $terminal->name }} ({{ $terminal->city->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_terminal_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Fare Information Section -->
                        <div class="form-section">
                            <h6 class="mb-3">
                                <i class="bx bx-money me-2"></i>Fare Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="base_fare" class="form-label">
                                        Base Fare <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="currency-symbol">PKR</span>
                                        <input type="number" 
                                               class="form-control @error('base_fare') is-invalid @enderror" 
                                               id="base_fare" 
                                               name="base_fare" 
                                               value="{{ old('base_fare') }}" 
                                               step="0.01" 
                                               min="1" 
                                               max="100000" 
                                               required>
                                    </div>
                                    @error('base_fare')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Enter the base fare amount</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="currency" class="form-label">
                                        Currency <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('currency') is-invalid @enderror" 
                                            id="currency" name="currency" required>
                                        @foreach($currencies as $code => $name)
                                            <option value="{{ $code }}" 
                                                    {{ old('currency', 'PKR') == $code ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Discount Information Section -->
                        <div class="form-section">
                            <h6 class="mb-3">
                                <i class="bx bx-percentage me-2"></i>Discount Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="discount_type" class="form-label">
                                        Discount Type
                                    </label>
                                    <select class="form-select @error('discount_type') is-invalid @enderror" 
                                            id="discount_type" name="discount_type">
                                        <option value="">No Discount</option>
                                        @foreach($discountTypes as $value => $label)
                                            <option value="{{ $value }}" 
                                                    {{ old('discount_type') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('discount_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="discount_value" class="form-label">
                                        Discount Value
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="discount-symbol">PKR</span>
                                        <input type="number" 
                                               class="form-control @error('discount_value') is-invalid @enderror" 
                                               id="discount_value" 
                                               name="discount_value" 
                                               value="{{ old('discount_value') }}" 
                                               step="0.01" 
                                               min="0" 
                                               max="100000">
                                    </div>
                                    @error('discount_value')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Enter discount amount or percentage</small>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Preview Section -->
                        <div class="form-section">
                            <h6 class="mb-3">
                                <i class="bx bx-toggle-right me-2"></i>Status & Preview
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" 
                                                    {{ old('status', 'active') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Final Fare Preview</label>
                                    <div class="preview-box">
                                        <div class="fare-display" id="final-fare-display">PKR 0.00</div>
                                        <small class="text-muted">Calculated automatically</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Route Preview -->
                        <div class="form-section" id="route-preview" style="display: none;">
                            <h6 class="mb-3">
                                <i class="bx bx-route me-2"></i>Route Preview
                            </h6>
                            <div class="route-preview-box">
                                <div id="route-details"></div>
                            </div>
                        </div>

                        <!-- Information Box -->
                        <div class="info-box">
                            <div class="d-flex align-items-start">
                                <i class="bx bx-info-circle me-2 mt-1"></i>
                                <div>
                                    <strong class="d-block mb-2">Fare Creation Tips:</strong>
                                    <ul class="mb-0 ps-3">
                                        <li>Ensure terminals are different (no same terminal fares)</li>
                                        <li>Base fare should be reasonable for the route distance</li>
                                        <li>Discounts are optional and can be flat amount or percentage</li>
                                        <li>Final fare is calculated automatically based on discount</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <a href="{{ route('admin.fares.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary px-4" id="resetFormBtn">
                                    <i class="bx bx-reset me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Create Fare
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetBtn = document.getElementById('resetFormBtn');
    const form = document.getElementById('fare-form');
    const baseFareInput = document.getElementById('base_fare');
    const discountTypeSelect = document.getElementById('discount_type');
    const discountValueInput = document.getElementById('discount_value');
    const currencySelect = document.getElementById('currency');
    const fromTerminalSelect = document.getElementById('from_terminal_id');
    const toTerminalSelect = document.getElementById('to_terminal_id');
    const statusSelect = document.getElementById('status');

    // ✅ Update currency symbol when currency changes
    currencySelect.addEventListener('change', function() {
        const currency = this.value;
        document.getElementById('currency-symbol').textContent = currency;
        document.getElementById('discount-symbol').textContent = currency;
        calculateFinalFare();
    });

    // ✅ Calculate final fare when inputs change
    [baseFareInput, discountTypeSelect, discountValueInput].forEach(element => {
        element.addEventListener('input', calculateFinalFare);
        element.addEventListener('change', calculateFinalFare);
    });

    // ✅ Update discount symbol based on discount type
    discountTypeSelect.addEventListener('change', function() {
        const discountType = this.value;
        const symbol = discountType === 'percent' ? '%' : currencySelect.value;
        document.getElementById('discount-symbol').textContent = symbol;
        calculateFinalFare();
    });

    // ✅ Show route preview when terminals are selected
    [fromTerminalSelect, toTerminalSelect].forEach(element => {
        element.addEventListener('change', updateRoutePreview);
    });

    // ✅ Reset form functionality
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
                form.reset();
                document.getElementById('final-fare-display').textContent = 'PKR 0.00';
                document.getElementById('route-preview').style.display = 'none';
                document.getElementById('currency-symbol').textContent = 'PKR';
                document.getElementById('discount-symbol').textContent = 'PKR';
                calculateFinalFare();
            }
        });
    }

    // ✅ Initial calculation
    calculateFinalFare();
});

function calculateFinalFare() {
    const baseFare = parseFloat(document.getElementById('base_fare').value) || 0;
    const discountType = document.getElementById('discount_type').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
    const currency = document.getElementById('currency').value;

    let finalFare = baseFare;

    if (discountType && discountValue > 0) {
        if (discountType === 'flat') {
            finalFare = Math.max(0, baseFare - discountValue);
        } else if (discountType === 'percent') {
            finalFare = Math.max(0, baseFare - (baseFare * discountValue / 100));
        }
    }

    document.getElementById('final-fare-display').textContent = currency + ' ' + finalFare.toFixed(2);
}

function updateRoutePreview() {
    const fromTerminalId = document.getElementById('from_terminal_id').value;
    const toTerminalId = document.getElementById('to_terminal_id').value;

    if (fromTerminalId && toTerminalId) {
        const fromTerminal = document.getElementById('from_terminal_id').selectedOptions[0];
        const toTerminal = document.getElementById('to_terminal_id').selectedOptions[0];
        
        const fromCity = fromTerminal.dataset.city;
        const toCity = toTerminal.dataset.city;
        
        document.getElementById('route-details').innerHTML = `
            <strong>${fromTerminal.textContent}</strong> → <strong>${toTerminal.textContent}</strong><br>
            <small class="text-muted">${fromCity} → ${toCity}</small>
        `;
        
        document.getElementById('route-preview').style.display = 'block';
    } else {
        document.getElementById('route-preview').style.display = 'none';
    }
}
</script>
@endpush

@extends('admin.layouts.app')

@section('title', 'Create New Fare')

@section('styles')
<style>
    .fare-card {
        border-left: 4px solid #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        overflow: hidden;
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
    
    .form-control:disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
        opacity: 0.6;
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
    
    
    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    .preview-box {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
        border-left: 4px solid #ffc107;
        padding: 0.75rem;
        border-radius: 6px;
        margin-top: 0.5rem;
        text-align: center;
    }
    
    .fare-display {
        font-size: 1.2rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 0.25rem;
    }
    
    .route-preview-box {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border-left: 4px solid #17a2b8;
        padding: 0.75rem;
        border-radius: 6px;
        text-align: center;
    }
    
    .fare-alert-box {
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 4px solid;
    }
    
    .fare-alert-box.info {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-left-color: #2196f3;
        color: #1976d2;
    }
    
    .fare-alert-box.warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
        border-left-color: #ffc107;
        color: #856404;
    }
    
    .fare-alert-box p {
        margin: 0;
        font-size: 0.875rem;
    }
    
    .fare-alert-box .btn {
        margin-top: 0.5rem;
        font-size: 0.875rem;
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
        <div class="col-xl-6 mx-auto">
            <div class="card fare-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New Fare</h5>
                </div>
                
                <form action="{{ route('admin.fares.store') }}" method="POST" id="fare-form" class="row g-3">
                    @csrf
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
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

                            <div class="col-md-6">
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

                        <div class="row mt-3">
                            <div class="col-md-6">
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
                                           min="1" 
                                           max="100000" 
                                           required>
                                </div>
                                @error('base_fare')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
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


                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="preview-box">
                                    <div class="fare-display" id="final-fare-display">PKR 0.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Route Preview & Fare Status -->
                        <div class="row mt-3" id="route-preview" style="display: none;">
                            <div class="col-12">
                                <div class="route-preview-box">
                                    <div id="route-details"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Fare Alert -->
                        <div class="row mt-3" id="fare-alert-container" style="display: none;">
                            <div class="col-12">
                                <div id="fare-alert"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.fares.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.fares.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4" id="submit-btn">
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseFareInput = document.getElementById('base_fare');
    const currencySelect = document.getElementById('currency');
    const fromTerminalSelect = document.getElementById('from_terminal_id');
    const toTerminalSelect = document.getElementById('to_terminal_id');

    // Update currency symbol when currency changes
    currencySelect.addEventListener('change', function() {
        const currency = this.value;
        document.getElementById('currency-symbol').textContent = currency;
        calculateFinalFare();
    });

    // Calculate final fare when inputs change
    baseFareInput.addEventListener('input', calculateFinalFare);
    baseFareInput.addEventListener('change', calculateFinalFare);

    // Show route preview and check for existing fare when terminals are selected
    [fromTerminalSelect, toTerminalSelect].forEach(element => {
        element.addEventListener('change', function() {
            updateRoutePreview();
            checkExistingFare();
        });
    });

    // Function to check if fare exists for selected terminals
    function checkExistingFare() {
        const fromTerminalId = fromTerminalSelect.value;
        const toTerminalId = toTerminalSelect.value;
        const alertContainer = document.getElementById('fare-alert-container');
        const alertDiv = document.getElementById('fare-alert');

        if (!fromTerminalId || !toTerminalId || fromTerminalId === toTerminalId) {
            alertContainer.style.display = 'none';
            // Show submit button when terminals are cleared
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.style.display = '';
            }
            return;
        }

        // Show loading state
        alertContainer.style.display = 'block';
        alertDiv.innerHTML = '<p><i class="bx bx-loader-alt bx-spin me-1"></i>Checking for existing fare...</p>';

        fetch(`{{ route('admin.fares.check') }}?from_terminal_id=${fromTerminalId}&to_terminal_id=${toTerminalId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.exists) {
                // Fare exists - load it
                const fare = data.fare;
                alertDiv.className = 'fare-alert-box info';
                alertDiv.innerHTML = `
                    <p><i class="bx bx-info-circle me-1"></i><strong>Fare Found!</strong> Loading existing fare data...</p>
                    <p class="mb-0">Base Fare: ${fare.currency} ${parseInt(fare.base_fare).toLocaleString()} | Final Fare: ${fare.currency} ${parseInt(fare.final_fare).toLocaleString()}</p>
                `;

                // Hide submit button since fare already exists
                const submitBtn = document.getElementById('submit-btn');
                if (submitBtn) {
                    submitBtn.style.display = 'none';
                }

                // Load fare data into form
                document.getElementById('base_fare').value = fare.base_fare;
                document.getElementById('currency').value = fare.currency;
                
                // Update currency symbol
                document.getElementById('currency-symbol').textContent = fare.currency;
                
                calculateFinalFare();

                // Show edit button after a delay
                setTimeout(() => {
                    alertDiv.innerHTML = `
                        <p><i class="bx bx-check-circle me-1"></i><strong>Fare Loaded!</strong> Existing fare data has been loaded into the form.</p>
                        <a href="${window.location.origin}/admin/fares/${fare.id}/edit" class="btn btn-sm btn-primary">
                            <i class="bx bx-edit me-1"></i>Edit Existing Fare
                        </a>
                    `;
                }, 1000);
            } else {
                // No fare exists - show message and show submit button
                alertDiv.className = 'fare-alert-box warning';
                alertDiv.innerHTML = `
                    <p><i class="bx bx-info-circle me-1"></i><strong>No Fare Found</strong></p>
                    <p class="mb-0">${data.message || 'No fare exists for this terminal pair. Fill in the form to create a new fare.'}</p>
                `;
                
                // Show submit button if it was hidden
                const submitBtn = document.getElementById('submit-btn');
                if (submitBtn) {
                    submitBtn.style.display = '';
                }
            }
        })
        .catch(error => {
            alertDiv.className = 'fare-alert-box warning';
            alertDiv.innerHTML = `<p><i class="bx bx-error me-1"></i>Error checking fare: ${error.message}</p>`;
            // Show submit button on error (allow user to proceed)
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.style.display = '';
            }
        });
    }

    // Initial setup
    calculateFinalFare();
});

function calculateFinalFare() {
    const baseFare = parseInt(document.getElementById('base_fare').value) || 0;
    const currency = document.getElementById('currency').value;

    // Base fare is the final fare (no discounts)
    const finalFare = baseFare;

    document.getElementById('final-fare-display').textContent = currency + ' ' + finalFare.toLocaleString();
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
@endsection


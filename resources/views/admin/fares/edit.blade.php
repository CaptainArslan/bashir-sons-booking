@extends('admin.layouts.app')

@section('title', 'Edit Fare')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-edit me-2"></i>Edit Fare
            </h1>
            <p class="text-muted mb-0">Update fare information between terminals</p>
        </div>
        <a href="{{ route('admin.fares.index') }}" class="btn btn-light">
            <i class="bx bx-arrow-back me-1"></i>Back to Fares
        </a>
    </div>

    <!-- Form Card -->
    <div class="card shadow">
        <form action="{{ route('admin.fares.update', $fare->id) }}" method="POST" id="fare-form">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <!-- Terminal Selection -->
                    <div class="col-md-6 mb-4">
                        <label for="from_terminal_id" class="form-label">
                            <i class="bx bx-map-pin me-1"></i>From Terminal <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('from_terminal_id') is-invalid @enderror" 
                                id="from_terminal_id" name="from_terminal_id" required>
                            <option value="">Select From Terminal</option>
                            @foreach($terminals as $terminal)
                                <option value="{{ $terminal->id }}" 
                                        {{ old('from_terminal_id', $fare->from_terminal_id) == $terminal->id ? 'selected' : '' }}
                                        data-city="{{ $terminal->city->name }}">
                                    {{ $terminal->name }} ({{ $terminal->city->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('from_terminal_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="to_terminal_id" class="form-label">
                            <i class="bx bx-map-pin me-1"></i>To Terminal <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('to_terminal_id') is-invalid @enderror" 
                                id="to_terminal_id" name="to_terminal_id" required>
                            <option value="">Select To Terminal</option>
                            @foreach($terminals as $terminal)
                                <option value="{{ $terminal->id }}" 
                                        {{ old('to_terminal_id', $fare->to_terminal_id) == $terminal->id ? 'selected' : '' }}
                                        data-city="{{ $terminal->city->name }}">
                                    {{ $terminal->name }} ({{ $terminal->city->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('to_terminal_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Fare Information -->
                    <div class="col-md-6 mb-4">
                        <label for="base_fare" class="form-label">
                            <i class="bx bx-money me-1"></i>Base Fare <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" id="currency-symbol">{{ $fare->currency }}</span>
                            <input type="number" 
                                   class="form-control @error('base_fare') is-invalid @enderror" 
                                   id="base_fare" 
                                   name="base_fare" 
                                   value="{{ old('base_fare', $fare->base_fare) }}" 
                                   step="0.01" 
                                   min="1" 
                                   max="100000" 
                                   required>
                        </div>
                        @error('base_fare')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Enter the base fare amount</small>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="currency" class="form-label">
                            <i class="bx bx-dollar me-1"></i>Currency <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('currency') is-invalid @enderror" 
                                id="currency" name="currency" required>
                            @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" 
                                        {{ old('currency', $fare->currency) == $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Discount Information -->
                    <div class="col-md-6 mb-4">
                        <label for="discount_type" class="form-label">
                            <i class="bx bx-percentage me-1"></i>Discount Type
                        </label>
                        <select class="form-select @error('discount_type') is-invalid @enderror" 
                                id="discount_type" name="discount_type">
                            <option value="">No Discount</option>
                            @foreach($discountTypes as $value => $label)
                                <option value="{{ $value }}" 
                                        {{ old('discount_type', $fare->discount_type) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="discount_value" class="form-label">
                            <i class="bx bx-minus me-1"></i>Discount Value
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" id="discount-symbol">{{ $fare->currency }}</span>
                            <input type="number" 
                                   class="form-control @error('discount_value') is-invalid @enderror" 
                                   id="discount_value" 
                                   name="discount_value" 
                                   value="{{ old('discount_value', $fare->discount_value) }}" 
                                   step="0.01" 
                                   min="0" 
                                   max="100000">
                        </div>
                        @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Enter discount amount or percentage</small>
                    </div>

                    <!-- Final Fare Display -->
                    <div class="col-md-6 mb-4">
                        <label class="form-label">
                            <i class="bx bx-calculator me-1"></i>Final Fare
                        </label>
                        <div class="form-control-plaintext bg-light p-3 rounded">
                            <span class="fw-bold text-success" id="final-fare-display">{{ $fare->currency }} {{ number_format($fare->final_fare, 2) }}</span>
                            <small class="text-muted d-block">Calculated automatically</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-4">
                        <label for="status" class="form-label">
                            <i class="bx bx-toggle-right me-1"></i>Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" 
                                        {{ old('status', $fare->status) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Route Preview -->
                <div class="row" id="route-preview">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bx bx-route me-2"></i>Route Preview
                            </h6>
                            <div id="route-details">
                                <strong>{{ $fare->fromTerminal->name }}</strong> → <strong>{{ $fare->toTerminal->name }}</strong><br>
                                <small class="text-muted">{{ $fare->fromTerminal->city->name }} → {{ $fare->toTerminal->city->name }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Fare Information -->
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-light">
                            <h6 class="alert-heading">
                                <i class="bx bx-info-circle me-2"></i>Current Fare Information
                            </h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Base Fare:</strong><br>
                                    <span class="text-primary">{{ $fare->currency }} {{ number_format($fare->base_fare, 2) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Discount:</strong><br>
                                    <span class="text-info">
                                        @if($fare->discount_type && $fare->discount_value > 0)
                                            {{ $fare->discount_type === 'percent' ? $fare->discount_value . '%' : $fare->currency . ' ' . number_format($fare->discount_value, 2) }}
                                        @else
                                            No Discount
                                        @endif
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Final Fare:</strong><br>
                                    <span class="text-success">{{ $fare->currency }} {{ number_format($fare->final_fare, 2) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge {{ $fare->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($fare->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Footer -->
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.fares.index') }}" class="btn btn-light px-4">
                            <i class="bx bx-arrow-back me-1"></i>Cancel
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary px-4" onclick="resetForm()">
                            <i class="bx bx-reset me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i>Update Fare
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update currency symbol when currency changes
    $('#currency').on('change', function() {
        const currency = $(this).val();
        $('#currency-symbol').text(currency);
        $('#discount-symbol').text(currency);
        calculateFinalFare();
    });

    // Calculate final fare when inputs change
    $('#base_fare, #discount_type, #discount_value').on('input change', function() {
        calculateFinalFare();
    });

    // Update discount symbol based on discount type
    $('#discount_type').on('change', function() {
        const discountType = $(this).val();
        const symbol = discountType === 'percent' ? '%' : $('#currency').val();
        $('#discount-symbol').text(symbol);
        calculateFinalFare();
    });

    // Show route preview when terminals are selected
    $('#from_terminal_id, #to_terminal_id').on('change', function() {
        updateRoutePreview();
    });

    // Initial calculation
    calculateFinalFare();
});

function calculateFinalFare() {
    const baseFare = parseFloat($('#base_fare').val()) || 0;
    const discountType = $('#discount_type').val();
    const discountValue = parseFloat($('#discount_value').val()) || 0;
    const currency = $('#currency').val();

    let finalFare = baseFare;

    if (discountType && discountValue > 0) {
        if (discountType === 'flat') {
            finalFare = Math.max(0, baseFare - discountValue);
        } else if (discountType === 'percent') {
            finalFare = Math.max(0, baseFare - (baseFare * discountValue / 100));
        }
    }

    $('#final-fare-display').text(currency + ' ' + finalFare.toFixed(2));
}

function updateRoutePreview() {
    const fromTerminalId = $('#from_terminal_id').val();
    const toTerminalId = $('#to_terminal_id').val();

    if (fromTerminalId && toTerminalId) {
        const fromTerminal = $('#from_terminal_id option:selected');
        const toTerminal = $('#to_terminal_id option:selected');
        
        const fromCity = fromTerminal.data('city');
        const toCity = toTerminal.data('city');
        
        $('#route-details').html(`
            <strong>${fromTerminal.text()}</strong> → <strong>${toTerminal.text()}</strong><br>
            <small class="text-muted">${fromCity} → ${toCity}</small>
        `);
    }
}

function resetForm() {
    if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
        location.reload();
    }
}
</script>
@endpush

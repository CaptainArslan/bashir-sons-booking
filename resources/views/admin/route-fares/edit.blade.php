@extends('admin.layouts.app')

@section('title', 'Edit Route Fare')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.route-fares.index') }}">Route Fares</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Route Fare</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.route-fares.update', $routeFare->id) }}" method="POST" class="row g-3">
                @method('PUT')
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit Route Fare</h5>
                        
                        <!-- Current Fare Information -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="bx bx-info-circle me-2"></i>Current Fare Information</h6>
                            <p class="mb-1"><strong>Route:</strong> {{ $routeFare->route->name }} ({{ $routeFare->route->code }})</p>
                            <p class="mb-1"><strong>Path:</strong> {{ $routeFare->fromStop->terminal->name }} → {{ $routeFare->toStop->terminal->name }}</p>
                            <p class="mb-0"><strong>Current Final Fare:</strong> PKR {{ number_format($routeFare->final_fare, 2) }}</p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="route_id" class="form-label">Route <span class="text-danger">*</span></label>
                                <select class="form-select @error('route_id') is-invalid @enderror" id="route_id" name="route_id" required>
                                    <option value="">Select Route</option>
                                    @foreach ($routes as $route)
                                        <option value="{{ $route->id }}" {{ old('route_id', $routeFare->route_id) == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('route_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $routeFare->status) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="from_stop_id" class="form-label">From Stop <span class="text-danger">*</span></label>
                                <select class="form-select @error('from_stop_id') is-invalid @enderror" id="from_stop_id" name="from_stop_id" required>
                                    <option value="">Select From Stop</option>
                                    @foreach ($routeStops as $stop)
                                        <option value="{{ $stop->id }}" 
                                                data-route-id="{{ $stop->route_id }}"
                                                {{ old('from_stop_id', $routeFare->from_stop_id) == $stop->id ? 'selected' : '' }}>
                                            {{ $stop->terminal->name }} - {{ $stop->terminal->city->name }} ({{ $stop->terminal->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_stop_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="to_stop_id" class="form-label">To Stop <span class="text-danger">*</span></label>
                                <select class="form-select @error('to_stop_id') is-invalid @enderror" id="to_stop_id" name="to_stop_id" required>
                                    <option value="">Select To Stop</option>
                                    @foreach ($routeStops as $stop)
                                        <option value="{{ $stop->id }}" 
                                                data-route-id="{{ $stop->route_id }}"
                                                {{ old('to_stop_id', $routeFare->to_stop_id) == $stop->id ? 'selected' : '' }}>
                                            {{ $stop->terminal->name }} - {{ $stop->terminal->city->name }} ({{ $stop->terminal->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_stop_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="base_fare" class="form-label">Base Fare (PKR) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('base_fare') is-invalid @enderror" id="base_fare"
                                    name="base_fare" placeholder="Enter base fare amount" 
                                    value="{{ old('base_fare', $routeFare->base_fare) }}" 
                                    step="0.01" min="1" max="100000" required>
                                <div class="form-text">Enter the base fare amount in PKR</div>
                                @error('base_fare')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="discount_type" class="form-label">Discount Type</label>
                                <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type">
                                    <option value="">No Discount</option>
                                    <option value="flat" {{ old('discount_type', $routeFare->discount_type) == 'flat' ? 'selected' : '' }}>Flat Amount</option>
                                    <option value="percent" {{ old('discount_type', $routeFare->discount_type) == 'percent' ? 'selected' : '' }}>Percentage</option>
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6" id="discount_value_container" style="display: {{ $routeFare->discount_type ? 'block' : 'none' }};">
                                <label for="discount_value" class="form-label">Discount Value</label>
                                <input type="number" class="form-control @error('discount_value') is-invalid @enderror" id="discount_value"
                                    name="discount_value" placeholder="Enter discount value" 
                                    value="{{ old('discount_value', $routeFare->discount_value) }}" 
                                    step="0.01" min="0" max="100">
                                <div class="form-text" id="discount_help_text">Enter discount amount or percentage</div>
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Calculated Final Fare</label>
                                <div class="form-control-plaintext" id="final_fare_display">
                                    <span class="text-success fw-bold">PKR {{ number_format($routeFare->final_fare, 2) }}</span>
                                </div>
                                <div class="form-text">Final fare will be calculated automatically</div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.route-fares.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Update Route Fare
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
            const routeSelect = document.getElementById('route_id');
            const fromStopSelect = document.getElementById('from_stop_id');
            const toStopSelect = document.getElementById('to_stop_id');
            const discountTypeSelect = document.getElementById('discount_type');
            const discountValueContainer = document.getElementById('discount_value_container');
            const discountValueInput = document.getElementById('discount_value');
            const discountHelpText = document.getElementById('discount_help_text');
            const baseFareInput = document.getElementById('base_fare');
            const finalFareDisplay = document.getElementById('final_fare_display');

            // Filter stops based on selected route
            function filterStopsByRoute() {
                const selectedRouteId = routeSelect.value;
                
                // Filter from stops
                Array.from(fromStopSelect.options).forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                    } else {
                        option.style.display = option.dataset.routeId === selectedRouteId ? 'block' : 'none';
                    }
                });
                
                // Filter to stops
                Array.from(toStopSelect.options).forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                    } else {
                        option.style.display = option.dataset.routeId === selectedRouteId ? 'block' : 'none';
                    }
                });
            }

            // Show/hide discount value input
            function toggleDiscountInput() {
                const discountType = discountTypeSelect.value;
                
                if (discountType) {
                    discountValueContainer.style.display = 'block';
                    discountValueInput.required = true;
                    
                    if (discountType === 'flat') {
                        discountHelpText.textContent = 'Enter flat discount amount in PKR';
                        discountValueInput.max = '100000';
                    } else if (discountType === 'percent') {
                        discountHelpText.textContent = 'Enter discount percentage (0-100)';
                        discountValueInput.max = '100';
                    }
                } else {
                    discountValueContainer.style.display = 'none';
                    discountValueInput.required = false;
                    discountValueInput.value = '';
                }
                
                calculateFinalFare();
            }

            // Calculate final fare
            function calculateFinalFare() {
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
                
                finalFareDisplay.innerHTML = `<span class="text-success fw-bold">PKR ${finalFare.toFixed(2)}</span>`;
            }

            // Event listeners
            routeSelect.addEventListener('change', filterStopsByRoute);
            discountTypeSelect.addEventListener('change', toggleDiscountInput);
            baseFareInput.addEventListener('input', calculateFinalFare);
            discountValueInput.addEventListener('input', calculateFinalFare);
            
            // Initialize
            filterStopsByRoute();
            toggleDiscountInput();
            calculateFinalFare();
        });
    </script>
@endsection

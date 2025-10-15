@extends('admin.layouts.app')

@section('title', 'Manage Route Fares')

@section('styles')
    <style>
        .fare-matrix {
            overflow-x: auto;
        }
        .fare-cell {
            min-width: 120px;
            padding: 8px;
        }
        .stop-header {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            padding: 10px;
        }
        .stop-name {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 10px;
            text-align: center;
            min-width: 150px;
        }
        .fare-input {
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .discount-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .discount-type-select {
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .route-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }
        .no-route-selected {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .fare-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
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
                    <li class="breadcrumb-item active" aria-current="page">Manage Fares</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-12 mx-auto">
            <!-- Route Selection Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bx bx-map me-2"></i>Select Route to Manage Fares
                    </h5>
                    <div class="row">
                        <div class="col-md-8">
                            <select class="form-select" id="route-select" name="route_id">
                                <option value="">Select a route to manage fares...</option>
                                @foreach ($routes as $route)
                                    <option value="{{ $route->id }}" data-code="{{ $route->code }}" data-name="{{ $route->name }}">
                                        {{ $route->name }} ({{ $route->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" id="load-route-btn" disabled>
                                <i class="bx bx-search me-1"></i>Load Route Fares
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div class="card loading-spinner" id="loading-spinner">
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="spinner-border text-primary me-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span>Loading route information and existing fares...</span>
                    </div>
                </div>
            </div>

            <!-- No Route Selected -->
            <div class="card no-route-selected" id="no-route-selected">
                <div class="card-body">
                    <i class="bx bx-map text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">Select a Route</h5>
                    <p class="text-muted">Choose a route from the dropdown above to start managing fares between stops.</p>
                </div>
            </div>

            <!-- Route Information Card -->
            <div class="card route-info-card" id="route-info-card" style="display: none;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1" id="route-name">Route Name</h4>
                            <p class="mb-0" id="route-code">Route Code</p>
                            <small id="route-stops-count">0 stops</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-light" id="save-all-fares-btn">
                                <i class="bx bx-save me-1"></i>Save All Fares
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fare Management Card -->
            <div class="card" id="fare-management-card" style="display: none;">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bx bx-money me-2"></i>Fare Matrix
                        <small class="text-muted">(From Stop → To Stop)</small>
                    </h5>
                    
                    <div class="fare-matrix" id="fare-matrix">
                        <!-- Fare matrix will be generated here -->
                    </div>

                    <!-- Fare Summary -->
                    <div class="fare-summary" id="fare-summary" style="display: none;">
                        <h6 class="mb-3">Fare Summary</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary mb-1" id="total-fares">0</h4>
                                    <small class="text-muted">Total Fares</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success mb-1" id="active-fares">0</h4>
                                    <small class="text-muted">Active Fares</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning mb-1" id="inactive-fares">0</h4>
                                    <small class="text-muted">Inactive Fares</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info mb-1" id="avg-fare">PKR 0</h4>
                                    <small class="text-muted">Average Fare</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let currentRouteId = null;
        let routeStops = [];
        let existingFares = {};

        document.addEventListener('DOMContentLoaded', function() {
            const routeSelect = document.getElementById('route-select');
            const loadRouteBtn = document.getElementById('load-route-btn');
            const saveAllFaresBtn = document.getElementById('save-all-fares-btn');

            // Route selection change
            routeSelect.addEventListener('change', function() {
                const selectedRouteId = this.value;
                loadRouteBtn.disabled = !selectedRouteId;
                
                if (!selectedRouteId) {
                    hideAllCards();
                    showNoRouteSelected();
                }
            });

            // Load route button click
            loadRouteBtn.addEventListener('click', function() {
                const selectedRouteId = routeSelect.value;
                if (selectedRouteId) {
                    loadRouteFares(selectedRouteId);
                }
            });

            // Save all fares button click
            saveAllFaresBtn.addEventListener('click', function() {
                saveAllFares();
            });
        });

        function loadRouteFares(routeId) {
            currentRouteId = routeId;
            showLoading();
            
            // Get route information
            const selectedOption = document.querySelector(`#route-select option[value="${routeId}"]`);
            const routeName = selectedOption.dataset.name;
            const routeCode = selectedOption.dataset.code;

            // Update route info
            document.getElementById('route-name').textContent = routeName;
            document.getElementById('route-code').textContent = routeCode;

            // Fetch route stops and existing fares
            Promise.all([
                fetch(`{{ route('admin.route-fares.route-stops', ':routeId') }}`.replace(':routeId', routeId))
                    .then(response => response.json()),
                fetch(`{{ route('admin.route-fares.data') }}?route_id=${routeId}`)
                    .then(response => response.json())
            ]).then(([stopsResponse, faresResponse]) => {
                if (stopsResponse.success) {
                    routeStops = stopsResponse.data;
                    existingFares = {};
                    
                    // Process existing fares
                    if (faresResponse.data && faresResponse.data.length > 0) {
                        faresResponse.data.forEach(fare => {
                            const key = `${fare.from_stop_id}_${fare.to_stop_id}`;
                            existingFares[key] = fare;
                        });
                    }
                    
                    generateFareMatrix();
                    updateFareSummary();
                    hideLoading();
                    showFareManagement();
                } else {
                    toastr.error('Failed to load route information');
                    hideLoading();
                    showNoRouteSelected();
                }
            }).catch(error => {
                console.error('Error loading route fares:', error);
                toastr.error('An error occurred while loading route information');
                hideLoading();
                showNoRouteSelected();
            });
        }

        function generateFareMatrix() {
            const matrixContainer = document.getElementById('fare-matrix');
            const stopsCount = routeStops.length;
            
            if (stopsCount < 2) {
                matrixContainer.innerHTML = '<div class="alert alert-warning">This route has less than 2 stops. Cannot create fares.</div>';
                return;
            }

            let matrixHTML = '<table class="table table-bordered">';
            
            // Header row
            matrixHTML += '<thead><tr>';
            matrixHTML += '<th class="stop-name">From \\ To</th>';
            routeStops.forEach(stop => {
                matrixHTML += `<th class="stop-header">${stop.text}</th>`;
            });
            matrixHTML += '</tr></thead>';
            
            // Data rows
            matrixHTML += '<tbody>';
            routeStops.forEach((fromStop, fromIndex) => {
                matrixHTML += '<tr>';
                matrixHTML += `<td class="stop-name">${fromStop.text}</td>`;
                
                routeStops.forEach((toStop, toIndex) => {
                    if (fromIndex === toIndex) {
                        // Same stop - show dash
                        matrixHTML += '<td class="fare-cell text-center text-muted">-</td>';
                    } else {
                        // Different stops - show fare input
                        const fareKey = `${fromStop.id}_${toStop.id}`;
                        const existingFare = existingFares[fareKey];
                        
                        matrixHTML += '<td class="fare-cell">';
                        matrixHTML += generateFareInputs(fareKey, existingFare);
                        matrixHTML += '</td>';
                    }
                });
                
                matrixHTML += '</tr>';
            });
            matrixHTML += '</tbody></table>';
            
            matrixContainer.innerHTML = matrixHTML;
            
            // Update stops count
            document.getElementById('route-stops-count').textContent = `${stopsCount} stops`;
        }

        function generateFareInputs(fareKey, existingFare) {
            const baseFare = existingFare ? existingFare.base_fare : '';
            const discountType = existingFare ? existingFare.discount_type : '';
            const discountValue = existingFare ? existingFare.discount_value : '';
            const status = existingFare ? existingFare.status : 'active';
            
            let html = `
                <div class="mb-2">
                    <label class="form-label small">Base Fare (PKR)</label>
                    <input type="number" class="fare-input" 
                           name="fares[${fareKey}][base_fare]" 
                           value="${baseFare}" 
                           step="0.01" min="0" 
                           placeholder="0.00"
                           onchange="calculateFare('${fareKey}')">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Discount Type</label>
                    <select class="discount-type-select" 
                            name="fares[${fareKey}][discount_type]"
                            onchange="toggleDiscountValue('${fareKey}')">
                        <option value="">No Discount</option>
                        <option value="flat" ${discountType === 'flat' ? 'selected' : ''}>Flat Amount</option>
                        <option value="percent" ${discountType === 'percent' ? 'selected' : ''}>Percentage</option>
                    </select>
                </div>
                <div class="mb-2" id="discount-value-${fareKey}" style="display: ${discountType ? 'block' : 'none'};">
                    <label class="form-label small">Discount Value</label>
                    <input type="number" class="discount-input" 
                           name="fares[${fareKey}][discount_value]" 
                           value="${discountValue}" 
                           step="0.01" min="0" 
                           placeholder="0"
                           onchange="calculateFare('${fareKey}')">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Final Fare</label>
                    <div class="form-control-plaintext small" id="final-fare-${fareKey}">
                        PKR 0.00
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Status</label>
                    <select class="form-select form-select-sm" name="fares[${fareKey}][status]">
                        <option value="active" ${status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${status === 'inactive' ? 'selected' : ''}>Inactive</option>
                        <option value="suspended" ${status === 'suspended' ? 'selected' : ''}>Suspended</option>
                    </select>
                </div>
            `;
            
            return html;
        }

        function toggleDiscountValue(fareKey) {
            const discountTypeSelect = document.querySelector(`select[name="fares[${fareKey}][discount_type]"]`);
            const discountValueContainer = document.getElementById(`discount-value-${fareKey}`);
            const discountValueInput = document.querySelector(`input[name="fares[${fareKey}][discount_value]"]`);
            
            if (discountTypeSelect.value) {
                discountValueContainer.style.display = 'block';
                discountValueInput.required = true;
            } else {
                discountValueContainer.style.display = 'none';
                discountValueInput.required = false;
                discountValueInput.value = '';
            }
            
            calculateFare(fareKey);
        }

        function calculateFare(fareKey) {
            const baseFareInput = document.querySelector(`input[name="fares[${fareKey}][base_fare]"]`);
            const discountTypeSelect = document.querySelector(`select[name="fares[${fareKey}][discount_type]"]`);
            const discountValueInput = document.querySelector(`input[name="fares[${fareKey}][discount_value]"]`);
            const finalFareDisplay = document.getElementById(`final-fare-${fareKey}`);
            
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
            
            finalFareDisplay.innerHTML = `<strong class="text-success">PKR ${finalFare.toFixed(2)}</strong>`;
        }

        function saveAllFares() {
            if (!currentRouteId) {
                toastr.error('No route selected');
                return;
            }

            const fareData = {};
            const fareInputs = document.querySelectorAll('input[name^="fares["], select[name^="fares["]');
            
            // Collect all fare data
            fareInputs.forEach(input => {
                const name = input.name;
                const match = name.match(/fares\[([^\]]+)\]\[([^\]]+)\]/);
                if (match) {
                    const fareKey = match[1];
                    const field = match[2];
                    
                    if (!fareData[fareKey]) {
                        fareData[fareKey] = {};
                    }
                    
                    fareData[fareKey][field] = input.value;
                }
            });

            // Prepare data for submission
            const faresToSave = [];
            Object.keys(fareData).forEach(fareKey => {
                const [fromStopId, toStopId] = fareKey.split('_');
                const fare = fareData[fareKey];
                
                if (fare.base_fare && parseFloat(fare.base_fare) > 0) {
                    faresToSave.push({
                        route_id: currentRouteId,
                        from_stop_id: fromStopId,
                        to_stop_id: toStopId,
                        base_fare: parseFloat(fare.base_fare),
                        discount_type: fare.discount_type || null,
                        discount_value: fare.discount_value ? parseFloat(fare.discount_value) : null,
                        status: fare.status || 'active'
                    });
                }
            });

            if (faresToSave.length === 0) {
                toastr.warning('No fares to save. Please add at least one fare.');
                return;
            }

            // Show loading
            saveAllFaresBtn.disabled = true;
            saveAllFaresBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...';

            // Send AJAX request
            fetch(`{{ route('admin.route-fares.bulk-save') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ fares: faresToSave })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    // Reload the fares
                    loadRouteFares(currentRouteId);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error saving fares:', error);
                toastr.error('An error occurred while saving fares');
            })
            .finally(() => {
                saveAllFaresBtn.disabled = false;
                saveAllFaresBtn.innerHTML = '<i class="bx bx-save me-1"></i>Save All Fares';
            });
        }

        function updateFareSummary() {
            const totalFares = Object.keys(existingFares).length;
            const activeFares = Object.values(existingFares).filter(fare => fare.status === 'active').length;
            const inactiveFares = totalFares - activeFares;
            const avgFare = totalFares > 0 ? 
                Object.values(existingFares).reduce((sum, fare) => sum + parseFloat(fare.final_fare), 0) / totalFares : 0;

            document.getElementById('total-fares').textContent = totalFares;
            document.getElementById('active-fares').textContent = activeFares;
            document.getElementById('inactive-fares').textContent = inactiveFares;
            document.getElementById('avg-fare').textContent = `PKR ${avgFare.toFixed(2)}`;
            
            document.getElementById('fare-summary').style.display = totalFares > 0 ? 'block' : 'none';
        }

        function showLoading() {
            document.getElementById('loading-spinner').style.display = 'block';
            hideAllCards();
        }

        function hideLoading() {
            document.getElementById('loading-spinner').style.display = 'none';
        }

        function showNoRouteSelected() {
            document.getElementById('no-route-selected').style.display = 'block';
        }

        function showFareManagement() {
            document.getElementById('route-info-card').style.display = 'block';
            document.getElementById('fare-management-card').style.display = 'block';
        }

        function hideAllCards() {
            document.getElementById('no-route-selected').style.display = 'none';
            document.getElementById('route-info-card').style.display = 'none';
            document.getElementById('fare-management-card').style.display = 'none';
        }
    </script>
@endsection

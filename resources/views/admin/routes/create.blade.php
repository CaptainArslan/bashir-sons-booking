@extends('admin.layouts.app')

@section('title', 'Create Route')

@section('styles')
<style>
    .route-card {
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
    
    .stops-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        border-left: 4px solid #0dcaf0;
    }
    
    .stop-item {
        transition: all 0.2s ease;
        border-left: 3px solid #0d6efd !important;
        background: #ffffff;
        border-radius: 6px;
    }
    
    .stop-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
        transform: translateX(2px);
    }
    
    .form-check-label {
        cursor: pointer;
        font-size: 0.875rem;
        transition: color 0.2s ease;
    }
    
    .form-check-input:checked + .form-check-label {
        color: #0d6efd;
        font-weight: 600;
    }
    
    .add-stop-btn {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        border-radius: 20px;
        padding: 8px 16px;
        color: white;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .add-stop-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(40, 167, 69, 0.25);
        color: white;
    }
    
    .badge {
        font-size: 0.75rem;
        width: 24px;
        height: 24px;
    }
    
    .stop-header {
        font-size: 0.9rem;
        font-weight: 600;
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Route</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card route-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New Route</h5>
                </div>
                
                <form action="{{ route('admin.routes.store') }}" method="POST" class="row g-3">
                    @csrf
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Enter route details and add stops in sequence. The route code will be auto-generated based on the route name and direction.</p>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-route me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Route Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name" 
                                       name="name" 
                                       placeholder="Enter Route Name" 
                                       value="{{ old('name') }}"
                                       required
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="direction" class="form-label">
                                    Direction 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('direction') is-invalid @enderror" 
                                        id="direction"
                                        name="direction" 
                                        required>
                                    <option value="">Select Direction</option>
                                    <option value="forward" {{ old('direction') == 'forward' ? 'selected' : '' }}>Forward</option>
                                    <option value="return" {{ old('direction') == 'return' ? 'selected' : '' }}>Return</option>
                                </select>
                                @error('direction')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="code" class="form-label">
                                    Route Code 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror"
                                       id="code" 
                                       name="code" 
                                       placeholder="Route code will be auto-generated"
                                       value="{{ old('code') }}" 
                                       style="text-transform: uppercase;" 
                                       required 
                                       readonly>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Code will be auto-generated based on route name and direction
                                </div>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="is_return_of" class="form-label">Return Route Of</label>
                                <select class="form-select @error('is_return_of') is-invalid @enderror" 
                                        id="is_return_of"
                                        name="is_return_of">
                                    <option value="">Select Return Route (Optional)</option>
                                    @foreach ($routes as $route)
                                        <option value="{{ $route->id }}"
                                            {{ old('is_return_of') == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select if this route is a return route of another route</div>
                                @error('is_return_of')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="base_currency" class="form-label">
                                    Base Currency 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('base_currency') is-invalid @enderror" 
                                        id="base_currency"
                                        name="base_currency" 
                                        required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency }}"
                                            {{ old('base_currency') == $currency ? 'selected' : '' }}>
                                            {{ $currency }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('base_currency')
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
                                    @foreach (\App\Enums\RouteStatusEnum::getStatusOptions() as $value => $label)
                                        <option value="{{ $value }}" {{ old('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Route Stops Section -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-map me-1"></i>Route Stops
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="stops-section">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-map text-primary me-2" style="font-size: 1.2rem;"></i>
                                            <h6 class="mb-0 fw-bold text-primary">Route Stops</h6>
                                        </div>
                                        <button type="button" class="add-stop-btn" id="add-stop-btn">
                                            <i class="bx bx-plus me-1"></i>Add Stop
                                        </button>
                                    </div>
                                    <div id="stops-container">
                                        <!-- Stops will be added here dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>


                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.routes.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Create Route
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
            const nameInput = document.getElementById('name');
            const directionSelect = document.getElementById('direction');
            const codeInput = document.getElementById('code');

            // Auto-generate code when name or direction changes
            function generateRouteCode() {
                const name = nameInput.value.trim();
                const direction = directionSelect.value;

                console.log('Generating code for:', name, direction);

                if (name && direction) {
                    const code = generateCodeFromName(name, direction);
                    console.log('Generated code:', code);
                    codeInput.value = code;
                } else {
                    console.log('Missing name or direction, clearing code');
                    codeInput.value = '';
                }
            }

            // Generate code based on route name and direction
            function generateCodeFromName(name, direction) {
                // Extract city names from route name
                const cities = extractCitiesFromName(name);

                if (cities.length >= 2) {
                    const fromCity = cities[0].substring(0, 3).toUpperCase();
                    const toCity = cities[1].substring(0, 3).toUpperCase();
                    const directionCode = direction === 'forward' ? '001' : '002';
                    return `${fromCity}-${toCity}-${directionCode}`;
                } else if (cities.length === 1) {
                    const city = cities[0].substring(0, 3).toUpperCase();
                    const directionCode = direction === 'forward' ? '001' : '002';
                    return `${city}-ROUTE-${directionCode}`;
                } else {
                    const directionCode = direction === 'forward' ? '001' : '002';
                    return `ROUTE-${directionCode}`;
                }
            }

            // Extract city names from route name
            function extractCitiesFromName(name) {
                const commonPatterns = [
                    /(\w+)\s+to\s+(\w+)/i,
                    /(\w+)\s+-\s+(\w+)/i,
                    /(\w+)\s+→\s+(\w+)/i,
                    /(\w+)\s+and\s+(\w+)/i
                ];

                for (const pattern of commonPatterns) {
                    const match = name.match(pattern);
                    if (match) {
                        return [match[1], match[2]];
                    }
                }

                // If no pattern matches, try to extract words that look like city names
                const words = name.split(/\s+/);
                const cityWords = words.filter(word =>
                    word.length > 2 &&
                    /^[A-Za-z]+$/.test(word) &&
                    !['express', 'route', 'service', 'bus', 'line'].includes(word.toLowerCase())
                );

                return cityWords.slice(0, 2);
            }

            // Event listeners
            nameInput.addEventListener('input', generateRouteCode);
            directionSelect.addEventListener('change', generateRouteCode);

            // Initialize code generation if there are existing values
        if (nameInput.value || directionSelect.value) {
            generateRouteCode();
        }

        // Route Stops Management
        let stopCounter = 0;
        const stopsContainer = document.getElementById('stops-container');
        const addStopBtn = document.getElementById('add-stop-btn');

        // Add initial stop
        addStop();

        addStopBtn.addEventListener('click', function() {
            addStop();
        });

        function addStop() {
            stopCounter++;
            const stopDiv = document.createElement('div');
            stopDiv.className = 'stop-item border rounded p-3 mb-3';
            stopDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                            ${stopCounter}
                        </div>
                        <span class="stop-header text-primary">Stop ${stopCounter}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn" title="Remove this stop">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Terminal <span class="text-danger">*</span></label>
                        <select class="form-select terminal-select" name="stops[${stopCounter}][terminal_id]" required>
                            <option value="">Select Terminal</option>
                            @foreach ($terminals as $terminal)
                                <option value="{{ $terminal->id }}">
                                    {{ $terminal->name }} - {{ $terminal->city->name }} ({{ $terminal->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sequence</label>
                        <input type="number" class="form-control sequence-input" name="stops[${stopCounter}][sequence]" 
                               value="${stopCounter}" min="1" required readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Distance (km)</label>
                        <input type="number" class="form-control distance-input" name="stops[${stopCounter}][distance_from_previous]" 
                               placeholder="0.0" step="0.1" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Travel Time (min)</label>
                        <input type="number" class="form-control travel-time-input" name="stops[${stopCounter}][approx_travel_time]" 
                               placeholder="0" min="0">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="stops[${stopCounter}][is_pickup_allowed]" 
                                   value="1" id="pickup_${stopCounter}" checked>
                            <label class="form-check-label" for="pickup_${stopCounter}">
                                <i class="bx bx-up-arrow-circle me-1 text-success"></i>Pickup
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="stops[${stopCounter}][is_dropoff_allowed]" 
                                   value="1" id="dropoff_${stopCounter}" checked>
                            <label class="form-check-label" for="dropoff_${stopCounter}">
                                <i class="bx bx-down-arrow-circle me-1 text-primary"></i>Dropoff
                            </label>
                        </div>
                    </div>
                </div>
            `;
            
            stopsContainer.appendChild(stopDiv);

            // Add event listeners for this stop
            const removeBtn = stopDiv.querySelector('.remove-stop-btn');
            const distanceInput = stopDiv.querySelector('.distance-input');
            const travelTimeInput = stopDiv.querySelector('.travel-time-input');

            removeBtn.addEventListener('click', function() {
                stopDiv.remove();
                updateSequences();
            });

            // Auto-calculate travel time based on distance
            distanceInput.addEventListener('input', function() {
                const distance = parseFloat(this.value);
                if (distance && !travelTimeInput.value) {
                    const travelTime = Math.round(distance / 60 * 60); // 60 km/h average
                    travelTimeInput.value = travelTime;
                }
            });
        }

        function updateSequences() {
            const stopItems = stopsContainer.querySelectorAll('.stop-item');
            stopItems.forEach((item, index) => {
                const sequenceInput = item.querySelector('.sequence-input');
                const stopNumber = item.querySelector('h6');
                sequenceInput.value = index + 1;
                stopNumber.innerHTML = `<i class="bx bx-map-pin me-2 text-primary"></i>Stop ${index + 1}`;
            });
        }
    });
    </script>
@endsection

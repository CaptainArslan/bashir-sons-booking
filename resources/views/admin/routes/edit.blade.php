@extends('admin.layouts.app')

@section('title', 'Edit Route')

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
    
    .route-info-card {
        border-left: 3px solid #0dcaf0;
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Route</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card route-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Route: {{ $route->name }}</h5>
                </div>
                
                <form action="{{ route('admin.routes.update', $route->id) }}" method="POST" class="row g-3">
                    @method('PUT')
                    @csrf
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Updating route information will affect all timetables and bookings using this route. Please review carefully before saving changes.</p>
                        </div>
                        
                        <!-- Route Information Card -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card route-info-card">
                                    <div class="card-body" style="padding: 0.75rem;">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Route ID:</strong> 
                                                    <span class="badge bg-secondary">{{ $route->id }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Code:</strong> 
                                                    <span class="badge bg-info">{{ $route->code }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Current Status:</strong> 
                                                    <span class="badge bg-{{ \App\Enums\RouteStatusEnum::getStatusColor($route->status->value) }} stats-badge">
                                                        {{ \App\Enums\RouteStatusEnum::getStatusName($route->status->value) }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Direction:</strong> 
                                                    <span class="badge bg-primary">{{ ucfirst($route->direction) }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Stops:</strong> 
                                                    <span class="badge bg-success">{{ $route->routeStops->count() }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Created:</strong> 
                                                    {{ $route->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                       value="{{ old('name', $route->name) }}" 
                                       required
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
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
                                       value="{{ old('code', $route->code) }}" 
                                       style="text-transform: uppercase;" 
                                       required>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Code will be auto-generated based on route name and direction
                                </div>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
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
                                    <option value="forward" {{ old('direction', $route->direction) == 'forward' ? 'selected' : '' }}>Forward</option>
                                    <option value="return" {{ old('direction', $route->direction) == 'return' ? 'selected' : '' }}>Return</option>
                                </select>
                                @error('direction')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="is_return_of" class="form-label">Return Route Of</label>
                                <select class="form-select @error('is_return_of') is-invalid @enderror" 
                                        id="is_return_of" 
                                        name="is_return_of">
                                    <option value="">Select Return Route (Optional)</option>
                                    @foreach ($routes as $routeOption)
                                        <option value="{{ $routeOption->id }}" {{ old('is_return_of', $route->is_return_of) == $routeOption->id ? 'selected' : '' }}>
                                            {{ $routeOption->name }} ({{ $routeOption->code }})
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
                                        <option value="{{ $currency }}" {{ old('base_currency', $route->base_currency) == $currency ? 'selected' : '' }}>
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
                                        <option value="{{ $value }}" {{ old('status', $route->status->value) == $value ? 'selected' : '' }}>
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
                                        @foreach($route->routeStops->sortBy('sequence') as $stop)
                                            <div class="stop-item border rounded p-3 mb-3" data-stop-id="{{ $stop->id }}">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="badge bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                            {{ $stop->sequence }}
                                                        </div>
                                                        <span class="stop-header text-primary">Stop {{ $stop->sequence }}</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn" title="Remove this stop">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Terminal <span class="text-danger">*</span></label>
                                                        <select class="form-select terminal-select" name="stops[{{ $stop->id }}][terminal_id]" required>
                                                            <option value="">Select Terminal</option>
                                                            @foreach ($terminals as $terminal)
                                                                <option value="{{ $terminal->id }}" {{ $stop->terminal_id == $terminal->id ? 'selected' : '' }}>
                                                                    {{ $terminal->name }} - {{ $terminal->city->name }} ({{ $terminal->code }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Sequence</label>
                                                        <input type="number" class="form-control sequence-input" name="stops[{{ $stop->id }}][sequence]" 
                                                               value="{{ $stop->sequence }}" min="1" required readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Distance (km)</label>
                                                        <input type="number" class="form-control distance-input" name="stops[{{ $stop->id }}][distance_from_previous]" 
                                                               value="{{ $stop->distance_from_previous }}" placeholder="0.0" step="0.1" min="0">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Travel Time (min)</label>
                                                        <input type="number" class="form-control travel-time-input" name="stops[{{ $stop->id }}][approx_travel_time]" 
                                                               value="{{ $stop->approx_travel_time }}" placeholder="0" min="0">
                                                    </div>
                                                </div>
                                                <div class="row g-2 mt-2">
                                                    <div class="col-md-6">
                                                        <div class="form-check mt-4">
                                                            <input class="form-check-input" type="checkbox" name="stops[{{ $stop->id }}][is_pickup_allowed]" 
                                                                   value="1" id="pickup_{{ $stop->id }}" {{ $stop->is_pickup_allowed ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="pickup_{{ $stop->id }}">
                                                                <i class="bx bx-up-arrow-circle me-1 text-success"></i>Pickup
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check mt-4">
                                                            <input class="form-check-input" type="checkbox" name="stops[{{ $stop->id }}][is_dropoff_allowed]" 
                                                                   value="1" id="dropoff_{{ $stop->id }}" {{ $stop->is_dropoff_allowed ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="dropoff_{{ $stop->id }}">
                                                                <i class="bx bx-down-arrow-circle me-1 text-primary"></i>Dropoff
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Route Fares Section -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-money me-1"></i>Route Fares
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="stops-section">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-money text-success me-2" style="font-size: 1.2rem;"></i>
                                            <h6 class="mb-0 fw-bold text-success">Route Fares</h6>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($route->routeFares->count() > 0)
                                                <a href="{{ route('admin.routes.fares', $route->id) }}" class="btn btn-success btn-sm">
                                                    <i class="bx bx-edit me-1"></i>Manage Fares
                                                </a>
                                            @else
                                                <a href="{{ route('admin.routes.fares', $route->id) }}" class="btn btn-outline-success btn-sm">
                                                    <i class="bx bx-plus me-1"></i>Add Fares
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    @if($route->routeFares->count() > 0)
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-success">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bx bx-check-circle me-2"></i>
                                                        <div>
                                                            <strong>Fares Configured!</strong>
                                                            <p class="mb-0" style="font-size: 0.85rem;">
                                                                This route has {{ $route->routeFares->count() }} fare(s) configured. 
                                                                Click "Manage Fares" to view or modify existing fares.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-info-circle me-2"></i>
                                                <strong>No fares configured yet</strong>
                                            </div>
                                            <p class="text-muted mb-0 mt-2" style="font-size: 0.85rem;">
                                                Click "Add Fares" to configure pricing for different stop combinations.
                                            </p>
                                        </div>
                                    @endif
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
                                    <i class="bx bx-save me-1"></i>Update Route
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
        let stopCounter = {{ $route->routeStops->max('sequence') ?? 0 }};
        const stopsContainer = document.getElementById('stops-container');
        const addStopBtn = document.getElementById('add-stop-btn');

        addStopBtn.addEventListener('click', function() {
            addStop();
        });

        // Add event listeners to existing stops
        document.querySelectorAll('.remove-stop-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.stop-item').remove();
                updateSequences();
            });
        });

        document.querySelectorAll('.distance-input').forEach(input => {
            input.addEventListener('input', function() {
                const distance = parseFloat(this.value);
                const travelTimeInput = this.closest('.stop-item').querySelector('.travel-time-input');
                if (distance && !travelTimeInput.value) {
                    const travelTime = Math.round(distance / 60 * 60); // 60 km/h average
                    travelTimeInput.value = travelTime;
                }
            });
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
                        <select class="form-select terminal-select" name="stops[new_${stopCounter}][terminal_id]" required>
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
                        <input type="number" class="form-control sequence-input" name="stops[new_${stopCounter}][sequence]" 
                               value="${stopCounter}" min="1" required readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Distance (km)</label>
                        <input type="number" class="form-control distance-input" name="stops[new_${stopCounter}][distance_from_previous]" 
                               placeholder="0.0" step="0.1" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Travel Time (min)</label>
                        <input type="number" class="form-control travel-time-input" name="stops[new_${stopCounter}][approx_travel_time]" 
                               placeholder="0" min="0">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="stops[new_${stopCounter}][is_pickup_allowed]" 
                                   value="1" id="pickup_new_${stopCounter}" checked>
                            <label class="form-check-label" for="pickup_new_${stopCounter}">
                                <i class="bx bx-up-arrow-circle me-1 text-success"></i>Pickup
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="stops[new_${stopCounter}][is_dropoff_allowed]" 
                                   value="1" id="dropoff_new_${stopCounter}" checked>
                            <label class="form-check-label" for="dropoff_new_${stopCounter}">
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

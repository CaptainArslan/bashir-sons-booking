@extends('admin.layouts.app')

@section('title', 'Edit Route')

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
                    <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Route</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.routes.update', $route->id) }}" method="POST" class="row g-3">
                @method('PUT')
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit Route</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Route Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter Route Name" value="{{ old('name', $route->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="code" class="form-label">Route Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                                    name="code" placeholder="Route code will be auto-generated" 
                                    value="{{ old('code', $route->code) }}" 
                                    style="text-transform: uppercase;" required>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Code will be auto-generated based on route name and direction
                                </div>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="direction" class="form-label">Direction <span class="text-danger">*</span></label>
                                <select class="form-select @error('direction') is-invalid @enderror" id="direction" name="direction" required>
                                    <option value="">Select Direction</option>
                                    <option value="forward" {{ old('direction', $route->direction) == 'forward' ? 'selected' : '' }}>Forward</option>
                                    <option value="return" {{ old('direction', $route->direction) == 'return' ? 'selected' : '' }}>Return</option>
                                </select>
                                @error('direction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="is_return_of" class="form-label">Return Route Of</label>
                                <select class="form-select @error('is_return_of') is-invalid @enderror" id="is_return_of" name="is_return_of">
                                    <option value="">Select Return Route (Optional)</option>
                                    @foreach ($routes as $routeOption)
                                        <option value="{{ $routeOption->id }}" {{ old('is_return_of', $route->is_return_of) == $routeOption->id ? 'selected' : '' }}>
                                            {{ $routeOption->name }} ({{ $routeOption->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select if this route is a return route of another route</div>
                                @error('is_return_of')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="base_currency" class="form-label">Base Currency <span class="text-danger">*</span></label>
                                <select class="form-select @error('base_currency') is-invalid @enderror" id="base_currency" name="base_currency" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency }}" {{ old('base_currency', $route->base_currency) == $currency ? 'selected' : '' }}>
                                            {{ $currency }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('base_currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $route->status) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Route Stops Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bx bx-map me-2"></i>Route Stops
                                        </h5>
                                        <p class="text-muted mb-0">Manage terminals for this route</p>
                                    </div>
                                    <div class="card-body">
                                        <div id="stops-container">
                                            @foreach($route->routeStops->sortBy('sequence') as $stop)
                                                <div class="stop-item border rounded p-3 mb-3" data-stop-id="{{ $stop->id }}">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <h6 class="mb-0">
                                                            <i class="bx bx-map-pin me-2 text-primary"></i>
                                                            Stop {{ $stop->sequence }}
                                                        </h6>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
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
                                                        <div class="col-md-3">
                                                            <label class="form-label">Sequence <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control sequence-input" name="stops[{{ $stop->id }}][sequence]" 
                                                                   value="{{ $stop->sequence }}" min="1" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Distance (km)</label>
                                                            <input type="number" class="form-control distance-input" name="stops[{{ $stop->id }}][distance_from_previous]" 
                                                                   value="{{ $stop->distance_from_previous }}" placeholder="0.0" step="0.1" min="0">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Travel Time (min)</label>
                                                            <input type="number" class="form-control travel-time-input" name="stops[{{ $stop->id }}][approx_travel_time]" 
                                                                   value="{{ $stop->approx_travel_time }}" placeholder="0" min="0">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check mt-4">
                                                                <input class="form-check-input" type="checkbox" name="stops[{{ $stop->id }}][is_pickup_allowed]" 
                                                                       value="1" id="pickup_{{ $stop->id }}" {{ $stop->is_pickup_allowed ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="pickup_{{ $stop->id }}">
                                                                    Pickup Allowed
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check mt-4">
                                                                <input class="form-check-input" type="checkbox" name="stops[{{ $stop->id }}][is_dropoff_allowed]" 
                                                                       value="1" id="dropoff_{{ $stop->id }}" {{ $stop->is_dropoff_allowed ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="dropoff_{{ $stop->id }}">
                                                                    Dropoff Allowed
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-outline-primary" id="add-stop-btn">
                                                <i class="bx bx-plus"></i> Add Stop
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.routes.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Update Route
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
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">
                        <i class="bx bx-map-pin me-2 text-primary"></i>
                        Stop ${stopCounter}
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
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
                    <div class="col-md-3">
                        <label class="form-label">Sequence <span class="text-danger">*</span></label>
                        <input type="number" class="form-control sequence-input" name="stops[new_${stopCounter}][sequence]" 
                               value="${stopCounter}" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Distance (km)</label>
                        <input type="number" class="form-control distance-input" name="stops[new_${stopCounter}][distance_from_previous]" 
                               placeholder="0.0" step="0.1" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Travel Time (min)</label>
                        <input type="number" class="form-control travel-time-input" name="stops[new_${stopCounter}][approx_travel_time]" 
                               placeholder="0" min="0">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="stops[new_${stopCounter}][is_pickup_allowed]" 
                                   value="1" id="pickup_new_${stopCounter}" checked>
                            <label class="form-check-label" for="pickup_new_${stopCounter}">
                                Pickup Allowed
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="stops[new_${stopCounter}][is_dropoff_allowed]" 
                                   value="1" id="dropoff_new_${stopCounter}" checked>
                            <label class="form-check-label" for="dropoff_new_${stopCounter}">
                                Dropoff Allowed
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

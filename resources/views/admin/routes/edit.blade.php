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
        <div class="col-xl-6 mx-auto">
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
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-route me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
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
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
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
                                    Code will be auto-generated based on route name
                                </div>
                                @error('code')
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
                                    <div id="stops-container" class="row g-4">
                                        @foreach($route->routeStops->sortBy('sequence') as $stop)
                                            <div class="stop-item border rounded p-3 mb-1 col-md-4" data-stop-id="{{ $stop->id }}">
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
                                                <div class="row g-3">
                                                    <div class="col-md-12">
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
                                                    <input type="hidden" class="sequence-input" name="stops[{{ $stop->id }}][sequence]" value="{{ $stop->sequence }}">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="stops[{{ $stop->id }}][online_booking_allowed]" value="1" id="online_booking_{{ $stop->id }}" {{ $stop->online_booking_allowed ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="online_booking_{{ $stop->id }}">
                                                                Allow Online Booking from this stop
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
    const codeInput = document.getElementById('code');

    // Auto-generate code when name changes
    function generateRouteCode() {
        const name = nameInput.value.trim();

        if (name) {
            const code = generateCodeFromName(name);
            codeInput.value = code;
        } else {
            codeInput.value = '';
        }
    }

    // Generate code based on route name
    function generateCodeFromName(name) {
        // Extract city names from route name
        const cities = extractCitiesFromName(name);

        if (cities.length >= 2) {
            const fromCity = cities[0].substring(0, 3).toUpperCase();
            const toCity = cities[1].substring(0, 3).toUpperCase();
            return `${fromCity}-${toCity}`;
        } else if (cities.length === 1) {
            const city = cities[0].substring(0, 3).toUpperCase();
            return `${city}-ROUTE`;
        } else {
            // Generate code from route name initials
            const words = name.split(/\s+/);
            const initials = words.map(word => word.charAt(0).toUpperCase()).join('');
            return initials.length > 0 ? initials.substring(0, 8) : 'ROUTE';
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

    // Initialize code generation if there are existing values
    if (nameInput.value) {
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
                const stopItem = this.closest('.stop-item');
                const terminalSelect = stopItem.querySelector('.terminal-select');
                
                // Destroy Select2 before removing the element
                if (terminalSelect && $(terminalSelect).hasClass('select2-hidden-accessible')) {
                    $(terminalSelect).select2('destroy');
                }
                
                stopItem.remove();
                updateSequences();
            });
        });

        function addStop() {
            stopCounter++;
            const stopDiv = document.createElement('div');
            stopDiv.className = 'stop-item border rounded p-3 mb-1 col-md-4';
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
                <div class="row g-3">
                    <div class="col-md-12">
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
                    <input type="hidden" class="sequence-input" name="stops[new_${stopCounter}][sequence]" value="${stopCounter}">
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="stops[new_${stopCounter}][online_booking_allowed]" value="1" id="online_booking_new_${stopCounter}" checked>
                            <label class="form-check-label" for="online_booking_new_${stopCounter}">
                                Allow Online Booking from this stop
                            </label>
                        </div>
                    </div>
                </div>
            `;
            
            stopsContainer.appendChild(stopDiv);

            // Initialize Select2 for the new terminal select
            const terminalSelect = stopDiv.querySelector('.terminal-select');
            $(terminalSelect).select2({
                width: 'resolve',
                placeholder: 'Select Terminal'
            });

            // Add event listeners for this stop
            const removeBtn = stopDiv.querySelector('.remove-stop-btn');

            removeBtn.addEventListener('click', function() {
                // Destroy Select2 before removing the element
                $(terminalSelect).select2('destroy');
                stopDiv.remove();
                updateSequences();
            });
        }

        function updateSequences() {
            const stopItems = stopsContainer.querySelectorAll('.stop-item');
            stopItems.forEach((item, index) => {
                const sequenceInput = item.querySelector('.sequence-input');
                const badge = item.querySelector('.badge');
                const stopHeader = item.querySelector('.stop-header');
                if (sequenceInput) {
                    sequenceInput.value = index + 1;
                }
                if (badge) {
                    badge.textContent = index + 1;
                }
                if (stopHeader) {
                    stopHeader.textContent = `Stop ${index + 1}`;
                }
            });
        }

        // Initialize Select2 for existing terminal selects
        $('.terminal-select').select2({
            width: 'resolve',
            placeholder: 'Select Terminal'
        });
    });
</script>
@endsection

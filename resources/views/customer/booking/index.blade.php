@extends('frontend.layouts.app')

@section('title', 'Book Your Trip')

@section('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.form-select-lg, .form-control-lg {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-select-lg:focus, .form-control-lg:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    background: #6c757d;
    transform: none;
    box-shadow: none;
}

.form-label {
    color: #495057;
    margin-bottom: 0.5rem;
}

.text-primary {
    color: #667eea !important;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white py-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-bus me-2"></i>Book Your Trip
                            </h3>
                            <p class="mb-0 opacity-75">Find and book your perfect journey</p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-route fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('customer.booking.search') }}" id="bookingForm">
                        <!-- Step 1: Select Terminals -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>Select Your Journey
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="from_terminal_id" class="form-label fw-semibold">
                                        <i class="fas fa-play-circle text-success me-1"></i>From Terminal
                                    </label>
                                    <select name="from_terminal_id" id="from_terminal_id" class="form-select form-select-lg" required>
                                        <option value="">Select departure terminal...</option>
                                        @foreach($terminals as $terminal)
                                            <option value="{{ $terminal->id }}">
                                                {{ $terminal->name }} ({{ $terminal->city->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="to_terminal_id" class="form-label fw-semibold">
                                        <i class="fas fa-stop-circle text-danger me-1"></i>To Terminal
                                    </label>
                                    <select name="to_terminal_id" id="to_terminal_id" class="form-select form-select-lg" required>
                                        <option value="">Select destination terminal...</option>
                                        @foreach($terminals as $terminal)
                                            <option value="{{ $terminal->id }}">
                                                {{ $terminal->name }} ({{ $terminal->city->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Select Date and Route -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-calendar-alt me-2"></i>Choose Date & Route
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="travel_date" class="form-label fw-semibold">
                                        <i class="fas fa-calendar me-1"></i>Travel Date
                                    </label>
                                    <input type="date" name="travel_date" id="travel_date" 
                                           class="form-control form-control-lg" 
                                           min="{{ date('Y-m-d') }}" 
                                           value="{{ date('Y-m-d') }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="route_id" class="form-label fw-semibold">
                                        <i class="fas fa-route me-1"></i>Select Route
                                    </label>
                                    <select name="route_id" id="route_id" class="form-select form-select-lg" required disabled>
                                        <option value="">First select terminals and date...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for route stops -->
                        <input type="hidden" name="from_stop_id" id="from_stop_id">
                        <input type="hidden" name="to_stop_id" id="to_stop_id">

                        <!-- Search Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3" id="searchBtn" disabled>
                                <i class="fas fa-search me-2"></i>Search Available Trips
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromTerminalSelect = document.getElementById('from_terminal_id');
    const toTerminalSelect = document.getElementById('to_terminal_id');
    const travelDateInput = document.getElementById('travel_date');
    const routeSelect = document.getElementById('route_id');
    const searchBtn = document.getElementById('searchBtn');
    const fromStopInput = document.getElementById('from_stop_id');
    const toStopInput = document.getElementById('to_stop_id');

    // Function to check if all required fields are filled
    function checkFormValidity() {
        const fromTerminal = fromTerminalSelect.value;
        const toTerminal = toTerminalSelect.value;
        const travelDate = travelDateInput.value;
        const route = routeSelect.value;

        if (fromTerminal && toTerminal && travelDate && route && fromTerminal !== toTerminal) {
            searchBtn.disabled = false;
            return true;
        } else {
            searchBtn.disabled = true;
            return false;
        }
    }

    // Function to fetch available routes
    function fetchAvailableRoutes() {
        const fromTerminal = fromTerminalSelect.value;
        const toTerminal = toTerminalSelect.value;
        const travelDate = travelDateInput.value;

        if (!fromTerminal || !toTerminal || !travelDate) {
            routeSelect.innerHTML = '<option value="">First select terminals and date...</option>';
            routeSelect.disabled = true;
            checkFormValidity();
            return;
        }

        if (fromTerminal === toTerminal) {
            routeSelect.innerHTML = '<option value="">Please select different terminals</option>';
            routeSelect.disabled = true;
            checkFormValidity();
            return;
        }

        // Show loading state
        routeSelect.innerHTML = '<option value=""><span class="loading-spinner me-2"></span>Loading available routes...</option>';
        routeSelect.disabled = true;

        // Fetch available routes
        const params = new URLSearchParams({
            from_terminal_id: fromTerminal,
            to_terminal_id: toTerminal,
            travel_date: travelDate
        });

        fetch(`/api/booking/available-routes?${params}`)
            .then(response => response.json())
            .then(data => {
                routeSelect.innerHTML = '<option value="">Select a route...</option>';
                
                if (data.success && data.routes.length > 0) {
                    data.routes.forEach(routeData => {
                        const route = routeData.route;
                        const fromStop = routeData.from_stop;
                        const toStop = routeData.to_stop;
                        const timetablesCount = routeData.timetables_count;
                        
                        const option = new Option(
                            `${route.name} (${route.code}) - ${timetablesCount} trip(s) available`,
                            route.id
                        );
                        option.dataset.fromStopId = fromStop.id;
                        option.dataset.toStopId = toStop.id;
                        routeSelect.appendChild(option);
                    });
                    routeSelect.disabled = false;
                } else {
                    routeSelect.innerHTML = '<option value="">No routes available for this route</option>';
                    routeSelect.disabled = true;
                }
                
                checkFormValidity();
            })
            .catch(error => {
                console.error('Error fetching available routes:', error);
                routeSelect.innerHTML = '<option value="">Error loading routes</option>';
                routeSelect.disabled = true;
                checkFormValidity();
            });
    }

    // Event listeners
    fromTerminalSelect.addEventListener('change', function() {
        // Add visual feedback
        this.classList.add('border-success');
        setTimeout(() => this.classList.remove('border-success'), 1000);
        fetchAvailableRoutes();
    });
    
    toTerminalSelect.addEventListener('change', function() {
        // Add visual feedback
        this.classList.add('border-success');
        setTimeout(() => this.classList.remove('border-success'), 1000);
        fetchAvailableRoutes();
    });
    
    travelDateInput.addEventListener('change', function() {
        // Add visual feedback
        this.classList.add('border-success');
        setTimeout(() => this.classList.remove('border-success'), 1000);
        fetchAvailableRoutes();
    });

    routeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.fromStopId) {
            fromStopInput.value = selectedOption.dataset.fromStopId;
            toStopInput.value = selectedOption.dataset.toStopId;
        } else {
            fromStopInput.value = '';
            toStopInput.value = '';
        }
        checkFormValidity();
    });

    // Initial check
    checkFormValidity();
});
</script>
@endsection

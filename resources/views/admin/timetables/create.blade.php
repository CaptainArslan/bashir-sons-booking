@extends('admin.layouts.app')

@section('title', 'Generate Timetables')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Generate Timetables</h4>
                    <p class="text-muted mb-0">Create multiple timetables for a selected route</p>
                </div>
                
                <div class="card-body">
                    <form id="timetable-form" method="POST" action="{{ route('admin.timetables.store') }}">
                        @csrf
                        
                        <!-- Back Button -->
                        <div class="mb-3">
                            <a href="{{ route('admin.timetables.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Timetables
                            </a>
                        </div>
                        
                        <!-- Route Selection -->
                        <div class="mb-3">
                            <label for="route_id" class="form-label">Select Route</label>
                            <select name="route_id" id="route_id" class="form-select" required>
                                <option value="">Choose a route...</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route['id'] }}" data-stops="{{ json_encode($route['stops']) }}">
                                        {{ $route['name'] }} ({{ $route['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('route_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Route Information -->
                        <div id="route-info" class="alert alert-info" style="display: none;">
                            <h6>Route Information</h6>
                            <div id="stops-list"></div>
                        </div>
                        
                        <!-- Number of Departures -->
                        <div class="mb-3">
                            <label for="departure_count" class="form-label">Number of Departures</label>
                            <input type="number" 
                                   name="departure_count" 
                                   id="departure_count" 
                                   class="form-control" 
                                   min="1" 
                                   max="10" 
                                   value="1"
                                   required>
                            <div class="form-text">Enter how many timetables you want to create (1-10)</div>
                            @error('departure_count')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Timetable Details Section -->
                        <div id="timetable-details" style="display: none;">
                            <h5 class="mb-3">Timetable Details</h5>
                            <div id="timetable-inputs"></div>
                        </div>
                        
                        <!-- Generate Button -->
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100" id="generate-btn">
                                Generate Timetables
                            </button>
                        </div>
                    </form>
                    
                    <!-- Loading Spinner -->
                    <div id="loading-spinner" class="text-center" style="display: none;">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Generating...</span>
                        </div>
                        <p class="mt-2 text-muted">Generating timetables, please wait...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentStops = [];
    
    // Route selection change handler
    $('#route_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const stops = selectedOption.data('stops');
        
        if (stops && stops.length > 0) {
            currentStops = stops;
            let stopsHtml = '';
            stops.forEach(function(stop, index) {
                stopsHtml += `<span class="badge bg-secondary me-1 mb-1">${index + 1}. ${stop.name}</span>`;
            });
            
            $('#stops-list').html(stopsHtml);
            $('#route-info').show();
            generateTimetableInputs();
        } else {
            $('#route-info').hide();
            $('#timetable-details').hide();
            currentStops = [];
        }
    });
    
    // Departure count change handler
    $('#departure_count').on('input', function() {
        generateTimetableInputs();
    });
    
    // Generate timetable inputs based on route and departure count
    function generateTimetableInputs() {
        const departureCount = parseInt($('#departure_count').val()) || 0;
        
        if (currentStops.length === 0 || departureCount === 0) {
            $('#timetable-details').hide();
            return;
        }
        
        let timetableHtml = '';
        
        for (let i = 1; i <= departureCount; i++) {
            timetableHtml += `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Timetable ${i}</h6>
                    </div>
                    <div class="card-body">
            `;
            
            currentStops.forEach(function(stop, index) {
                const isStartStop = index === 0;
                const isEndStop = index === currentStops.length - 1;
                
                timetableHtml += `
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="fw-bold">${stop.name}</div>
                            <small class="text-muted">Stop ${index + 1}${isStartStop ? ' (Start)' : (isEndStop ? ' (End)' : '')}</small>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                `;
                
                // Add arrival time input first (not for start stop)
                if (!isStartStop) {
                    timetableHtml += `
                        <div class="col-md-6">
                            <label class="form-label">Arrival Time</label>
                            <input type="time" name="timetables[${i-1}][stops][${index}][arrival_time]" class="form-control" required>
                        </div>
                    `;
                }
                
                // Add departure time input second (not for end stop)
                if (!isEndStop) {
                    timetableHtml += `
                        <div class="col-md-6">
                            <label class="form-label">Departure Time</label>
                            <input type="time" name="timetables[${i-1}][stops][${index}][departure_time]" class="form-control" required>
                        </div>
                    `;
                }
                
                // Add hidden fields for stop data
                timetableHtml += `
                                <input type="hidden" name="timetables[${i-1}][stops][${index}][stop_id]" value="${stop.id}">
                                <input type="hidden" name="timetables[${i-1}][stops][${index}][sequence]" value="${index + 1}">
                            </div>
                        </div>
                    </div>
                `;
            });
            
            timetableHtml += '</div></div>';
        }
        
        $('#timetable-inputs').html(timetableHtml);
        $('#timetable-details').show();
    }
    
    // Form submission handler
    $('#timetable-form').submit(function(e) {
        const departureCount = $('#departure_count').val();
        const routeId = $('#route_id').val();
        
        if (!routeId) {
            e.preventDefault();
            alert('Please select a route first.');
            return false;
        }
        
        if (!departureCount || departureCount < 1 || departureCount > 10) {
            e.preventDefault();
            alert('Please enter a valid number of departures (1-10).');
            return false;
        }
        
        // Validate that all time inputs are filled
        const timeInputs = $('input[type="time"]');
        let allFilled = true;
        
        timeInputs.each(function() {
            if (!$(this).val()) {
                allFilled = false;
                return false;
            }
        });
        
        if (!allFilled) {
            e.preventDefault();
            alert('Please fill in all arrival and departure times.');
            return false;
        }
        
        // Show loading spinner
        $('#generate-btn').prop('disabled', true);
        $('#loading-spinner').show();
        $('.card-body').hide();
    });
    
    // Real-time validation
    $('#departure_count').on('input', function() {
        const value = $(this).val();
        if (value < 1 || value > 10) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endsection

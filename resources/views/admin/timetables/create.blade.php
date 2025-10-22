@extends('admin.layouts.app')

@section('title', 'Generate Timetables')
@section('styles')
    <style>
        /* Consistent Timetables Styling */
        .timetables-header {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .timetables-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
        }
        
        .timetables-header p {
            margin: 0.25rem 0 0 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .back-btn {
            background: #6c757d;
            border: 1px solid #6c757d;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: #5a6268;
            border-color: #5a6268;
            color: white;
        }
        
        .form-container {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .card-header {
            background: #e9ecef;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
        }
        
        .card-header h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: #495057;
        }
        
        .card-header p {
            margin: 0.5rem 0 0 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-select, .form-control {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .btn-primary {
            background: #007bff;
            border-color: #007bff;
            color: white;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        .btn-primary:disabled {
            background: #6c757d;
            border-color: #6c757d;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            border-radius: 4px;
        }
        
        .badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            border-radius: 3px;
        }
        
        .timetable-card {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .timetable-card .card-header {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
        }
        
        .timetable-card .card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #495057;
        }
        
        .timetable-card .card-body {
            padding: 1rem;
        }
        
        .stop-row {
            border-bottom: 1px solid #f1f3f4;
            padding: 0.75rem 0;
        }
        
        .stop-row:last-child {
            border-bottom: none;
        }
        
        .stop-name {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        
        .stop-type {
            font-size: 0.8rem;
            color: #6c757d;
            font-style: italic;
        }
        
        .time-input-group {
            margin-bottom: 0.5rem;
        }
        
        .time-input-group label {
            font-size: 0.8rem;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        .spinner-border {
            width: 2rem;
            height: 2rem;
        }
        
        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .text-danger {
            font-size: 0.8rem;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Consistent Header -->
    <div class="timetables-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-time me-2"></i>Generate Timetables</h4>
                <p>Create multiple timetables for a selected route with detailed scheduling</p>
            </div>
            <div>
                <a href="{{ route('admin.timetables.index') }}" class="back-btn">
                    <i class="bx bx-arrow-back me-1"></i>Back to Timetables
                </a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="card-header">
            <h4>Timetable Generation</h4>
            <p>Select a route and configure departure times for each stop</p>
        </div>
        
        <div class="card-body">
            <form id="timetable-form" method="POST" action="{{ route('admin.timetables.store') }}">
                @csrf
                
                <!-- Route Selection and Departure Count in Single Line -->
                <div class="row mb-3">
                    <div class="col-md-8">
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
                    <div class="col-md-4">
                        <label for="departure_count" class="form-label">Number of Departures</label>
                        <input type="number" 
                               name="departure_count" 
                               id="departure_count" 
                               class="form-control" 
                               min="1" 
                               max="10" 
                               value="1"
                               required>
                        @error('departure_count')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Route Information -->
                <div id="route-info" class="alert alert-info mb-3" style="display: none;">
                    <h6>Route Information</h6>
                    <div id="stops-list"></div>
                </div>
                
                <!-- Timetable Details Section -->
                <div id="timetable-details" style="display: none;">
                    <h5 class="mb-3">Timetable Details</h5>
                    <div id="timetable-inputs"></div>
                </div>
                
                <!-- Generate Button -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100" id="generate-btn">
                        <i class="bx bx-plus me-2"></i>Generate Timetables
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
                <div class="timetable-card">
                    <div class="card-header">
                        <h6>Timetable ${i}</h6>
                    </div>
                    <div class="card-body">
            `;
            
            currentStops.forEach(function(stop, index) {
                const isStartStop = index === 0;
                const isEndStop = index === currentStops.length - 1;
                
                timetableHtml += `
                    <div class="stop-row">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stop-name">${stop.name}</div>
                                <div class="stop-type">Stop ${index + 1}${isStartStop ? ' (Starting Point)' : (isEndStop ? ' (Final Destination)' : ' (Intermediate)')}</div>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                `;
                
                // Add arrival time input first (not for start stop)
                if (!isStartStop) {
                    timetableHtml += `
                        <div class="col-md-6">
                            <div class="time-input-group">
                                <label class="form-label">Arrival Time</label>
                                <input type="time" name="timetables[${i-1}][stops][${index}][arrival_time]" class="form-control" required>
                            </div>
                        </div>
                    `;
                }
                
                // Add departure time input second (not for end stop)
                if (!isEndStop) {
                    timetableHtml += `
                        <div class="col-md-6">
                            <div class="time-input-group">
                                <label class="form-label">Departure Time</label>
                                <input type="time" name="timetables[${i-1}][stops][${index}][departure_time]" class="form-control" required>
                            </div>
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

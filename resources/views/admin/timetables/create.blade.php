@extends('admin.layouts.app')

@section('title', 'Generate Timetables')
@section('styles')
    <style>
        .timetable-generator {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .generator-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .generator-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .generator-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .form-section {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .route-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            display: none;
        }
        
        .route-info h6 {
            color: #495057;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .stops-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .stop-item {
            padding: 0.5rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }
        
        .stop-item:last-child {
            border-bottom: none;
        }
        
        .btn-generate {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn-generate:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-back {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #667eea;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="timetable-generator">
                <!-- Header -->
                <div class="generator-header">
                    <h4><i class="fas fa-clock me-2"></i>Generate Timetables</h4>
                    <p>Create multiple timetables for a selected route with automatic time intervals</p>
                </div>
                
                <!-- Form Section -->
                <div class="form-section">
                    <form id="timetable-form" method="POST" action="{{ route('admin.timetables.store') }}">
                        @csrf
                        
                        <!-- Back Button -->
                        <div class="mb-3">
                            <a href="{{ route('admin.timetables.index') }}" class="btn-back">
                                <i class="fas fa-arrow-left me-1"></i>Back to Timetables
                            </a>
                        </div>
                        
                        <!-- Route Selection -->
                        <div class="form-group">
                            <label for="route_id" class="form-label">
                                <i class="fas fa-route me-1"></i>Select Route
                            </label>
                            <select name="route_id" id="route_id" class="form-control" required>
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
                        <div id="route-info" class="route-info">
                            <h6><i class="fas fa-info-circle me-1"></i>Route Information</h6>
                            <div id="stops-list" class="stops-list"></div>
                        </div>
                        
                        <!-- Number of Departures -->
                        <div class="form-group">
                            <label for="departure_count" class="form-label">
                                <i class="fas fa-hashtag me-1"></i>Number of Departures
                            </label>
                            <input type="number" 
                                   name="departure_count" 
                                   id="departure_count" 
                                   class="form-control" 
                                   min="1" 
                                   max="50" 
                                   value="5"
                                   required>
                            <small class="form-text text-muted">Enter how many timetables you want to generate (1-50)</small>
                            @error('departure_count')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Start Time -->
                        <div class="form-group">
                            <label for="start_time" class="form-label">
                                <i class="fas fa-clock me-1"></i>First Departure Time
                            </label>
                            <input type="time" 
                                   name="start_time" 
                                   id="start_time" 
                                   class="form-control" 
                                   value="06:00"
                                   required>
                            <small class="form-text text-muted">The departure time for the first timetable</small>
                            @error('start_time')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Time Interval -->
                        <div class="form-group">
                            <label for="time_interval" class="form-label">
                                <i class="fas fa-stopwatch me-1"></i>Time Interval (minutes)
                            </label>
                            <select name="time_interval" id="time_interval" class="form-control" required>
                                <option value="30">30 minutes</option>
                                <option value="60" selected>1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                                <option value="180">3 hours</option>
                                <option value="240">4 hours</option>
                            </select>
                            <small class="form-text text-muted">Interval between each departure</small>
                            @error('time_interval')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Generate Button -->
                        <div class="form-group">
                            <button type="submit" class="btn-generate" id="generate-btn">
                                <i class="fas fa-magic me-2"></i>Generate Timetables
                            </button>
                        </div>
                    </form>
                    
                    <!-- Loading Spinner -->
                    <div id="loading-spinner" class="loading-spinner">
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
    // Route selection change handler
    $('#route_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const stops = selectedOption.data('stops');
        
        if (stops && stops.length > 0) {
            let stopsHtml = '';
            stops.forEach(function(stop, index) {
                stopsHtml += `
                    <div class="stop-item">
                        <strong>${index + 1}.</strong> ${stop.name}
                    </div>
                `;
            });
            
            $('#stops-list').html(stopsHtml);
            $('#route-info').show();
        } else {
            $('#route-info').hide();
        }
    });
    
    // Form submission handler
    $('#timetable-form').submit(function(e) {
        const departureCount = $('#departure_count').val();
        const routeId = $('#route_id').val();
        
        if (!routeId) {
            e.preventDefault();
            alert('Please select a route first.');
            return false;
        }
        
        if (!departureCount || departureCount < 1 || departureCount > 50) {
            e.preventDefault();
            alert('Please enter a valid number of departures (1-50).');
            return false;
        }
        
        // Show loading spinner
        $('#generate-btn').prop('disabled', true);
        $('#loading-spinner').show();
        $('.form-section').hide();
    });
    
    // Real-time validation
    $('#departure_count').on('input', function() {
        const value = $(this).val();
        if (value < 1 || value > 50) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endsection

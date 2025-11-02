@extends('admin.layouts.app')

@section('title', 'Edit Timetable')
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
        
        .stops-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .stops-table th {
            background: #f8f9fa;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stops-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .stops-table tr:hover {
            background: #f8f9fa;
        }
        
        .sequence-badge {
            background: #007bff;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
            display: inline-block;
        }
        
        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        .form-check-label {
            font-weight: 500;
            color: #495057;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Consistent Header -->
    <div class="timetables-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-edit me-2"></i>Edit Timetable</h4>
                <p>Modify timetable details and schedule times for each stop</p>
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
            <h4>Timetable Information</h4>
            <p>Update timetable details and stop schedule times</p>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.timetables.update', $timetable->id) }}">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="mb-4">
                    <h5 class="mb-3">Basic Information</h5>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="name" class="form-label">Timetable Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       class="form-control" 
                                       value="{{ old('name', $timetable->name) }}"
                                       placeholder="Enter timetable name">
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_departure_time" class="form-label">
                                    Start Departure Time 
                                    <small class="text-muted">(Auto-calculated)</small>
                                </label>
                                @php
                                    // Get raw database value to avoid accessor formatting
                                    $rawStartTime = $timetable->getRawOriginal('start_departure_time');
                                    $startTimeFormatted = $rawStartTime ? \Carbon\Carbon::parse($rawStartTime)->format('H:i') : '';
                                @endphp
                                <input type="text" 
                                       id="start_departure_time_display" 
                                       class="form-control" 
                                       value="{{ $startTimeFormatted }}"
                                       readonly
                                       style="background-color: #e9ecef; cursor: not-allowed;">
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Automatically set from first stop's departure time
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_arrival_time" class="form-label">
                                    End Arrival Time 
                                    <small class="text-muted">(Auto-calculated)</small>
                                </label>
                                @php
                                    // Get raw database value to avoid accessor formatting
                                    $rawEndTime = $timetable->getRawOriginal('end_arrival_time');
                                    $endTimeFormatted = $rawEndTime ? \Carbon\Carbon::parse($rawEndTime)->format('H:i') : '';
                                @endphp
                                <input type="text" 
                                       id="end_arrival_time_display" 
                                       class="form-control" 
                                       value="{{ $endTimeFormatted }}"
                                       readonly
                                       style="background-color: #e9ecef; cursor: not-allowed;">
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Automatically set from last stop's arrival time
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   class="form-check-input" 
                                   value="1"
                                   {{ old('is_active', $timetable->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Active Timetable</label>
                        </div>
                        @error('is_active')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Stops Schedule Editor -->
                <div class="mb-4">
                    <h5 class="mb-3">Stop Schedule</h5>
                    <div class="table-responsive">
                        <table class="stops-table">
                            <thead>
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="30%">Terminal</th>
                                    <th width="30%">Arrival Time</th>
                                    <th width="30%">Departure Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timetableStops as $index => $stop)
                                <tr>
                                    <td>
                                        <span class="sequence-badge">{{ $stop->sequence }}</span>
                                    </td>
                                    <td>
                                        <div class="stop-name">{{ $stop->terminal->name }}</div>
                                        <div class="stop-type">{{ $stop->terminal->city->name ?? 'N/A' }}</div>
                                        <input type="hidden" name="stops[{{ $index }}][id]" value="{{ $stop->id }}">
                                    </td>
                                    <td>
                                        @if($index === 0)
                                            <input type="hidden" name="stops[{{ $index }}][arrival_time]" value="">
                                            <input type="time" 
                                                   class="form-control" 
                                                   value=""
                                                   disabled
                                                   style="background-color: #e9ecef; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="bx bx-info-circle me-1"></i>First stop - no arrival time
                                            </div>
                                        @else
                                            @php
                                                // Get raw database value to avoid accessor formatting
                                                $rawArrivalTime = $stop->getRawOriginal('arrival_time');
                                                $arrivalTimeFormatted = $rawArrivalTime ? \Carbon\Carbon::parse($rawArrivalTime)->format('H:i') : '';
                                            @endphp
                                            <input type="time" 
                                                   name="stops[{{ $index }}][arrival_time]" 
                                                   class="form-control stop-arrival-time" 
                                                   data-stop-index="{{ $index }}"
                                                   value="{{ old('stops.' . $index . '.arrival_time', $arrivalTimeFormatted) }}">
                                        @endif
                                    </td>
                                    <td>
                                        @if($index === $timetableStops->count() - 1)
                                            <input type="hidden" name="stops[{{ $index }}][departure_time]" value="">
                                            <input type="time" 
                                                   class="form-control" 
                                                   value=""
                                                   disabled
                                                   style="background-color: #e9ecef; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="bx bx-info-circle me-1"></i>Last stop - no departure time
                                            </div>
                                        @else
                                            @php
                                                // Get raw database value to avoid accessor formatting
                                                $rawDepartureTime = $stop->getRawOriginal('departure_time');
                                                $departureTimeFormatted = $rawDepartureTime ? \Carbon\Carbon::parse($rawDepartureTime)->format('H:i') : '';
                                            @endphp
                                            <input type="time" 
                                                   name="stops[{{ $index }}][departure_time]" 
                                                   class="form-control stop-departure-time" 
                                                   data-stop-index="{{ $index }}"
                                                   value="{{ old('stops.' . $index . '.departure_time', $departureTimeFormatted) }}">
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Save Button -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-save me-2"></i>Update Timetable
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Function to update start and end times based on stop inputs
    function updateTimetableTimes() {
        // Get first stop's departure time (start_departure_time)
        const firstStopDeparture = $('input[name="stops[0][departure_time]"]').val();
        if (firstStopDeparture) {
            $('#start_departure_time_display').val(firstStopDeparture);
        } else {
            $('#start_departure_time_display').val('');
        }
        
        // Get last stop's arrival time (end_arrival_time)
        const lastStopIndex = {{ $timetableStops->count() - 1 }};
        const lastStopArrival = $(`input[name="stops[${lastStopIndex}][arrival_time]"]`).val();
        if (lastStopArrival) {
            $('#end_arrival_time_display').val(lastStopArrival);
        } else {
            $('#end_arrival_time_display').val('');
        }
    }
    
    // Update times when stop times change
    $(document).on('change', '.stop-departure-time, .stop-arrival-time', function() {
        updateTimetableTimes();
    });
    
    // Also update when first stop departure changes
    $(document).on('change', 'input[name="stops[0][departure_time]"]', function() {
        updateTimetableTimes();
    });
    
    // Initial update on page load
    updateTimetableTimes();
    
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        const errors = [];
        
        // Check if first stop has departure time
        const firstStopDeparture = $('input[name="stops[0][departure_time]"]').val();
        if (!firstStopDeparture) {
            errors.push('First stop must have a departure time.');
            isValid = false;
            $('input[name="stops[0][departure_time]"]').addClass('is-invalid');
        } else {
            $('input[name="stops[0][departure_time]"]').removeClass('is-invalid');
        }
        
        // Validate that times are logical (arrival before departure for intermediate stops)
        $('.stop-arrival-time, .stop-departure-time, input[name*="[departure_time]"]').each(function() {
            const stopIndex = $(this).data('stop-index');
            if (stopIndex !== undefined && stopIndex > 0 && stopIndex < {{ $timetableStops->count() - 1 }}) {
                const arrivalTime = $(`input[name="stops[${stopIndex}][arrival_time]"]`).val();
                const departureTime = $(`input[name="stops[${stopIndex}][departure_time]"]`).val();
                
                if (arrivalTime && departureTime) {
                    if (arrivalTime >= departureTime) {
                        errors.push(`Stop ${parseInt(stopIndex) + 1}: Arrival time must be before departure time.`);
                        isValid = false;
                        $(`input[name="stops[${stopIndex}][arrival_time]"]`).addClass('is-invalid');
                        $(`input[name="stops[${stopIndex}][departure_time]"]`).addClass('is-invalid');
                    }
                }
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: 'Please fix the following errors:<br><ul style="text-align: left;"><li>' + errors.join('</li><li>') + '</li></ul>',
                confirmButtonText: 'OK'
            });
        }
    });
    
    // Real-time validation for time inputs
    $(document).on('change', '.stop-arrival-time, .stop-departure-time, input[name*="[departure_time]"]', function() {
        const time = $(this).val();
        if (time) {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endsection

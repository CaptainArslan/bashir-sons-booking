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
                                    <th width="25%">Terminal</th>
                                    <th width="25%">Arrival Time <small class="text-muted">(Optional for first stop)</small></th>
                                    <th width="25%">Departure Time</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timetableStops as $index => $stop)
                                <tr id="stop-row-{{ $stop->id }}" class="{{ !$stop->is_active ? 'table-secondary opacity-75' : '' }}">
                                    <td>
                                        <span class="sequence-badge">{{ $stop->sequence }}</span>
                                        @if(!$stop->is_active)
                                            <span class="badge bg-danger ms-1" title="Disabled">OFF</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="stop-name">{{ $stop->terminal->name }}</div>
                                        <div class="stop-type">{{ $stop->terminal->city->name ?? 'N/A' }}</div>
                                        <input type="hidden" name="stops[{{ $index }}][id]" value="{{ $stop->id }}">
                                    </td>
                                    <td>
                                        @php
                                            // Get raw database value to avoid accessor formatting
                                            $rawArrivalTime = $stop->getRawOriginal('arrival_time');
                                            $arrivalTimeFormatted = $rawArrivalTime ? \Carbon\Carbon::parse($rawArrivalTime)->format('H:i') : '';
                                            $isFirstStop = $index === 0;
                                        @endphp
                                        <input type="time" 
                                               name="stops[{{ $index }}][arrival_time]" 
                                               class="form-control stop-arrival-time {{ $isFirstStop ? 'first-stop-arrival' : '' }}" 
                                               data-stop-index="{{ $index }}"
                                               value="{{ old('stops.' . $index . '.arrival_time', $arrivalTimeFormatted) }}"
                                               {{ !$stop->is_active ? 'disabled' : '' }}
                                               {{ !$isFirstStop ? 'required' : '' }}>
                                        @if($isFirstStop)
                                            <div class="form-text text-muted">
                                                <i class="bx bx-info-circle me-1"></i>Optional - leave empty if not needed
                                            </div>
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
                                                   value="{{ old('stops.' . $index . '.departure_time', $departureTimeFormatted) }}"
                                                   {{ !$stop->is_active ? 'disabled' : '' }}>
                                        @endif
                                    </td>
                                    <td>
                                        @can('edit timetables')
                                            @php
                                                $isFirstStop = $index === 0;
                                                $isLastStop = $index === $timetableStops->count() - 1;
                                                $canToggle = !$isFirstStop && !$isLastStop;
                                            @endphp
                                            @if($canToggle)
                                                <button type="button" 
                                                        class="btn btn-sm {{ $stop->is_active ? 'btn-warning' : 'btn-success' }} toggle-stop-btn" 
                                                        data-stop-id="{{ $stop->id }}"
                                                        data-stop-name="{{ $stop->terminal->name }}"
                                                        data-is-active="{{ $stop->is_active ? '1' : '0' }}"
                                                        title="{{ $stop->is_active ? 'Disable Stop' : 'Enable Stop' }}">
                                                    <i class="bx {{ $stop->is_active ? 'bx-pause' : 'bx-play' }}"></i>
                                                </button>
                                            @else
                                                <span class="text-muted" title="First and last stops cannot be disabled">
                                                    <i class="bx bx-lock"></i>
                                                </span>
                                            @endif
                                        @endcan
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
        
        // Validate that times are logical (arrival before departure)
        // First stop's arrival time is optional, but if provided, should be before departure
        $('.stop-arrival-time, .stop-departure-time, input[name*="[departure_time]"]').each(function() {
            const stopIndex = $(this).data('stop-index');
            if (stopIndex !== undefined) {
                const arrivalTime = $(`input[name="stops[${stopIndex}][arrival_time]"]`).val();
                const departureTime = $(`input[name="stops[${stopIndex}][departure_time]"]`).val();
                const isFirstStop = stopIndex === 0;
                const isLastStop = stopIndex === {{ $timetableStops->count() - 1 }};
                
                // For first stop: arrival is optional, but if provided, must be before departure
                if (isFirstStop && arrivalTime && departureTime) {
                    if (arrivalTime >= departureTime) {
                        errors.push(`First stop: Arrival time must be before departure time.`);
                        isValid = false;
                        $(`input[name="stops[${stopIndex}][arrival_time]"]`).addClass('is-invalid');
                        $(`input[name="stops[${stopIndex}][departure_time]"]`).addClass('is-invalid');
                    }
                }
                // For intermediate stops: arrival is required and must be before departure
                else if (!isFirstStop && !isLastStop) {
                    if (!arrivalTime) {
                        errors.push(`Stop ${parseInt(stopIndex) + 1}: Arrival time is required.`);
                        isValid = false;
                        $(`input[name="stops[${stopIndex}][arrival_time]"]`).addClass('is-invalid');
                    } else if (arrivalTime && departureTime && arrivalTime >= departureTime) {
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
    
    // Toggle timetable stop status
    $(document).on('click', '.toggle-stop-btn', function() {
        const btn = $(this);
        const stopId = btn.data('stop-id');
        const stopName = btn.data('stop-name');
        const isActive = btn.data('is-active') === '1';
        const action = isActive ? 'disable' : 'enable';
        const timetableId = {{ $timetable->id }};
        
        Swal.fire({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Stop?`,
            text: `Are you sure you want to ${action} "${stopName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel',
            confirmButtonColor: isActive ? '#dc3545' : '#28a745',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const url = `/admin/timetables/{{ $timetable->id }}/stops/${stopId}/toggle-status`;
                return fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _method: 'PATCH'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Failed to update stop status');
                        });
                    }
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                if (data.success) {
                    // Update button state
                    const newIsActive = data.is_active;
                    btn.data('is-active', newIsActive ? '1' : '0');
                    
                    // Update button appearance
                    if (newIsActive) {
                        btn.removeClass('btn-success').addClass('btn-warning');
                        btn.find('i').removeClass('bx-play').addClass('bx-pause');
                        btn.attr('title', 'Disable Stop');
                    } else {
                        btn.removeClass('btn-warning').addClass('btn-success');
                        btn.find('i').removeClass('bx-pause').addClass('bx-play');
                        btn.attr('title', 'Enable Stop');
                    }
                    
                    // Update row appearance
                    const row = $(`#stop-row-${stopId}`);
                    if (newIsActive) {
                        row.removeClass('table-secondary opacity-75');
                        row.find('.badge.bg-danger').remove();
                        row.find('input[type="time"]').not(':disabled').prop('disabled', false);
                    } else {
                        row.addClass('table-secondary opacity-75');
                        row.find('td:first').append('<span class="badge bg-danger ms-1" title="Disabled">OFF</span>');
                        row.find('input[type="time"]').not(':disabled').prop('disabled', true);
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to update stop status'
                    });
                }
            }
        });
    });
});
</script>
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit Timetable')
@section('styles')
    <style>
        .timetable-editor {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .editor-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
        }
        
        .editor-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .editor-content {
            padding: 2rem;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
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
        
        .stops-editor {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .stops-editor th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
        }
        
        .stops-editor td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }
        
        .stops-editor tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        .stops-editor tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .time-input {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
            width: 100%;
        }
        
        .time-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .sequence-badge {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }
        
        .btn-save {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn-back {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
    <div class="row">
        <div class="col-lg-12">
            <div class="timetable-editor">
                <!-- Header -->
                <div class="editor-header">
                    <h4><i class="fas fa-edit me-2"></i>Edit Timetable</h4>
                </div>
                
                <!-- Content -->
                <div class="editor-content">
                    <!-- Back Button -->
                    <a href="{{ route('admin.timetables.show', $timetable->id) }}" class="btn-back">
                        <i class="fas fa-arrow-left me-1"></i>Back to Timetable Details
                    </a>
                    
                    <form method="POST" action="{{ route('admin.timetables.update', $timetable->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
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
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="start_departure_time" class="form-label">Start Departure Time</label>
                                        <input type="time" 
                                               name="start_departure_time" 
                                               id="start_departure_time" 
                                               class="form-control" 
                                               value="{{ old('start_departure_time', $timetable->start_departure_time) }}"
                                               required>
                                        @error('start_departure_time')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="end_arrival_time" class="form-label">End Arrival Time</label>
                                        <input type="time" 
                                               name="end_arrival_time" 
                                               id="end_arrival_time" 
                                               class="form-control" 
                                               value="{{ old('end_arrival_time', $timetable->end_arrival_time) }}">
                                        @error('end_arrival_time')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-wrapper">
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
                        <div class="stops-editor">
                            <h5 class="p-3 mb-0"><i class="fas fa-map-marker-alt me-2"></i>Stop Schedule</h5>
                            <table class="table table-hover mb-0">
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
                                            <strong>{{ $stop->terminal->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $stop->terminal->city->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   name="stops[{{ $index }}][arrival_time]" 
                                                   class="time-input" 
                                                   value="{{ old('stops.' . $index . '.arrival_time', $stop->arrival_time) }}"
                                                   {{ $index === 0 ? 'readonly' : '' }}>
                                            <input type="hidden" name="stops[{{ $index }}][id]" value="{{ $stop->id }}">
                                            @if($index === 0)
                                                <small class="text-muted d-block">First stop - no arrival time</small>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   name="stops[{{ $index }}][departure_time]" 
                                                   class="time-input" 
                                                   value="{{ old('stops.' . $index . '.departure_time', $stop->departure_time) }}"
                                                   {{ $index === $timetableStops->count() - 1 ? 'readonly' : '' }}>
                                            @if($index === $timetableStops->count() - 1)
                                                <small class="text-muted d-block">Last stop - no departure time</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Save Button -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save me-2"></i>Update Timetable
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate end arrival time based on last stop
    $('input[name*="[arrival_time]"]').on('change', function() {
        const lastStopIndex = {{ $timetableStops->count() - 1 }};
        const lastArrivalTime = $(`input[name="stops[${lastStopIndex}][arrival_time]"]`).val();
        
        if (lastArrivalTime) {
            $('#end_arrival_time').val(lastArrivalTime);
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        const errors = [];
        
        // Check if start departure time is set
        const startTime = $('#start_departure_time').val();
        if (!startTime) {
            errors.push('Start departure time is required.');
            isValid = false;
        }
        
        // Check if at least one stop has times set
        let hasStopTimes = false;
        $('input[name*="[arrival_time]"], input[name*="[departure_time]"]').each(function() {
            if ($(this).val() && !$(this).prop('readonly')) {
                hasStopTimes = true;
                return false;
            }
        });
        
        if (!hasStopTimes) {
            errors.push('Please set arrival or departure times for at least one stop.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n' + errors.join('\n'));
        }
    });
    
    // Real-time validation for time inputs
    $('.time-input').on('change', function() {
        const time = $(this).val();
        if (time) {
            $(this).removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid');
        }
    });
});
</script>
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit Stop Times')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Stop Times for: {{ $routeTimetable->trip_code }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Timetable
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.route-stop-times.update', $routeTimetable) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> Instructions</h5>
                                    <ul class="mb-0">
                                        <li>Modify the stop times as needed</li>
                                        <li>Sequence numbers must be consecutive starting from 1</li>
                                        <li>Only stops where online booking is allowed will be available for customer bookings</li>
                                        <li>Times should be in 24-hour format (HH:MM)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10%">Sequence</th>
                                        <th width="25%">Terminal</th>
                                        <th width="15%">Arrival Time</th>
                                        <th width="15%">Departure Time</th>
                                        <th width="15%">Online Booking</th>
                                        <th width="20%">Distance/Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stopTimes->sortBy('sequence') as $index => $stopTime)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="stop_times[{{ $index }}][id]" 
                                                       value="{{ $stopTime->id }}">
                                                <input type="hidden" name="stop_times[{{ $index }}][route_stop_id]" 
                                                       value="{{ $stopTime->route_stop_id }}">
                                                <input type="number" name="stop_times[{{ $index }}][sequence]" 
                                                       class="form-control sequence-input" 
                                                       value="{{ $stopTime->sequence }}" 
                                                       min="1" required>
                                            </td>
                                            <td>
                                                <strong>{{ $stopTime->routeStop->terminal->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $stopTime->routeStop->terminal->city->name }}</small>
                                            </td>
                                            <td>
                                                <input type="time" name="stop_times[{{ $index }}][arrival_time]" 
                                                       class="form-control"
                                                       value="{{ $stopTime->arrival_time }}">
                                            </td>
                                            <td>
                                                <input type="time" name="stop_times[{{ $index }}][departure_time]" 
                                                       class="form-control"
                                                       value="{{ $stopTime->departure_time }}">
                                            </td>
                                            <td>
                                                <select name="stop_times[{{ $index }}][allow_online_booking]" class="form-control">
                                                    <option value="1" {{ $stopTime->allow_online_booking ? 'selected' : '' }}>Allowed</option>
                                                    <option value="0" {{ !$stopTime->allow_online_booking ? 'selected' : '' }}>Not Allowed</option>
                                                </select>
                                            </td>
                                            <td>
                                                @if($stopTime->routeStop->distance_from_previous)
                                                    <small>{{ $stopTime->routeStop->distance_from_previous }} km</small><br>
                                                @endif
                                                @if($stopTime->routeStop->approx_travel_time)
                                                    <small>{{ $stopTime->routeStop->approx_travel_time }} min</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Stop Times
                        </button>
                        <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-danger float-right" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Delete All Stop Times
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sequenceInputs = document.querySelectorAll('.sequence-input');
    
    // Auto-update sequences to be consecutive
    function updateSequences() {
        const rows = Array.from(sequenceInputs).map((input, index) => ({
            input: input,
            currentValue: parseInt(input.value) || 999,
            index: index
        }));
        
        // Sort by current sequence value
        rows.sort((a, b) => a.currentValue - b.currentValue);
        
        // Assign consecutive sequences
        rows.forEach((row, index) => {
            row.input.value = index + 1;
        });
    }
    
    // Update sequences when inputs change
    sequenceInputs.forEach(input => {
        input.addEventListener('change', updateSequences);
    });
    
    // Initial sequence update
    updateSequences();
});

function confirmDelete() {
    if (confirm('Are you sure you want to delete all stop times for this timetable? This action cannot be undone.')) {
        // Create a form to delete all stop times
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.route-stop-times.destroy", $routeTimetable) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

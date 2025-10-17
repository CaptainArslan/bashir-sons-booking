@extends('admin.layouts.app')

@section('title', 'Add Stop Times')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Stop Times for: {{ $routeTimetable->trip_code }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Timetable
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.route-stop-times.store', $routeTimetable) }}">
                    @csrf
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> Instructions</h5>
                                    <ul class="mb-0">
                                        <li>Select the stops for this timetable and set their arrival/departure times</li>
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
                                        <th width="5%">Select</th>
                                        <th width="10%">Sequence</th>
                                        <th width="25%">Terminal</th>
                                        <th width="15%">Arrival Time</th>
                                        <th width="15%">Departure Time</th>
                                        <th width="15%">Online Booking</th>
                                        <th width="15%">Distance/Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($routeTimetable->route->routeStops->sortBy('sequence') as $index => $routeStop)
                                        @php
                                            $existingStopTime = $existingStopTimes->get($routeStop->id);
                                            $generatedStopTime = $generatedStopTimes[$index] ?? null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="stop_times[{{ $index }}][selected]" 
                                                       class="stop-checkbox" value="1"
                                                       {{ $existingStopTime || $generatedStopTime ? 'checked' : '' }}>
                                                <input type="hidden" name="stop_times[{{ $index }}][route_stop_id]" 
                                                       value="{{ $routeStop->id }}">
                                            </td>
                                            <td>
                                                <input type="number" name="stop_times[{{ $index }}][sequence]" 
                                                       class="form-control sequence-input" 
                                                       value="{{ $existingStopTime?->sequence ?? $generatedStopTime['sequence'] ?? $routeStop->sequence }}" 
                                                       min="1" required>
                                            </td>
                                            <td>
                                                <strong>{{ $routeStop->terminal->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $routeStop->terminal->city->name }}</small>
                                            </td>
                                            <td>
                                                <input type="time" name="stop_times[{{ $index }}][arrival_time]" 
                                                       class="form-control"
                                                       value="{{ $existingStopTime?->arrival_time ?? $generatedStopTime['arrival_time'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="time" name="stop_times[{{ $index }}][departure_time]" 
                                                       class="form-control"
                                                       value="{{ $existingStopTime?->departure_time ?? $generatedStopTime['departure_time'] ?? '' }}">
                                            </td>
                                            <td>
                                                <select name="stop_times[{{ $index }}][allow_online_booking]" class="form-control">
                                                    <option value="1" {{ ($existingStopTime?->allow_online_booking ?? $generatedStopTime['allow_online_booking'] ?? true) ? 'selected' : '' }}>Allowed</option>
                                                    <option value="0" {{ !($existingStopTime?->allow_online_booking ?? $generatedStopTime['allow_online_booking'] ?? true) ? 'selected' : '' }}>Not Allowed</option>
                                                </select>
                                            </td>
                                            <td>
                                                @if($routeStop->distance_from_previous)
                                                    <small>{{ $routeStop->distance_from_previous }} km</small><br>
                                                @endif
                                                @if($routeStop->approx_travel_time)
                                                    <small>{{ $routeStop->approx_travel_time }} min</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(isset($generatedStopTimes))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-success">
                                        <h5><i class="fas fa-magic"></i> Auto-Generated Times</h5>
                                        <p class="mb-0">Stop times have been automatically generated based on the route's travel times. You can modify them as needed.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Save Stop Times
                        </button>
                        <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const checkboxes = document.querySelectorAll('.stop-checkbox');
    const sequenceInputs = document.querySelectorAll('.sequence-input');
    
    // Handle checkbox changes
    checkboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const inputs = row.querySelectorAll('input[type="time"], input[type="number"], select');
            
            if (this.checked) {
                inputs.forEach(input => input.disabled = false);
            } else {
                inputs.forEach(input => input.disabled = true);
                // Clear values when unchecked
                inputs.forEach(input => {
                    if (input.type === 'time' || input.type === 'number') {
                        input.value = '';
                    } else if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    }
                });
            }
        });
        
        // Initial state
        checkbox.dispatchEvent(new Event('change'));
    });
    
    // Auto-update sequences based on checked items
    function updateSequences() {
        const checkedRows = [];
        checkboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                checkedRows.push({
                    checkbox: checkbox,
                    sequenceInput: sequenceInputs[index],
                    index: index
                });
            }
        });
        
        // Sort by current sequence value
        checkedRows.sort((a, b) => {
            const aSeq = parseInt(a.sequenceInput.value) || 999;
            const bSeq = parseInt(b.sequenceInput.value) || 999;
            return aSeq - bSeq;
        });
        
        // Assign consecutive sequences
        checkedRows.forEach((row, index) => {
            row.sequenceInput.value = index + 1;
        });
    }
    
    // Update sequences when checkboxes change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSequences);
    });
    
    // Update sequences when sequence inputs change
    sequenceInputs.forEach(input => {
        input.addEventListener('change', updateSequences);
    });
    
    // Initial sequence update
    updateSequences();
    
    // Handle form submission - remove unchecked rows
    form.addEventListener('submit', function(e) {
        // Remove all inputs from unchecked rows
        checkboxes.forEach((checkbox, index) => {
            if (!checkbox.checked) {
                const row = checkbox.closest('tr');
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.remove();
                });
            }
        });
    });
});
</script>
@endsection

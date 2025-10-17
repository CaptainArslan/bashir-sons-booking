@extends('admin.layouts.app')

@section('title', 'Add Stop Times')

@section('styles')
<style>
    .stop-times-card {
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
    
    .info-card {
        border-left: 3px solid #0dcaf0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        margin-bottom: 1rem;
    }
    
    .info-card .card-header {
        background: linear-gradient(135deg, #0dcaf0 0%, #17a2b8 100%);
        color: white;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .info-card .card-body {
        padding: 0.75rem;
    }
    
    .stats-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
    }
    
    .instructions-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-left: 4px solid #2196f3;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    
    .instructions-box h6 {
        margin: 0 0 0.5rem 0;
        font-weight: 600;
        color: #1976d2;
    }
    
    .instructions-box ul {
        margin: 0;
        padding-left: 1.2rem;
    }
    
    .instructions-box li {
        margin-bottom: 0.25rem;
        font-size: 0.85rem;
        color: #1976d2;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .table th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.75rem 0.5rem;
    }
    
    .table td {
        padding: 0.75rem 0.5rem;
        vertical-align: middle;
        font-size: 0.85rem;
    }
    
    .form-control, .form-select {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
        border-radius: 4px;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .suggested-time-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
    }
    
    .use-time-btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.route-timetables.index') }}">Route Timetables</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.route-timetables.show', $routeTimetable) }}">{{ $routeTimetable->trip_code }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Stop Times</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-12 mx-auto">
            <div class="card stop-times-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-time me-2"></i>Add Stop Times for: {{ $routeTimetable->trip_code }}</h5>
                </div>

                <form method="POST" action="{{ route('admin.route-stop-times.store', $routeTimetable) }}">
                    @csrf
                    <div class="card-body">
                        <!-- Route and Timetable Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card info-card">
                                    <div class="card-header">
                                        <i class="bx bx-route me-2"></i>Route Information
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Route:</strong> 
                                                    <span class="text-primary">{{ $routeTimetable->route->name }}</span>
                                                </p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Code:</strong> 
                                                    <span class="text-info">{{ $routeTimetable->route->code }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Stops:</strong> 
                                                    <span class="badge bg-success stats-badge">{{ $routeTimetable->route->routeStops->count() }}</span>
                                                </p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Distance:</strong> 
                                                    <span class="text-warning">{{ $routeTimetable->route->totalDistance ?? 'N/A' }} km</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Direction:</strong> 
                                                    <span class="text-success">{{ $routeTimetable->route->firstTerminal?->name ?? 'Start' }}</span> 
                                                    <i class="bx bx-right-arrow-alt mx-1"></i>
                                                    <span class="text-danger">{{ $routeTimetable->route->lastTerminal?->name ?? 'End' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card info-card">
                                    <div class="card-header">
                                        <i class="bx bx-time me-2"></i>Timetable Information
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Trip Code:</strong> 
                                                    <span class="text-primary">{{ $routeTimetable->trip_code }}</span>
                                                </p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Frequency:</strong> 
                                                    <span class="badge bg-secondary stats-badge">{{ $routeTimetable->frequency->getName() }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Departure:</strong> 
                                                    <span class="badge bg-info stats-badge">{{ $routeTimetable->departure_time }}</span>
                                                </p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Arrival:</strong> 
                                                    @if($routeTimetable->arrival_time)
                                                        <span class="badge bg-success stats-badge">{{ $routeTimetable->arrival_time }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Status:</strong> 
                                                    <span class="badge bg-{{ $routeTimetable->is_active ? 'success' : 'danger' }} stats-badge">
                                                        {{ $routeTimetable->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1" style="font-size: 0.8rem;">
                                                    <strong>Created:</strong> 
                                                    <small class="text-muted">{{ $routeTimetable->created_at->format('M d, Y') }}</small>
                                                </p>
                                            </div>
                                        </div>
                                        @if($routeTimetable->frequency->value === 'custom' && $routeTimetable->operating_days)
                                            <div class="row">
                                                <div class="col-12">
                                                    <p class="mb-1" style="font-size: 0.8rem;">
                                                        <strong>Operating Days:</strong><br>
                                                        @foreach($routeTimetable->operating_days as $day)
                                                            <span class="badge bg-secondary me-1" style="font-size: 0.65rem;">{{ ucfirst($day) }}</span>
                                                        @endforeach
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="instructions-box">
                            <h6><i class="bx bx-info-circle me-1"></i>Setup Instructions</h6>
                            <ul>
                                <li><strong>Select Stops:</strong> Check the stops you want to include in this timetable</li>
                                <li><strong>Base Time:</strong> Start with departure time {{ $routeTimetable->departure_time }} for the first stop</li>
                                <li><strong>Time Flow:</strong> Each stop should have a later time than the previous stop</li>
                                <li><strong>Quick Help:</strong> Use the "Use" buttons to apply suggested times, then adjust manually</li>
                                <li><strong>Sequence:</strong> Numbers will be automatically assigned (1, 2, 3...)</li>
                                <li><strong>Booking:</strong> Enable online booking for stops where customers can book tickets</li>
                            </ul>
                        </div>

                        <!-- Stops Table -->
                        <div class="table-container">
                            <div class="p-3">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="5%">Select</th>
                                                <th width="8%">Sequence</th>
                                                <th width="20%">Terminal</th>
                                                <th width="12%">Arrival Time</th>
                                                <th width="12%">Departure Time</th>
                                                <th width="12%">Online Booking</th>
                                                <th width="15%">Distance/Time</th>
                                                <th width="16%">Suggested Time</th>
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
                                                       value="{{ $routeStop->id }}"
                                                       data-original-sequence="{{ $routeStop->sequence }}">
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
                                            <td>
                                                @php
                                                    $suggestedTime = null;
                                                    try {
                                                        if ($index === 0) {
                                                            // First stop - use timetable departure time
                                                            $suggestedTime = $routeTimetable->departure_time;
                                                        } else {
                                                            // Calculate based on previous stop's travel time
                                                            $previousStop = $routeTimetable->route->routeStops->sortBy('sequence')->values()[$index - 1];
                                                            $travelTime = $previousStop->approx_travel_time ?? 30;
                                                            
                                                            // Parse the departure time safely
                                                            $departureTime = $routeTimetable->departure_time;
                                                            if (is_string($departureTime)) {
                                                                $baseTime = \Carbon\Carbon::createFromFormat('H:i:s', $departureTime);
                                                            } else {
                                                                $baseTime = \Carbon\Carbon::parse($departureTime);
                                                            }
                                                            
                                                            // Calculate cumulative travel time
                                                            $cumulativeMinutes = 0;
                                                            for ($i = 0; $i < $index; $i++) {
                                                                $stop = $routeTimetable->route->routeStops->sortBy('sequence')->values()[$i];
                                                                $cumulativeMinutes += $stop->approx_travel_time ?? 30;
                                                            }
                                                            
                                                            $suggestedTime = $baseTime->addMinutes($cumulativeMinutes)->format('H:i');
                                                        }
                                                    } catch (\Exception $e) {
                                                        // Fallback to a simple time calculation
                                                        $baseHour = 8; // Default 8 AM
                                                        $baseMinute = 0;
                                                        
                                                        if ($routeTimetable->departure_time) {
                                                            $timeParts = explode(':', $routeTimetable->departure_time);
                                                            if (count($timeParts) >= 2) {
                                                                $baseHour = (int) $timeParts[0];
                                                                $baseMinute = (int) $timeParts[1];
                                                            }
                                                        }
                                                        
                                                        $totalMinutes = $baseHour * 60 + $baseMinute;
                                                        $cumulativeMinutes = 0;
                                                        
                                                        for ($i = 0; $i < $index; $i++) {
                                                            $stop = $routeTimetable->route->routeStops->sortBy('sequence')->values()[$i];
                                                            $cumulativeMinutes += $stop->approx_travel_time ?? 30;
                                                        }
                                                        
                                                        $totalMinutes += $cumulativeMinutes;
                                                        $suggestedHour = intval($totalMinutes / 60) % 24;
                                                        $suggestedMinute = $totalMinutes % 60;
                                                        $suggestedTime = sprintf('%02d:%02d', $suggestedHour, $suggestedMinute);
                                                    }
                                                @endphp
                                                <div class="text-center">
                                                    <span class="badge bg-light text-dark suggested-time-badge">{{ $suggestedTime }}</span>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-outline-primary mt-1 use-time-btn" 
                                                            onclick="setSuggestedTime({{ $index }}, '{{ $suggestedTime }}')">
                                                        <i class="bx bx-time"></i> Use
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if(isset($generatedStopTimes))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-success">
                                        <h6><i class="bx bx-magic-wand me-1"></i>Auto-Generated Times</h6>
                                        <p class="mb-0" style="font-size: 0.85rem;">Stop times have been automatically generated based on the route's travel times. You can modify them as needed.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Timetable
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                    <i class="bx bx-save me-1"></i>Save Stop Times
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
                    index: index,
                    row: checkbox.closest('tr')
                });
            }
        });
        
        // Sort by original route stop sequence (not user input)
        checkedRows.sort((a, b) => {
            const aOriginalSeq = parseInt(a.row.querySelector('input[name*="[route_stop_id]"]').getAttribute('data-original-sequence')) || a.index;
            const bOriginalSeq = parseInt(b.row.querySelector('input[name*="[route_stop_id]"]').getAttribute('data-original-sequence')) || b.index;
            return aOriginalSeq - bOriginalSeq;
        });
        
        // Assign consecutive sequences starting from 1
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
    
    // Function to set suggested time
    window.setSuggestedTime = function(index, suggestedTime) {
        const row = document.querySelector(`input[name="stop_times[${index}][selected]"]`).closest('tr');
        const departureTimeInput = row.querySelector('input[name*="[departure_time]"]');
        const arrivalTimeInput = row.querySelector('input[name*="[arrival_time]"]');
        
        // Set departure time to suggested time
        departureTimeInput.value = suggestedTime;
        
        // If this is not the first stop, also set arrival time
        if (index > 0) {
            arrivalTimeInput.value = suggestedTime;
        }
        
        // Visual feedback
        row.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            row.style.backgroundColor = '';
        }, 1000);
    };
    
    // Handle form submission - remove unchecked rows and validate
    form.addEventListener('submit', function(e) {
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Please select at least one stop.');
            return false;
        }
        
        // Validate that all checked rows have required times
        let hasErrors = false;
        checkboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                const row = checkbox.closest('tr');
                const departureTime = row.querySelector('input[name*="[departure_time]"]').value;
                const arrivalTime = row.querySelector('input[name*="[arrival_time]"]').value;
                
                if (!departureTime && !arrivalTime) {
                    hasErrors = true;
                    row.style.backgroundColor = '#ffebee';
                } else {
                    row.style.backgroundColor = '';
                }
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            alert('Please provide at least arrival time or departure time for all selected stops.');
            return false;
        }
        
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
        
        // Re-index the remaining inputs to ensure proper array structure
        const remainingRows = Array.from(document.querySelectorAll('tr')).filter(tr => 
            tr.querySelector('.stop-checkbox') && tr.querySelector('.stop-checkbox').checked
        );
        
        remainingRows.forEach((row, newIndex) => {
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/\[\d+\]/, `[${newIndex}]`);
                }
            });
        });
    });
});
</script>
@endsection

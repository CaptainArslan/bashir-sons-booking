@extends('admin.layouts.app')

@section('title', 'Edit Route Timetable')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Route Timetable: {{ $routeTimetable->trip_code }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('admin.route-timetables.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.route-timetables.update', $routeTimetable) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_id">Route *</label>
                                    <select name="route_id" id="route_id" class="form-control @error('route_id') is-invalid @enderror" required>
                                        <option value="">Select Route</option>
                                        @foreach($routes as $route)
                                            <option value="{{ $route->id }}" {{ old('route_id', $routeTimetable->route_id) == $route->id ? 'selected' : '' }}>
                                                {{ $route->name }} ({{ $route->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('route_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trip_code">Trip Code *</label>
                                    <input type="text" name="trip_code" id="trip_code" 
                                           class="form-control @error('trip_code') is-invalid @enderror" 
                                           value="{{ old('trip_code', $routeTimetable->trip_code) }}" required>
                                    @error('trip_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Unique identifier for this scheduled trip</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="departure_time">Departure Time *</label>
                                    <input type="time" name="departure_time" id="departure_time" 
                                           class="form-control @error('departure_time') is-invalid @enderror" 
                                           value="{{ old('departure_time', $routeTimetable->departure_time) }}" required>
                                    @error('departure_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Departure time from the first terminal</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="arrival_time">Arrival Time</label>
                                    <input type="time" name="arrival_time" id="arrival_time" 
                                           class="form-control @error('arrival_time') is-invalid @enderror" 
                                           value="{{ old('arrival_time', $routeTimetable->arrival_time) }}">
                                    @error('arrival_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Expected arrival time at the last terminal</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="frequency">Frequency *</label>
                                    <select name="frequency" id="frequency" class="form-control @error('frequency') is-invalid @enderror" required>
                                        @foreach($frequencyTypes as $frequency)
                                            <option value="{{ $frequency->value }}" {{ old('frequency', $routeTimetable->frequency->value) == $frequency->value ? 'selected' : '' }}>
                                                {{ $frequency->getName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('frequency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <select name="is_active" id="is_active" class="form-control">
                                        <option value="1" {{ old('is_active', $routeTimetable->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $routeTimetable->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Operating Days (shown only when frequency is custom) -->
                        <div class="row" id="operating-days-section" style="display: none;">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Operating Days</label>
                                    <div class="row">
                                        @php
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            $selectedDays = old('operating_days', $routeTimetable->operating_days ?? []);
                                        @endphp
                                        @foreach($days as $day)
                                            <div class="col-md-3 col-sm-6">
                                                <div class="form-check">
                                                    <input type="checkbox" name="operating_days[]" value="{{ $day }}" 
                                                           id="day_{{ $day }}" class="form-check-input"
                                                           {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_{{ $day }}">
                                                        {{ ucfirst($day) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('operating_days')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h4 class="card-title">Route Information</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Route:</strong> {{ $routeTimetable->route->name }} ({{ $routeTimetable->route->code }})<br>
                                                <strong>Total Stops:</strong> {{ $routeTimetable->route->totalStops }}<br>
                                                <strong>Total Distance:</strong> {{ $routeTimetable->route->totalDistance }} km
                                            </div>
                                            <div class="col-md-6">
                                                <strong>First Terminal:</strong> {{ $routeTimetable->route->firstTerminal?->name }}<br>
                                                <strong>Last Terminal:</strong> {{ $routeTimetable->route->lastTerminal?->name }}<br>
                                                <strong>Total Travel Time:</strong> {{ $routeTimetable->route->totalTravelTime }} minutes
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Timetable
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
    const frequencySelect = document.getElementById('frequency');
    const operatingDaysSection = document.getElementById('operating-days-section');
    
    function toggleOperatingDays() {
        if (frequencySelect.value === 'custom') {
            operatingDaysSection.style.display = 'block';
        } else {
            operatingDaysSection.style.display = 'none';
        }
    }
    
    frequencySelect.addEventListener('change', toggleOperatingDays);
    toggleOperatingDays(); // Initial call
});
</script>
@endsection

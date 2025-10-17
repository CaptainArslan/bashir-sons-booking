@extends('admin.layouts.app')

@section('title', 'Edit Route Timetable')

@section('styles')
<style>
    .timetable-card {
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
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .form-control, .form-select {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .row {
        margin-bottom: 0.5rem;
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
    }
    
    .timetable-info-card {
        border-left: 3px solid #0dcaf0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .stats-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
    }
    
    .info-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-left: 4px solid #2196f3;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    
    .info-box p {
        margin: 0;
        font-size: 0.85rem;
        color: #1976d2;
    }
    
    .section-divider {
        border-top: 1px solid #e9ecef;
        margin: 1rem 0;
        padding-top: 1rem;
    }
    
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    .operating-days-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        border-left: 4px solid #0dcaf0;
    }
    
    .day-checkbox {
        background: #ffffff;
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 3px solid #0d6efd;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
    }
    
    .day-checkbox:hover {
        background: #e9ecef;
        transform: translateX(2px);
    }
    
    .day-checkbox .form-check-input:checked ~ .form-check-label {
        color: #0d6efd;
        font-weight: 600;
    }
    
    .day-checkbox .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
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
                    <li class="breadcrumb-item active" aria-current="page">Edit Timetable</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card timetable-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Route Timetable: {{ $routeTimetable->trip_code }}</h5>
                </div>

                <form method="POST" action="{{ route('admin.route-timetables.update', $routeTimetable) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Updating timetable information will affect all bookings and stop times. Please review carefully before saving changes.</p>
                        </div>
                        
                        <!-- Timetable Information Card -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card timetable-info-card">
                                    <div class="card-body" style="padding: 0.75rem;">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Timetable ID:</strong> 
                                                    <span class="badge bg-secondary">{{ $routeTimetable->id }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Trip Code:</strong> 
                                                    <span class="badge bg-primary">{{ $routeTimetable->trip_code }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Current Status:</strong> 
                                                    <span class="badge bg-{{ $routeTimetable->is_active ? 'success' : 'danger' }} stats-badge">
                                                        {{ $routeTimetable->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Frequency:</strong> 
                                                    <span class="badge bg-{{ $routeTimetable->frequency->getFrequencyTypeColor($routeTimetable->frequency->value) }} stats-badge">
                                                        {{ $routeTimetable->frequency->getName() }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Stops:</strong> 
                                                    <span class="badge bg-info">{{ $routeTimetable->stops->count() }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Created:</strong> 
                                                    {{ $routeTimetable->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-time me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="route_id" class="form-label">
                                    Route 
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="route_id" 
                                        id="route_id" 
                                        class="form-select @error('route_id') is-invalid @enderror" 
                                        required
                                        autofocus>
                                    <option value="">Select Route</option>
                                    @foreach($routes as $route)
                                        <option value="{{ $route->id }}" {{ old('route_id', $routeTimetable->route_id) == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('route_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="trip_code" class="form-label">
                                    Trip Code 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="trip_code" 
                                       id="trip_code" 
                                       class="form-control @error('trip_code') is-invalid @enderror" 
                                       value="{{ old('trip_code', $routeTimetable->trip_code) }}" 
                                       placeholder="Enter unique trip code"
                                       required>
                                @error('trip_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Unique identifier for this scheduled trip</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="departure_time" class="form-label">
                                    Departure Time 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       name="departure_time" 
                                       id="departure_time" 
                                       class="form-control @error('departure_time') is-invalid @enderror" 
                                       value="{{ old('departure_time', $routeTimetable->departure_time) }}" 
                                       required>
                                @error('departure_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Departure time from the first terminal</div>
                            </div>

                            <div class="col-md-6">
                                <label for="arrival_time" class="form-label">Arrival Time</label>
                                <input type="time" 
                                       name="arrival_time" 
                                       id="arrival_time" 
                                       class="form-control @error('arrival_time') is-invalid @enderror" 
                                       value="{{ old('arrival_time', $routeTimetable->arrival_time) }}"
                                       placeholder="Optional">
                                @error('arrival_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Expected arrival time at the last terminal (optional)</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="frequency" class="form-label">
                                    Frequency 
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="frequency" 
                                        id="frequency" 
                                        class="form-select @error('frequency') is-invalid @enderror" 
                                        required>
                                    @foreach($frequencyTypes as $frequency)
                                        <option value="{{ $frequency->value }}" {{ old('frequency', $routeTimetable->frequency->value) == $frequency->value ? 'selected' : '' }}>
                                            {{ $frequency->getName() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('frequency')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="is_active" class="form-label">Status</label>
                                <select name="is_active" 
                                        id="is_active" 
                                        class="form-select">
                                    <option value="1" {{ old('is_active', $routeTimetable->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $routeTimetable->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="form-text">Set timetable as active or inactive</div>
                            </div>
                        </div>

                        <!-- Operating Days Section -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-calendar me-1"></i>Operating Days
                        </div>
                        
                        <div class="operating-days-section" id="operating-days-section" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-calendar text-primary me-2" style="font-size: 1.2rem;"></i>
                                    <h6 class="mb-0 fw-bold text-primary">Select Operating Days</h6>
                                </div>
                            </div>
                            
                            <div class="row">
                                @php
                                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                    $selectedDays = old('operating_days', $routeTimetable->operating_days ?? []);
                                @endphp
                                @foreach($days as $day)
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <div class="day-checkbox">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="operating_days[]" 
                                                       value="{{ $day }}" 
                                                       id="day_{{ $day }}" 
                                                       class="form-check-input"
                                                       {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="day_{{ $day }}">
                                                    <i class="bx bx-calendar-check me-1"></i>{{ ucfirst($day) }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('operating_days')
                                <div class="text-danger d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the days when this timetable operates</div>
                        </div>

                        <!-- Route Information Card -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-route me-1"></i>Route Information
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card timetable-info-card">
                                    <div class="card-body" style="padding: 0.75rem;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Route:</strong> 
                                                    <span class="badge bg-primary">{{ $routeTimetable->route->name }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Code:</strong> 
                                                    <span class="badge bg-info">{{ $routeTimetable->route->code }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Stops:</strong> 
                                                    <span class="badge bg-success">{{ $routeTimetable->route->totalStops ?? 'N/A' }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Distance:</strong> 
                                                    <span class="badge bg-warning">{{ $routeTimetable->route->totalDistance ?? 'N/A' }} km</span>
                                                </p>
                                            </div>
                                            <div class="col-md-2">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Travel Time:</strong> 
                                                    <span class="badge bg-secondary">{{ $routeTimetable->route->totalTravelTime ?? 'N/A' }} min</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.route-timetables.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.route-timetables.show', $routeTimetable) }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Update Timetable
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const frequencySelect = document.getElementById('frequency');
    const operatingDaysSection = document.getElementById('operating-days-section');
    
    function toggleOperatingDays() {
        if (frequencySelect.value === 'custom') {
            operatingDaysSection.style.display = 'block';
            // Remove required attribute from individual checkboxes
            const checkboxes = operatingDaysSection.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.removeAttribute('required');
            });
        } else {
            operatingDaysSection.style.display = 'none';
            // Remove required attribute when not custom
            const checkboxes = operatingDaysSection.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.removeAttribute('required');
            });
        }
    }
    
    frequencySelect.addEventListener('change', toggleOperatingDays);
    toggleOperatingDays(); // Initial call
    
    // Add form validation for operating days
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (frequencySelect.value === 'custom') {
            const checkboxes = operatingDaysSection.querySelectorAll('input[type="checkbox"]');
            const checkedBoxes = operatingDaysSection.querySelectorAll('input[type="checkbox"]:checked');
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one operating day when using custom frequency.');
                return false;
            }
        }
    });
});
</script>
@endsection

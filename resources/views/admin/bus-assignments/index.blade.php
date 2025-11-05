@extends('admin.layouts.app')

@section('title', 'Bus Assignments - Terminal Segments')

@section('content')
<div class="container-fluid p-4">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-bus"></i> Bus Assignments - Terminal Segment Management
                </h5>
                @if(request()->has('trip_id'))
                    <a href="{{ route('admin.bus-assignments.create', ['trip_id' => request('trip_id')]) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus-circle"></i> Assign Bus to Segment
                    </a>
                @endif
            </div>
        </div>

        <!-- Trip Selection -->
        <div class="card-body bg-light">
            <form method="GET" action="{{ route('admin.bus-assignments.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Select Trip</label>
                    <select class="form-select" name="trip_id" id="tripSelect" required onchange="this.form.submit()">
                        <option value="">-- Select a Trip --</option>
                        @if(isset($recentTrips))
                            @foreach($recentTrips as $tripOption)
                                <option value="{{ $tripOption->id }}" {{ request('trip_id') == $tripOption->id ? 'selected' : '' }}>
                                    {{ $tripOption->route->name ?? 'N/A' }} - {{ $tripOption->departure_date->format('M d, Y') }} {{ $tripOption->departure_datetime->format('H:i') }}
                                </option>
                            @endforeach
                        @endif
                        @if(request()->has('trip_id') && isset($trip) && isset($recentTrips) && !$recentTrips->contains('id', $trip->id))
                            <option value="{{ $trip->id }}" selected>
                                {{ $trip->route->name ?? 'N/A' }} - {{ $trip->departure_date->format('M d, Y') }} {{ $trip->departure_datetime->format('H:i') }}
                            </option>
                        @endif
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Load Trip
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(request()->has('trip_id') && isset($trip))
        <!-- Trip Information -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Trip Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Route:</strong> {{ $trip->route->name ?? 'N/A' }}<br>
                        <strong>Date:</strong> {{ $trip->departure_date->format('M d, Y') }}<br>
                        <strong>Departure:</strong> {{ $trip->departure_datetime->format('H:i') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Total Stops:</strong> {{ $trip->stops->count() }}<br>
                        <strong>Assignments:</strong> {{ $trip->busAssignments->count() }}<br>
                        <strong>Status:</strong> <span class="badge bg-{{ $trip->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($trip->status) }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Trip Stops:</strong>
                        <div class="mt-2">
                            @foreach($trip->stops as $stop)
                                <span class="badge bg-secondary me-1 mb-1">
                                    {{ $stop->terminal->code }} (Seq: {{ $stop->sequence }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bus Assignments Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fas fa-list"></i> Segment-Wise Bus Assignments
                </h6>
            </div>
            <div class="card-body">
                @if($trip->busAssignments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-route"></i> Segment</th>
                                    <th><i class="fas fa-bus"></i> Bus</th>
                                    <th><i class="fas fa-user-tie"></i> Driver</th>
                                    <th><i class="fas fa-phone"></i> Driver Contact</th>
                                    <th><i class="fas fa-user-friends"></i> Host/Attendant</th>
                                    <th><i class="fas fa-phone"></i> Host Contact</th>
                                    <th><i class="fas fa-user"></i> Assigned By</th>
                                    <th><i class="fas fa-cog"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trip->busAssignments->sortBy('fromTripStop.sequence') as $assignment)
                                    <tr>
                                        <td>
                                            <strong>{{ $assignment->segment_label }}</strong><br>
                                            <small class="text-muted">
                                                {{ $assignment->fromTripStop->terminal->name }} → {{ $assignment->toTripStop->terminal->name }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ $assignment->bus->name ?? 'N/A' }}<br>
                                            <small class="text-muted">{{ $assignment->bus->registration_number ?? '' }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $assignment->driver_name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">License: {{ $assignment->driver_license ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            {{ $assignment->driver_phone ?? 'N/A' }}<br>
                                            @if($assignment->driver_cnic)
                                                <small class="text-muted">CNIC: {{ $assignment->driver_cnic }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $assignment->host_name ?? 'Not Assigned' }}
                                        </td>
                                        <td>
                                            {{ $assignment->host_phone ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $assignment->assignedBy->name ?? 'N/A' }}<br>
                                            <small class="text-muted">{{ $assignment->assigned_at?->format('M d, Y H:i') ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.bus-assignments.edit', $assignment) }}" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="deleteAssignment({{ $assignment->id }})" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3" style="font-size: 3rem; opacity: 0.3;">
                            <i class="fas fa-bus-slash"></i>
                        </div>
                        <p class="text-muted mb-0">No bus assignments found for this trip.</p>
                        <small class="text-muted">Assign buses to terminal segments to get started.</small>
                        <div class="mt-3">
                            <a href="{{ route('admin.bus-assignments.create', ['trip_id' => $trip->id]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Create First Assignment
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-3" style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-bus"></i>
                </div>
                <p class="text-muted mb-0">Please select a trip to view and manage bus assignments.</p>
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this bus assignment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function deleteAssignment(id) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/bus-assignments/${id}`;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Load trips for dropdown (if needed)
    // You can add AJAX here to load trips dynamically
</script>
@endsection


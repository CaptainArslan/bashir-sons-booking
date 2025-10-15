@extends('admin.layouts.app')

@section('title', 'Manage Route Stops')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manage Stops - {{ $route->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @can('create routes')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStopModal">
                    <i class="bx bx-plus"></i> Add Stop
                </button>
            @endcan
        </div>
    </div>
    <!--end breadcrumb-->

    <!-- Route Information Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="card-title mb-3">Route Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Route Name:</strong> {{ $route->name }}</p>
                            <p><strong>Route Code:</strong> <span class="badge bg-primary">{{ $route->code }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Direction:</strong> 
                                <span class="badge bg-{{ $route->direction === 'forward' ? 'success' : 'warning' }}">
                                    {{ ucfirst($route->direction) }}
                                </span>
                            </p>
                            <p><strong>Status:</strong> 
                                @if($route->status instanceof \App\Enums\RouteStatusEnum)
                                    {!! \App\Enums\RouteStatusEnum::getStatusBadge($route->status->value) !!}
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($route->status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.routes.edit', $route->id) }}" class="btn btn-outline-primary">
                        <i class="bx bx-edit"></i> Edit Route
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Route Summary Card -->
    @if($route->routeStops->count() > 0)
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Route Summary</h5>
            <div class="row">
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-map text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Stops</h6>
                            <span class="text-muted">{{ $route->totalStops }} stops</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-tachometer text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Distance</h6>
                            <span class="text-muted">{{ $route->totalDistance }} km</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-time text-warning fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Travel Time</h6>
                            <span class="text-muted">{{ $route->totalTravelTime }} min</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-map-pin text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Route Path</h6>
                            <span class="text-muted">
                                {{ $route->routeStops->first()->terminal->city->name ?? 'N/A' }} → 
                                {{ $route->routeStops->last()->terminal->city->name ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Route Stops Card -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Route Stops</h5>
            <div class="table-responsive">
                <table id="route-stops-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Sequence</th>
                            <th>Terminal</th>
                            <th>City</th>
                            <th>Distance</th>
                            <th>Travel Time</th>
                            <th>Services</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($route->routeStops->sortBy('sequence') as $stop)
                            <tr data-stop-id="{{ $stop->id }}">
                                <td>
                                    <span class="badge bg-primary">{{ $stop->sequence }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $stop->terminal->name }}</span>
                                        <small class="text-muted">Code: {{ $stop->terminal->code }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $stop->terminal->city->name }}</span>
                                </td>
                                <td>
                                    {{ $stop->distance_from_previous ? $stop->distance_from_previous . ' km' : '-' }}
                                </td>
                                <td>
                                    {{ $stop->approx_travel_time ? $stop->approx_travel_time . ' min' : '-' }}
                                </td>
                                <td>
                                    @if($stop->is_pickup_allowed)
                                        <span class="badge bg-success me-1">Pickup</span>
                                    @endif
                                    @if($stop->is_dropoff_allowed)
                                        <span class="badge bg-info">Dropoff</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @can('edit routes')
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="javascript:void(0)" 
                                                   onclick="editStop({{ $stop->id }}, {{ $stop->sequence }}, {{ $stop->distance_from_previous ?? 'null' }}, {{ $stop->approx_travel_time ?? 'null' }}, {{ $stop->is_pickup_allowed ? 'true' : 'false' }}, {{ $stop->is_dropoff_allowed ? 'true' : 'false' }})">
                                                    <i class="bx bx-edit me-2"></i>Edit Stop
                                                </a>
                                            </li>
                                            @endcan
                                            @can('delete routes')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" 
                                                   href="javascript:void(0)" 
                                                   onclick="deleteStop({{ $stop->id }})">
                                                    <i class="bx bx-trash me-2"></i>Delete Stop
                                                </a>
                                            </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bx bx-map me-2"></i>No stops added to this route yet.
                                    <br>
                                    <small>Click "Add Stop" to start building your route.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Stop Modal -->
    @can('create routes')
    <div class="modal fade" id="addStopModal" tabindex="-1" aria-labelledby="addStopModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStopModalLabel">Add Stop to Route</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addStopForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="terminal_id" class="form-label">Terminal <span class="text-danger">*</span></label>
                                <select class="form-select" id="terminal_id" name="terminal_id" required>
                                    <option value="">Select Terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}">
                                            {{ $terminal->name }} - {{ $terminal->city->name }} ({{ $terminal->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="sequence" class="form-label">Sequence <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="sequence" name="sequence" 
                                       placeholder="Enter sequence number" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="distance_from_previous" class="form-label">Distance from Previous (km)</label>
                                <input type="number" class="form-control" id="distance_from_previous" 
                                       name="distance_from_previous" placeholder="Enter distance" step="0.1" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="approx_travel_time" class="form-label">Travel Time (minutes)</label>
                                <input type="number" class="form-control" id="approx_travel_time" 
                                       name="approx_travel_time" placeholder="Enter travel time" min="0">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_pickup_allowed" 
                                           name="is_pickup_allowed" value="1" checked>
                                    <label class="form-check-label" for="is_pickup_allowed">
                                        Pickup Allowed
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_dropoff_allowed" 
                                           name="is_dropoff_allowed" value="1" checked>
                                    <label class="form-check-label" for="is_dropoff_allowed">
                                        Dropoff Allowed
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Stop</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <!-- Edit Stop Modal -->
    @can('edit routes')
    <div class="modal fade" id="editStopModal" tabindex="-1" aria-labelledby="editStopModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStopModalLabel">Edit Stop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editStopForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_sequence" class="form-label">Sequence <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_sequence" name="sequence" 
                                       placeholder="Enter sequence number" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_distance_from_previous" class="form-label">Distance from Previous (km)</label>
                                <input type="number" class="form-control" id="edit_distance_from_previous" 
                                       name="distance_from_previous" placeholder="Enter distance" step="0.1" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_approx_travel_time" class="form-label">Travel Time (minutes)</label>
                                <input type="number" class="form-control" id="edit_approx_travel_time" 
                                       name="approx_travel_time" placeholder="Enter travel time" min="0">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_pickup_allowed" 
                                           name="is_pickup_allowed" value="1">
                                    <label class="form-check-label" for="edit_is_pickup_allowed">
                                        Pickup Allowed
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_dropoff_allowed" 
                                           name="is_dropoff_allowed" value="1">
                                    <label class="form-check-label" for="edit_is_dropoff_allowed">
                                        Dropoff Allowed
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Stop</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection

@section('scripts')
    <script>
        let currentStopId = null;

        // Auto-calculate next sequence number
        document.addEventListener('DOMContentLoaded', function() {
            const sequenceInput = document.getElementById('sequence');
            const maxSequence = {{ $route->routeStops->max('sequence') ?? 0 }};
            sequenceInput.value = maxSequence + 1;
        });


        // Edit Stop Function
        function editStop(stopId, sequence, distance, travelTime, pickupAllowed, dropoffAllowed) {
            currentStopId = stopId;
            
            // Populate edit form with current values
            document.getElementById('edit_sequence').value = sequence;
            document.getElementById('edit_distance_from_previous').value = distance || '';
            document.getElementById('edit_approx_travel_time').value = travelTime || '';
            document.getElementById('edit_is_pickup_allowed').checked = pickupAllowed;
            document.getElementById('edit_is_dropoff_allowed').checked = dropoffAllowed;
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editStopModal'));
            editModal.show();
        }


        // Delete Stop Function
        function deleteStop(stopId) {
            if (confirm('Are you sure you want to delete this stop?')) {
                fetch(`{{ route('admin.routes.stops.destroy', [$route->id, ':stopId']) }}`.replace(':stopId', stopId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        location.reload(); // Reload to remove deleted stop
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('An error occurred while deleting the stop.');
                });
            }
        }

        // Auto-calculate travel time based on distance
        document.getElementById('distance_from_previous').addEventListener('input', function() {
            const distance = parseFloat(this.value);
            const travelTimeInput = document.getElementById('approx_travel_time');
            
            if (distance && !travelTimeInput.value) {
                // Calculate travel time based on 60 km/h average speed
                const travelTime = Math.round(distance / 60 * 60); // Convert to minutes
                travelTimeInput.value = travelTime;
            }
        });

        document.getElementById('edit_distance_from_previous').addEventListener('input', function() {
            const distance = parseFloat(this.value);
            const travelTimeInput = document.getElementById('edit_approx_travel_time');
            
            if (distance && !travelTimeInput.value) {
                // Calculate travel time based on 60 km/h average speed
                const travelTime = Math.round(distance / 60 * 60); // Convert to minutes
                travelTimeInput.value = travelTime;
            }
        });

        // Reset form when modal is closed
        document.getElementById('addStopModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('addStopForm').reset();
            const maxSequence = {{ $route->routeStops->max('sequence') ?? 0 }};
            document.getElementById('sequence').value = maxSequence + 1;
        });

        document.getElementById('editStopModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('editStopForm').reset();
            currentStopId = null;
        });

        // Form validation
        function validateStopForm(formId) {
            const form = document.getElementById(formId);
            const sequence = form.querySelector('input[name="sequence"]').value;
            const terminalId = form.querySelector('select[name="terminal_id"]')?.value;
            
            if (!sequence || sequence < 1) {
                toastr.error('Please enter a valid sequence number.');
                return false;
            }
            
            if (formId === 'addStopForm' && !terminalId) {
                toastr.error('Please select a terminal.');
                return false;
            }
            
            return true;
        }

        // Enhanced form submission with validation
        document.getElementById('addStopForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateStopForm('addStopForm')) {
                return;
            }
            
            const formData = new FormData(this);
            
            fetch(`{{ route('admin.routes.stops.store', $route->id) }}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    // Close modal and reload
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addStopModal'));
                    modal.hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while adding the stop.');
            });
        });

        // Enhanced edit form submission with validation
        document.getElementById('editStopForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateStopForm('editStopForm')) {
                return;
            }
            
            if (!currentStopId) return;
            
            const formData = new FormData(this);
            
            fetch(`{{ route('admin.routes.stops.update', [$route->id, ':stopId']) }}`.replace(':stopId', currentStopId), {
                method: 'PUT',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    // Close modal and reload
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editStopModal'));
                    modal.hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while updating the stop.');
            });
        });
    </script>
@endsection

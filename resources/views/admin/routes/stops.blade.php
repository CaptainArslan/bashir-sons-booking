@extends('admin.layouts.app')

@section('title', 'Manage Route Stops')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.css" rel="stylesheet">
    
    <style>
        /* Compact Stops Management Styling */
        .stops-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .stops-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .stops-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .add-stop-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-stop-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .route-info-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid #007bff;
        }
        
        .stop-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .stop-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        
        .stop-item.sortable-ghost {
            opacity: 0.4;
        }
        
        .drag-handle {
            cursor: move;
            color: #6c757d;
            font-size: 1.2rem;
            padding: 0.5rem;
        }
        
        .drag-handle:hover {
            color: #007bff;
        }
        
        .stop-sequence-badge {
            min-width: 40px;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <!-- Compact Header -->
    <div class="stops-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-map me-2"></i>Manage Route Stops</h4>
                <p>Manage stops, sequences, and online booking settings for route: {{ $route->name }}</p>
            </div>
            <div>
                @can('create routes')
                    <button type="button" class="add-stop-btn" data-bs-toggle="modal" data-bs-target="#addStopModal">
                        <i class="bx bx-plus me-1"></i>Add Stop
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Route Information Card -->
    <div class="route-info-card mb-3">
        <div class="row">
            <div class="col-md-8">
                <h6 class="mb-2"><i class="bx bx-info-circle me-1"></i>Route Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Route Name:</strong> {{ $route->name }}</p>
                        <p class="mb-1"><strong>Route Code:</strong> <span class="badge bg-primary">{{ $route->code }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Direction:</strong> 
                            <span class="badge bg-{{ $route->direction === 'forward' ? 'success' : 'warning' }}">
                                {{ ucfirst($route->direction) }}
                            </span>
                        </p>
                        <p class="mb-1"><strong>Status:</strong> 
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
                <a href="{{ route('admin.routes.edit', $route->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-edit"></i> Edit Route
                </a>
            </div>
        </div>
    </div>

    <!-- Route Stops Form -->
    @can('edit routes')
    <form id="routeStopsForm" method="POST" action="{{ route('admin.routes.stops.update', $route->id) }}">
        @csrf
        @method('PUT')
        <div class="table-container">
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="bx bx-list-ul me-1"></i>Route Stops</h6>
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        Drag stops to reorder. Sequences will be updated automatically.
                    </small>
                </div>
                
                <div id="stops-list">
                    @forelse($route->routeStops->sortBy('sequence') as $index => $stop)
                        <div class="stop-item" data-stop-id="{{ $stop->id }}">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="bx bx-menu drag-handle"></i>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-primary stop-sequence-badge" data-sequence-display>{{ $stop->sequence }}</span>
                                    <input type="hidden" name="stops[{{ $index }}][id]" value="{{ $stop->id }}">
                                    <input type="hidden" name="stops[{{ $index }}][sequence]" class="sequence-input" value="{{ $stop->sequence }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Terminal</label>
                                    <select name="stops[{{ $index }}][terminal_id]" class="form-select form-select-sm" required>
                                        <option value="">Select Terminal</option>
                                        @foreach ($terminals as $terminal)
                                            <option value="{{ $terminal->id }}" 
                                                {{ $stop->terminal_id == $terminal->id ? 'selected' : '' }}>
                                                {{ $terminal->name }} - {{ $terminal->city->name }} ({{ $terminal->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Sequence</label>
                                    <input type="number" 
                                           name="stops[{{ $index }}][sequence]" 
                                           class="form-control form-control-sm sequence-number" 
                                           value="{{ $stop->sequence }}" 
                                           min="1" 
                                           required
                                           readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Online Booking</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="stops[{{ $index }}][online_booking_allowed]" 
                                               value="1"
                                               {{ $stop->online_booking_allowed ? 'checked' : '' }}>
                                        <label class="form-check-label small">
                                            {{ $stop->online_booking_allowed ? 'Allowed' : 'Not Allowed' }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted mb-1">Terminal Info</label>
                                    <div>
                                        <strong class="small">{{ $stop->terminal->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $stop->terminal->code ?? 'N/A' }}</small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @can('delete routes')
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-stop-btn" 
                                                data-stop-id="{{ $stop->id }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bx bx-map me-2"></i>No stops added to this route yet.
                            <br>
                            <small>Click "Add Stop" to start building your route.</small>
                        </div>
                    @endforelse
                </div>

                @if($route->routeStops->count() > 0)
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save All Changes
                    </button>
                    <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Routes
                    </a>
                </div>
                @endif
            </div>
        </div>
    </form>
    @else
    <div class="table-container">
        <div class="p-3">
            <h6 class="mb-3"><i class="bx bx-list-ul me-1"></i>Route Stops</h6>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Sequence</th>
                            <th>Terminal</th>
                            <th>City</th>
                            <th>Online Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($route->routeStops->sortBy('sequence') as $stop)
                            <tr>
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
                                    @if($stop->online_booking_allowed)
                                        <span class="badge bg-success">Allowed</span>
                                    @else
                                        <span class="badge bg-danger">Not Allowed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bx bx-map me-2"></i>No stops added to this route yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endcan

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
                                <small class="text-muted">Will be automatically adjusted if conflicts exist.</small>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="online_booking_allowed" name="online_booking_allowed" value="1" checked>
                                    <label class="form-check-label" for="online_booking_allowed">
                                        Allow Online Booking from this stop
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        let currentStopIndex = {{ $route->routeStops->count() }};

        // Initialize Sortable for stops list
        @can('edit routes')
        const stopsList = document.getElementById('stops-list');
        if (stopsList) {
            new Sortable(stopsList, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    // Update sequence numbers based on new order
                    updateSequences();
                }
            });
        }

        // Update sequence numbers after drag
        function updateSequences() {
            const stopItems = document.querySelectorAll('.stop-item');
            stopItems.forEach((item, index) => {
                const sequence = index + 1;
                const sequenceInput = item.querySelector('.sequence-input');
                const sequenceNumber = item.querySelector('.sequence-number');
                const sequenceDisplay = item.querySelector('[data-sequence-display]');
                
                if (sequenceInput) sequenceInput.value = sequence;
                if (sequenceNumber) sequenceNumber.value = sequence;
                if (sequenceDisplay) sequenceDisplay.textContent = sequence;
            });
        }

        // Handle sequence number manual input
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('sequence-number') && !e.target.readOnly) {
                const value = parseInt(e.target.value);
                if (value && value > 0) {
                    const stopItem = e.target.closest('.stop-item');
                    const sequenceInput = stopItem.querySelector('.sequence-input');
                    const sequenceDisplay = stopItem.querySelector('[data-sequence-display]');
                    
                    if (sequenceInput) sequenceInput.value = value;
                    if (sequenceDisplay) sequenceDisplay.textContent = value;
                }
            }
        });

        // Handle form submission
        document.getElementById('routeStopsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Update sequences before submission
            updateSequences();
            
            // Show loading state
            Swal.fire({
                title: 'Saving...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                
                if (!response.ok) {
                    let errorMessage = data.message || 'Failed to update stops';
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join(', ');
                        errorMessage = errorMessages || errorMessage;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: data.message || 'Route stops updated successfully.',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to update stops',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while updating stops. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
        @endcan

        // Auto-calculate next sequence number
        document.addEventListener('DOMContentLoaded', function() {
            const sequenceInput = document.getElementById('sequence');
            if (sequenceInput) {
                const maxSequence = {{ $route->routeStops->max('sequence') ?? 0 }};
                sequenceInput.value = maxSequence + 1;
            }
        });

        // Delete Stop Function
        @can('delete routes')
        function deleteStop(stopId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch(`{{ route('admin.routes.stops.destroy', [$route->id, ':stopId']) }}`.replace(':stopId', stopId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                confirmButtonColor: '#28a745',
                                timer: 2000,
                                timerProgressBar: true
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message,
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the stop.',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }

        // Attach delete handlers
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-stop-btn')) {
                const stopId = e.target.closest('.delete-stop-btn').getAttribute('data-stop-id');
                deleteStop(stopId);
            }
        });
        @endcan

        // Reset form when modal is closed
        document.getElementById('addStopModal')?.addEventListener('hidden.bs.modal', function() {
            document.getElementById('addStopForm').reset();
            const maxSequence = {{ $route->routeStops->max('sequence') ?? 0 }};
            const sequenceInput = document.getElementById('sequence');
            if (sequenceInput) {
                sequenceInput.value = maxSequence + 1;
            }
        });

        // Add Stop Form Submission
        @can('create routes')
        document.getElementById('addStopForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Adding...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`{{ route('admin.routes.stops.store', $route->id) }}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                
                if (!response.ok) {
                    let errorMessage = data.message || 'Failed to add stop';
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join(', ');
                        errorMessage = errorMessages || errorMessage;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                
                if (data.success) {
                    const modalElement = document.getElementById('addStopModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Stop added successfully.',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to add stop',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while adding the stop. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
        @endcan

        // Update checkbox labels on change
        document.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox' && e.target.name && e.target.name.includes('online_booking_allowed')) {
                const label = e.target.nextElementSibling;
                if (label) {
                    label.textContent = e.target.checked ? 'Allowed' : 'Not Allowed';
                }
            }
        });
    </script>
@endsection
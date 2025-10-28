@extends('admin.layouts.app')

@section('title', 'Manage Route Stops')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <!-- SweetAlert2 CSS -->

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
        
        .route-summary-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid #28a745;
        }
    </style>
@endsection

@section('content')
    <!-- Compact Header -->
    <div class="stops-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-map me-2"></i>Manage Route Stops</h4>
                <p>View, add, and edit stops for route: {{ $route->name }}</p>
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

    <!-- Route Stops Card -->
    <div class="table-container">
        <div class="p-3">
            <h6 class="mb-3"><i class="bx bx-list-ul me-1"></i>Route Stops</h6>
            <div class="table-responsive">
                <table id="route-stops-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Sequence</th>
                            <th>Terminal</th>
                            <th>City</th>
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
                                                   onclick="editStop({{ $stop->id }})">
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
                                <td colspan="4" class="text-center text-muted py-4">
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
                                 <label for="edit_terminal_id" class="form-label">Terminal <span class="text-danger">*</span></label>
                                 <select class="form-select" id="edit_terminal_id" name="terminal_id" required>
                                     <option value="">Select Terminal</option>
                                     @foreach ($terminals as $terminal)
                                         <option value="{{ $terminal->id }}">
                                             {{ $terminal->name }} - {{ $terminal->city->name }} ({{ $terminal->code }})
                                         </option>
                                     @endforeach
                                 </select>
                             </div>
                             <div class="col-md-6">
                                 <label for="edit_sequence" class="form-label">Sequence <span class="text-danger">*</span></label>
                                 <input type="number" class="form-control" id="edit_sequence" name="sequence" 
                                        placeholder="Enter sequence number" min="1" required>
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
    <!-- SweetAlert2 JS -->
    
    <script>
        let currentStopId = null;

        // Auto-calculate next sequence number
        document.addEventListener('DOMContentLoaded', function() {
            const sequenceInput = document.getElementById('sequence');
            const maxSequence = {{ $route->routeStops->max('sequence') ?? 0 }};
            sequenceInput.value = maxSequence + 1;
        });


         // Edit Stop Function
         function editStop(stopId) {
             currentStopId = stopId;
             
             // Get stop data via AJAX to populate terminal information
             fetch(`{{ route('admin.routes.stops.data', [$route->id, ':stopId']) }}`.replace(':stopId', stopId), {
                 method: 'GET',
                 headers: {
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                 }
             })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     // Populate edit form with current values
                     document.getElementById('edit_terminal_id').value = data.data.terminal.id;
                     document.getElementById('edit_sequence').value = data.data.sequence;
                     
                     // Show edit modal
                     const editModal = new bootstrap.Modal(document.getElementById('editStopModal'));
                     editModal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load stop data',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while loading stop data',
                    confirmButtonColor: '#dc3545'
                });
            });
         }


        // Delete Stop Function
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
                                location.reload(); // Reload to remove deleted stop
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
                 Swal.fire({
                     icon: 'warning',
                     title: 'Validation Error!',
                     text: 'Please enter a valid sequence number.',
                     confirmButtonColor: '#ffc107'
                 });
                 return false;
             }
             
             if (!terminalId) {
                 Swal.fire({
                     icon: 'warning',
                     title: 'Validation Error!',
                     text: 'Please select a terminal.',
                     confirmButtonColor: '#ffc107'
                 });
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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal first
                    const modalElement = document.getElementById('addStopModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        // Fallback: hide modal manually
                        modalElement.classList.remove('show');
                        modalElement.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload(); // Reload page to show new stop
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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal first
                    const modalElement = document.getElementById('editStopModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        // Fallback: hide modal manually
                        modalElement.classList.remove('show');
                        modalElement.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: data.message,
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload(); // Reload page to show updated stop
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to update stop',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while updating the stop. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
    </script>
@endsection

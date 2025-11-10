@extends('admin.layouts.app')

@section('title', 'Timetables')
@section('styles')
    <style>
        /* Simple Timetables Styling */
        .timetables-header {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .timetables-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
        }
        
        .timetables-header p {
            margin: 0.25rem 0 0 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .add-timetable-btn {
            background: #007bff;
            border: 1px solid #007bff;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        .add-timetable-btn:hover {
            background: #0056b3;
            border-color: #0056b3;
            color: white;
        }
        
        .table-container {
            /* background: white; */
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        
        /* Simple Timetable Group Styling */
        .timetable-group {
            margin-bottom: 1rem;
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #dee2e6;
            overflow: hidden;
        }
        
        .timetable-group:last-child {
            margin-bottom: 1rem;
        }
        
        .timetable-header {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .timetable-header:hover {
            background: #e9ecef;
        }
        
        .timetable-header.collapsed {
            border-bottom: none;
        }
        
        .timetable-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
            color: #495057;
        }
        
        .timetable-meta {
            margin-top: 0.25rem;
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .meta-item {
            margin-right: 0.75rem;
        }
        
        .collapse-icon {
            transition: transform 0.2s;
            font-size: 0.9rem;
        }
        
        .timetable-header.collapsed .collapse-icon {
            transform: rotate(-90deg);
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 70px;
            text-align: center;
        }
        
        .status-badge.active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stops-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .stops-table th {
            background: #f8f9fa;
            padding: 0.5rem 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.8rem;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stops-table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.85rem;
            color: #495057;
        }
        
        .stops-table tr:hover {
            background: #f8f9fa;
        }
        
        .timetable-body {
            display: none;
        }
        
        .timetable-body.show {
            display: block;
        }
        
        .stop-sequence {
            font-weight: 600;
            color: #007bff;
            font-size: 0.8rem;
        }
        
        .stop-type {
            font-size: 0.75rem;
            color: #6c757d;
            font-style: italic;
        }
        
        .time-value {
            font-weight: 500;
            font-family: monospace;
            font-size: 0.85rem;
        }
        
        .filters-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .filters-header {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }
        
        .filters-body {
            padding: 1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            border-radius: 4px;
            border: 1px solid;
            min-width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-sm i {
            font-size: 0.9rem;
            line-height: 1;
        }
        
        .btn-sm:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .btn-primary {
            background: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: #007bff;
            color: white;
        }
        
        .dropdown-menu {
            border: 1px solid #dee2e6;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
        }
        
        .no-timetables {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .loading-spinner {
            text-align: center;
            padding: 3rem;
        }
        
    </style>
@endsection

@section('content')
    <!-- Enhanced Header -->
    <div class="timetables-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="fas fa-clock me-2"></i>Timetables Management</h4>
                <p>Manage bus timetables and schedules for all routes with detailed stop information</p>
            </div>
            <div>
                @can('create timetables')
                    <a href="{{ route('admin.timetables.create') }}" class="add-timetable-btn">
                        <i class="fas fa-plus me-1"></i>Generate Timetables
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="filters-card">
        <div class="filters-header" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="true">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-filter-alt text-primary"></i> Filters & Search
                </h6>
                <i class="bx bx-chevron-down collapse-icon"></i>
            </div>
        </div>
        <div class="collapse show" id="filtersCollapse">
            <div class="filters-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Route</label>
                        <select class="form-select form-select-sm" id="filterRoute">
                            <option value="">All Routes</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->name }} ({{ $route->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold">Search Route</label>
                        <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Route name or code...">
                    </div>
                    <div class="col-md-12 d-flex align-items-end gap-2">
                        <button class="btn btn-primary btn-sm" onclick="applyFilters()">
                            <i class="bx bx-search"></i> Apply Filters
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
                            <i class="bx bx-refresh"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timetables List Container -->
    <div class="table-container">
        <div id="timetables-list">
            <!-- Timetables will be loaded here via AJAX -->
        </div>

        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted fs-5">Loading timetables...</p>
        </div>

        <!-- No Timetables Message -->
        <div id="no-timetables" class="text-center py-5" style="display: none;">
            <i class="fas fa-clock text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
            <h4 class="text-muted mt-3">No Timetables Found</h4>
            <p class="text-muted fs-5">Start by creating your first timetable using the "Generate Timetables" button above.</p>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadTimetables();
});

function loadTimetables() {
    $('#loading-spinner').show();
    $('#timetables-list').empty();
    $('#no-timetables').hide();

    const filters = {
        route_id: $('#filterRoute').val(),
        status: $('#filterStatus').val(),
        search: $('#filterSearch').val()
    };

    $.ajax({
        url: "{{ route('admin.timetables.data') }}",
        type: 'GET',
        data: filters,
        success: function(response) {
            $('#loading-spinner').hide();
            
            if (response.data && response.data.length > 0) {
                displayTimetables(response.data);
            } else {
                $('#no-timetables').show();
            }
        },
        error: function(xhr) {
            $('#loading-spinner').hide();
            $('#no-timetables').show();
            console.error('Error loading timetables:', xhr);
        }
    });
}

function applyFilters() {
    loadTimetables();
}

function resetFilters() {
    $('#filterRoute').val('');
    $('#filterStatus').val('');
    $('#filterSearch').val('');
    loadTimetables();
}

function displayTimetables(timetables) {
    let html = '';
    
    timetables.forEach(function(timetable, index) {
        const collapseId = `collapse-${timetable.id}`;
        html += `
            <div class="timetable-group">
                <!-- Timetable Header -->
                <div class="timetable-header collapsed" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <i class="bx bx-chevron-down collapse-icon"></i>
                            <div class="flex-grow-1">
                                <h3 class="timetable-title mb-0">
                                    ${timetable.route_name} <span class="text-muted">(${timetable.route_code})</span>
                                </h3>
                                <div class="timetable-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        ${timetable.start_terminal} → ${timetable.end_terminal}
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-clock me-1"></i>
                                        ${timetable.start_departure_time || 'N/A'}
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-list-ol me-1"></i>
                                        ${timetable.total_stops} stops
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation();">
                            <span class="status-badge ${timetable.status === 'active' ? 'active' : 'inactive'}">
                                ${timetable.status}
                            </span>
                            <div class="action-buttons">
                                ${timetable.can_edit ? `
                                <button class="btn btn-sm ${timetable.status === 'active' ? 'btn-warning' : 'btn-success'}" 
                                        onclick="toggleTimetableStatus(${timetable.id}, '${timetable.status}')" 
                                        title="${timetable.status === 'active' ? 'Deactivate' : 'Activate'}">
                                    <i class="bx ${timetable.status === 'active' ? 'bx-pause' : 'bx-play'}"></i>
                                </button>
                                ` : ''}
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                            type="button" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false"
                                            title="More Actions">
                                        <i class="bx bx-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="viewTimetable(${timetable.id})">
                                                <i class="bx bx-show me-2"></i>View Details
                                            </a>
                                        </li>
                                        ${timetable.can_edit ? `
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="editTimetable(${timetable.id})">
                                                <i class="bx bx-edit me-2"></i>Edit Timetable
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-success" href="#" onclick="toggleAllStops(${timetable.id}, 'active')">
                                                <i class="bx bx-check-circle me-2"></i>Activate All Stops
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-warning" href="#" onclick="toggleAllStops(${timetable.id}, 'inactive')">
                                                <i class="bx bx-pause-circle me-2"></i>Deactivate All Stops
                                            </a>
                                        </li>
                                        ` : ''}
                                        ${timetable.can_delete ? `
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deleteTimetable(${timetable.id})">
                                                <i class="bx bx-trash me-2"></i>Delete Timetable
                                            </a>
                                        </li>
                                        ` : ''}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stops Table -->
                <div class="collapse timetable-body" id="${collapseId}">
                    <div class="p-2">
                        <table class="stops-table">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="22%">Stop Name</th>
                                    <th width="10%">Type</th>
                                    <th width="12%">Arrival</th>
                                    <th width="12%">Departure</th>
                                    <th width="8%">Seq</th>
                                    <th width="11%">Status</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${generateStopsTableRows(timetable.stops, timetable.id)}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#timetables-list').html(html);
    
    // Initialize collapse behavior
    $('.timetable-header').on('click', function() {
        const target = $(this).data('bs-target');
        const isExpanded = $(target).hasClass('show');
        $(this).toggleClass('collapsed', !isExpanded);
    });
}

function generateStopsTableRows(stops, timetableId) {
    if (!stops || stops.length === 0) {
        return '<tr><td colspan="8" class="text-center text-muted py-3">No stops data available</td></tr>';
    }
    
    let html = '';
    stops.forEach(function(stop, index) {
        const isStartStop = index === 0;
        const isEndStop = index === stops.length - 1;
        const stopType = isStartStop ? 'Starting Point' : (isEndStop ? 'Final Destination' : 'Intermediate Stop');
        const canToggle = !isStartStop && !isEndStop;
        const statusClass = stop.is_active ? 'bg-success' : 'bg-danger';
        const statusText = stop.is_active ? 'Active' : 'Inactive';
        
        html += `
            <tr id="stop-row-${timetableId}-${stop.id}" class="${!stop.is_active ? 'table-secondary opacity-75' : ''}">
                <td class="stop-sequence">${index + 1}</td>
                <td>
                    <div><strong>${stop.name}</strong></div>
                    <div class="stop-type">${stopType}</div>
                </td>
                <td>
                    <span class="badge ${isStartStop ? 'bg-success' : (isEndStop ? 'bg-danger' : 'bg-primary')}" style="font-size: 0.75rem;">
                        ${isStartStop ? 'Start' : (isEndStop ? 'End' : 'Mid')}
                    </span>
                </td>
                <td class="time-value">
                    ${stop.arrival_time ? stop.arrival_time : (isStartStop ? '<span class="text-muted">--</span>' : '--')}
                </td>
                <td class="time-value">
                    ${!isEndStop ? (stop.departure_time || '--') : '-'}
                </td>
                <td>
                    <span class="badge bg-secondary" style="font-size: 0.75rem;">${stop.sequence || index + 1}</span>
                </td>
                <td>
                    <span class="badge ${statusClass}" style="font-size: 0.75rem;">${statusText}</span>
                </td>
                <td>
                    ${canToggle ? `
                        <button type="button" 
                                class="btn btn-sm ${stop.is_active ? 'btn-warning' : 'btn-success'} toggle-stop-btn" 
                                data-timetable-id="${timetableId}"
                                data-stop-id="${stop.id}"
                                data-stop-name="${stop.name}"
                                data-is-active="${stop.is_active ? '1' : '0'}"
                                title="${stop.is_active ? 'Disable Stop' : 'Enable Stop'}">
                            <i class="bx ${stop.is_active ? 'bx-pause' : 'bx-play'}"></i>
                        </button>
                    ` : `
                        <span class="text-muted" title="First and last stops cannot be disabled">
                            <i class="bx bx-lock"></i>
                        </span>
                    `}
                </td>
            </tr>
        `;
    });
    
    return html;
}

// Enhanced View Function
function viewTimetable(timetableId) {
    window.location.href = "{{ route('admin.timetables.show', ':id') }}".replace(':id', timetableId);
}

// Enhanced Edit Function
function editTimetable(timetableId) {
    window.location.href = "{{ route('admin.timetables.edit', ':id') }}".replace(':id', timetableId);
}

// Enhanced Toggle Status Function
function toggleTimetableStatus(timetableId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    const actionText = newStatus === 'active' ? 'activate' : 'deactivate';
    
    Swal.fire({
        title: 'Are you sure?',
        text: `You want to ${actionText} this timetable?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: newStatus === 'active' ? '#28a745' : '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${actionText} it!`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: `${actionText === 'activate' ? 'Activating' : 'Deactivating'}...`,
                text: 'Please wait while we process your request',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.timetables.toggle-status', ':id') }}".replace(':id', timetableId),
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: {
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Success!',
                            response.message,
                            'success'
                        ).then(() => {
                            loadTimetables();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message || `Failed to ${actionText} timetable.`,
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = `An error occurred while ${actionText}ing the timetable.`;
                    if (response && response.message) {
                        errorMessage = response.message;
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to edit timetables.';
                    }
                    Swal.fire(
                        'Error!',
                        errorMessage,
                        'error'
                    );
                }
            });
        }
    });
}

// Enhanced Delete Function
function deleteTimetable(timetableId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the timetable',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.timetables.destroy', ':id') }}".replace(':id', timetableId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Deleted!',
                            response.message || 'Timetable has been deleted.',
                            'success'
                        ).then(() => {
                            loadTimetables();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message || 'Failed to delete timetable.',
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = 'An error occurred while deleting the timetable.';
                    if (response && response.message) {
                        errorMessage = response.message;
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to delete timetables.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Timetable not found.';
                    }
                    Swal.fire(
                        'Error!',
                        errorMessage,
                        'error'
                    );
                }
            });
        }
    });
}

// Toggle timetable stop status from index page
$(document).on('click', '.toggle-stop-btn', function() {
    const btn = $(this);
    const timetableId = btn.data('timetable-id');
    const stopId = btn.data('stop-id');
    const stopName = btn.data('stop-name');
    const isActive = btn.data('is-active') === '1';
    const action = isActive ? 'disable' : 'enable';
    
    Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Stop?`,
        text: `Are you sure you want to ${action} "${stopName}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${action} it!`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: isActive ? '#dc3545' : '#28a745',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: () => {
            const url = `/admin/timetables/${timetableId}/stops/${stopId}/toggle-status`;
            return fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PATCH'
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to update stop status');
                    });
                }
                return response.json();
            })
            .catch(error => {
                Swal.hideLoading();
                Swal.showValidationMessage(`Request failed: ${error.message}`);
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            if (data.success) {
                // Update button state
                const newIsActive = data.is_active;
                btn.data('is-active', newIsActive ? '1' : '0');
                
                // Update button appearance
                if (newIsActive) {
                    btn.removeClass('btn-success').addClass('btn-warning');
                    btn.find('i').removeClass('bx-play').addClass('bx-pause');
                    btn.attr('title', 'Disable Stop');
                } else {
                    btn.removeClass('btn-warning').addClass('btn-success');
                    btn.find('i').removeClass('bx-pause').addClass('bx-play');
                    btn.attr('title', 'Enable Stop');
                }
                
                // Update row appearance
                const row = $(`#stop-row-${timetableId}-${stopId}`);
                if (newIsActive) {
                    row.removeClass('table-secondary opacity-75');
                } else {
                    row.addClass('table-secondary opacity-75');
                }
                
                // Update status badge
                const statusBadge = row.find('td:nth-child(7) .badge');
                if (newIsActive) {
                    statusBadge.removeClass('bg-danger').addClass('bg-success').text('Active');
                } else {
                    statusBadge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to update stop status'
                });
            }
        }
    });
});

// Toggle all stops for a timetable
function toggleAllStops(timetableId, status) {
    const action = status === 'active' ? 'activate' : 'deactivate';
    const actionText = status === 'active' ? 'activate' : 'deactivate';
    
    Swal.fire({
        title: `Are you sure?`,
        text: `You want to ${actionText} all intermediate stops in this timetable? (First and last stops will remain active)`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${actionText} all!`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: status === 'active' ? '#28a745' : '#ffc107',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: () => {
            const url = `/admin/timetables/${timetableId}/stops/toggle-all`;
            return fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: status,
                    _method: 'PATCH'
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to update stops');
                    });
                }
                return response.json();
            })
            .catch(error => {
                Swal.hideLoading();
                Swal.showValidationMessage(`Request failed: ${error.message}`);
                return false;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    // Reload timetables to reflect changes
                    loadTimetables();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to update stops'
                });
            }
        }
    });
}

// Add smooth scrolling for better UX
$('html').css('scroll-behavior', 'smooth');

// Allow Enter key to trigger filter
$('#filterSearch').on('keypress', function(e) {
    if (e.which === 13) {
        applyFilters();
    }
});

// Initialize collapse icons
$(document).on('shown.bs.collapse', '.collapse', function() {
    $(this).closest('.timetable-group').find('.timetable-header').removeClass('collapsed');
});

$(document).on('hidden.bs.collapse', '.collapse', function() {
    $(this).closest('.timetable-group').find('.timetable-header').addClass('collapsed');
});
</script>
@endsection

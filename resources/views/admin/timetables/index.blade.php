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
            margin-bottom: 3rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        
        .timetable-group:last-child {
            margin-bottom: 2rem;
        }
        
        .timetable-header {
            background: #e9ecef;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .timetable-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: #495057;
        }
        
        .timetable-meta {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .meta-item {
            margin-right: 1rem;
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
        }
        
        .stops-table th {
            background: #f8f9fa;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stops-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .stops-table tr:hover {
            background: #f8f9fa;
        }
        
        .stop-sequence {
            font-weight: 600;
            color: #007bff;
        }
        
        .stop-type {
            font-size: 0.8rem;
            color: #6c757d;
            font-style: italic;
        }
        
        .time-value {
            font-weight: 500;
            font-family: monospace;
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

    $.ajax({
        url: "{{ route('admin.timetables.data') }}",
        type: 'GET',
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

function displayTimetables(timetables) {
    let html = '';
    
    timetables.forEach(function(timetable, index) {
        html += `
            <div class="timetable-group">
                <!-- Timetable Header -->
                <div class="timetable-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="timetable-title">
                                ${timetable.route_name} <span class="text-muted">(${timetable.route_code})</span>
                            </h3>
                            <div class="timetable-meta">
                                <span class="meta-item">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    ${timetable.start_terminal} → ${timetable.end_terminal}
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    ${formatDate(timetable.created_at)}
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-list-ol me-1"></i>
                                    ${timetable.total_stops} stops
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-badge ${timetable.status === 'active' ? 'active' : 'inactive'}">
                                ${timetable.status}
                            </span>
                            <div class="action-buttons">
                                <button class="btn btn-sm ${timetable.status === 'active' ? 'btn-warning' : 'btn-success'}" 
                                        onclick="toggleTimetableStatus(${timetable.id}, '${timetable.status}')" 
                                        title="${timetable.status === 'active' ? 'Deactivate' : 'Activate'}">
                                    <i class="bx ${timetable.status === 'active' ? 'bx-pause' : 'bx-play'}"></i>
                                </button>
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
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="editTimetable(${timetable.id})">
                                                <i class="bx bx-edit me-2"></i>Edit Timetable
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deleteTimetable(${timetable.id})">
                                                <i class="bx bx-trash me-2"></i>Delete Timetable
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stops Table -->
                <div class="p-3">
                    <table class="stops-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Stop Name</th>
                                <th width="12%">Type</th>
                                <th width="12%">Status</th>
                                <th width="12%">Arrival Time</th>
                                <th width="12%">Departure Time</th>
                                <th width="22%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${generateStopsTableRows(timetable.stops)}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    });
    
    $('#timetables-list').html(html);
}

function generateStopsTableRows(stops) {
    if (!stops || stops.length === 0) {
        return '<tr><td colspan="7" class="text-center text-muted py-3">No stops data available</td></tr>';
    }
    
    let html = '';
    stops.forEach(function(stop, index) {
        const isStartStop = index === 0;
        const isEndStop = index === stops.length - 1;
        const stopType = isStartStop ? 'Starting Point' : (isEndStop ? 'Final Destination' : 'Intermediate Stop');
        const stopStatus = stop.status || 'active';
        const statusClass = stopStatus === 'active' ? 'active' : 'inactive';
        
        html += `
            <tr>
                <td class="stop-sequence">${index + 1}</td>
                <td>
                    <div>${stop.name}</div>
                    <div class="stop-type">${stopType}</div>
                </td>
                <td>
                    <span class="badge ${isStartStop ? 'bg-success' : (isEndStop ? 'bg-danger' : 'bg-primary')}">
                        ${stopType}
                    </span>
                </td>
                <td>
                    <span class="status-badge ${statusClass}">
                        ${stopStatus}
                    </span>
                </td>
                <td class="time-value">
                    ${!isStartStop ? (stop.arrival_time || '--:--') : '-'}
                </td>
                <td class="time-value">
                    ${!isEndStop ? (stop.departure_time || '--:--') : '-'}
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="viewStop(${stop.id})" title="View Stop">
                            <i class="bx bx-show"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editStop(${stop.id})" title="Edit Stop">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-sm ${stopStatus === 'active' ? 'btn-outline-warning' : 'btn-outline-success'}" 
                                onclick="toggleStopStatus(${stop.id}, '${stopStatus}')" 
                                title="${stopStatus === 'active' ? 'Deactivate Stop' : 'Activate Stop'}">
                            <i class="bx ${stopStatus === 'active' ? 'bx-pause' : 'bx-play'}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    return html;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
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
    
    if (confirm(`Are you sure you want to ${action} this timetable?`)) {
        $.ajax({
            url: "{{ route('admin.timetables.toggle-status', ':id') }}".replace(':id', timetableId),
            type: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    loadTimetables(); // Reload timetables
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response.message || 'An error occurred while updating the timetable status.');
            }
        });
    }
}

// Enhanced Delete Function
function deleteTimetable(timetableId) {
    if (confirm('Are you sure you want to delete this timetable? This action cannot be undone.')) {
        $.ajax({
            url: "{{ route('admin.timetables.destroy', ':id') }}".replace(':id', timetableId),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    loadTimetables(); // Reload timetables
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response.message || 'An error occurred while deleting the timetable.');
            }
        });
    }
}

// Stop action functions
function viewStop(stopId) {
    console.log('View stop:', stopId);
    // Add your view stop logic here
}

function editStop(stopId) {
    console.log('Edit stop:', stopId);
    // Add your edit stop logic here
}

function toggleStopStatus(stopId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${action} this stop?`)) {
        // Add your stop status toggle logic here
        console.log(`Toggle stop ${stopId} from ${currentStatus} to ${newStatus}`);
        
        // You can add AJAX call here to update stop status
        // For now, just reload the timetables to show updated status
        loadTimetables();
    }
}

// Add smooth scrolling for better UX
$('html').css('scroll-behavior', 'smooth');
</script>
@endsection

@extends('admin.layouts.app')

@section('title', 'Announcements')
@section('styles')
    <style>
        /* Consistent Announcements Styling */
        .announcements-header {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .announcements-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
        }
        
        .announcements-header p {
            margin: 0.25rem 0 0 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .add-announcement-btn {
            background: #007bff;
            border: 1px solid #007bff;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        .add-announcement-btn:hover {
            background: #0056b3;
            border-color: #0056b3;
            color: white;
        }
        
        .table-container {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #ced4da;
        }
        
        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            margin: 0 2px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #0056b3;
            border-color: #0056b3;
            color: white;
        }
        
        .table th {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .table td {
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
            color: #495057;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.5rem;
            border-radius: 3px;
        }
        
        .btn-group .btn {
            border-radius: 4px;
            margin-right: 2px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
        }
        
        .btn-outline-primary:hover {
            background: #007bff;
            color: white;
        }
        
        .btn-outline-warning {
            color: #ffc107;
            border-color: #ffc107;
        }
        
        .btn-outline-warning:hover {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-outline-success {
            color: #198754;
            border-color: #198754;
        }
        
        .btn-outline-success:hover {
            background: #198754;
            color: white;
        }
        
        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-outline-danger:hover {
            background: #dc3545;
            color: white;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Consistent Header -->
    <div class="announcements-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-megaphone me-2"></i>Announcements Management</h4>
                <p>Manage system announcements, banners, and notifications for users</p>
            </div>
            <div>
                @can('create announcements')
                    <a href="{{ route('admin.announcements.create') }}" class="add-announcement-btn">
                        <i class="bx bx-plus me-1"></i>Create Announcement
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="announcements-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Display Type</th>
                            <th>Priority</th>
                            <th>Audience</th>
                            <th>Flags</th>
                            <th>Date Range</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#announcements-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.announcements.data') }}",
        columns: [
            { data: 'id', name: 'id', width: '5%' },
            { data: 'title', name: 'title', width: '20%' },
            { data: 'status_badge', name: 'status', width: '8%', orderable: false },
            { data: 'display_type_badge', name: 'display_type', width: '10%', orderable: false },
            { data: 'priority_badge', name: 'priority', width: '8%', orderable: false },
            { data: 'audience_type_badge', name: 'audience_type', width: '10%', orderable: false },
            { data: 'flags', name: 'flags', width: '12%', orderable: false },
            { data: 'date_range', name: 'date_range', width: '15%', orderable: false },
            { data: 'created_at', name: 'created_at', width: '10%' },
            { data: 'actions', name: 'actions', width: '12%', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: "Loading announcements...",
            emptyTable: "No announcements found",
            zeroRecords: "No matching announcements found"
        }
    });
});

// Toggle status function
function toggleStatus(announcementId, isActive) {
    const action = isActive ? 'deactivate' : 'activate';
    
    if (confirm(`Are you sure you want to ${action} this announcement?`)) {
        $.ajax({
            url: "{{ route('admin.announcements.toggle-status', ':id') }}".replace(':id', announcementId),
            type: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#announcements-table').DataTable().ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response.message || 'An error occurred while updating the announcement status.');
            }
        });
    }
}

// Delete announcement function
function deleteAnnouncement(announcementId) {
    if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
        $.ajax({
            url: "{{ route('admin.announcements.destroy', ':id') }}".replace(':id', announcementId),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#announcements-table').DataTable().ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response.message || 'An error occurred while deleting the announcement.');
            }
        });
    }
}
</script>
@endsection

@extends('admin.layouts.app')

@section('title', 'View Announcement')
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
        
        .back-btn {
            background: #6c757d;
            border: 1px solid #6c757d;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: #5a6268;
            border-color: #5a6268;
            color: white;
        }
        
        .view-container {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .card-header {
            background: #e9ecef;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
        }
        
        .card-header h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: #495057;
        }
        
        .card-header p {
            margin: 0.5rem 0 0 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .info-row {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.5rem;
            border-radius: 3px;
        }
        
        .announcement-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        
        .description-content {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .audience-list {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .audience-item {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            margin: 0.125rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            border-color: #d39e00;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            border-color: #bd2130;
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
                <h4><i class="bx bx-show me-2"></i>View Announcement</h4>
                <p>Announcement details and information</p>
            </div>
            <div>
                <a href="{{ route('admin.announcements.index') }}" class="back-btn">
                    <i class="bx bx-arrow-back me-1"></i>Back to Announcements
                </a>
            </div>
        </div>
    </div>

    <!-- View Container -->
    <div class="view-container">
        <div class="card-header">
            <h4>{{ $announcement->title }}</h4>
            <p>Announcement ID: #{{ $announcement->id }}</p>
        </div>
        
        <div class="card-body">
            <!-- Image -->
            @if($announcement->image)
            <div class="info-row">
                <div class="info-label">Image</div>
                <div class="info-value">
                    <img src="{{ Storage::url($announcement->image) }}" class="announcement-image" alt="Announcement image">
                </div>
            </div>
            @endif
            
            <!-- Description -->
            <div class="info-row">
                <div class="info-label">Description</div>
                <div class="description-content">{{ $announcement->description }}</div>
            </div>
            
            <!-- Link -->
            @if($announcement->link)
            <div class="info-row">
                <div class="info-label">Link</div>
                <div class="info-value">
                    <a href="{{ $announcement->link }}" target="_blank" class="text-primary">
                        {{ $announcement->link }}
                        <i class="bx bx-link-external ms-1"></i>
                    </a>
                </div>
            </div>
            @endif
            
            <!-- Status and Settings -->
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $announcement->status->value === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($announcement->status->value) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Display Type</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $announcement->display_type->value === 'banner' ? 'primary' : ($announcement->display_type->value === 'popup' ? 'info' : 'warning') }}">
                                {{ ucfirst($announcement->display_type->value) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Priority</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $announcement->priority->value === 'high' ? 'danger' : ($announcement->priority->value === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($announcement->priority->value) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Audience Type</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $announcement->audience_type->value === 'all' ? 'primary' : ($announcement->audience_type->value === 'roles' ? 'info' : 'warning') }}">
                                {{ ucfirst($announcement->audience_type->value) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Flags</div>
                        <div class="info-value">
                            @if($announcement->is_pinned)
                                <span class="badge bg-danger me-1">Pinned</span>
                            @endif
                            @if($announcement->is_featured)
                                <span class="badge bg-warning me-1">Featured</span>
                            @endif
                            @if($announcement->is_active)
                                <span class="badge bg-success me-1">Active</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Audience</div>
                        <div class="audience-list">
                            @if($announcement->audience_type->value === 'all')
                                <span class="audience-item">All Users</span>
                            @elseif($announcement->audience_type->value === 'roles' && $announcement->audience_payload)
                                @foreach($announcement->audience_payload as $role)
                                    <span class="audience-item">{{ ucfirst($role) }}</span>
                                @endforeach
                            @elseif($announcement->audience_type->value === 'users' && $announcement->readers->count() > 0)
                                @foreach($announcement->readers as $user)
                                    <span class="audience-item">{{ $user->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No specific audience</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Date Range -->
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Start Date</div>
                        <div class="info-value">
                            {{ $announcement->start_date ? $announcement->start_date->format('M d, Y H:i') : 'Not set' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">End Date</div>
                        <div class="info-value">
                            {{ $announcement->end_date ? $announcement->end_date->format('M d, Y H:i') : 'Not set' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timestamps -->
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Created At</div>
                        <div class="info-value">
                            {{ $announcement->created_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Updated At</div>
                        <div class="info-value">
                            {{ $announcement->updated_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('admin.announcements.edit', $announcement->id) }}" class="btn btn-warning">
                    <i class="bx bx-edit"></i>Edit
                </a>
                
                <button class="btn btn-{{ $announcement->is_active ? 'danger' : 'success' }}" 
                        onclick="toggleStatus({{ $announcement->id }}, {{ $announcement->is_active ? 'true' : 'false' }})">
                    <i class="bx {{ $announcement->is_active ? 'bx-pause' : 'bx-play' }}"></i>
                    {{ $announcement->is_active ? 'Deactivate' : 'Activate' }}
                </button>
                
                <button class="btn btn-danger" onclick="deleteAnnouncement({{ $announcement->id }})">
                    <i class="bx bx-trash"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
                    location.reload();
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
                    window.location.href = "{{ route('admin.announcements.index') }}";
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

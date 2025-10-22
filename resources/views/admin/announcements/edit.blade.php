@extends('admin.layouts.app')

@section('title', 'Edit Announcement')
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
        
        .form-container {
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
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-select, .form-control {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .btn-primary {
            background: #007bff;
            border-color: #007bff;
            color: white;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .text-danger {
            font-size: 0.8rem;
        }
        
        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        .form-check-label {
            font-weight: 500;
            color: #495057;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        
        .audience-section {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 0.5rem;
        }
        
        .audience-section.hidden {
            display: none;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Consistent Header -->
    <div class="announcements-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-edit me-2"></i>Edit Announcement</h4>
                <p>Modify announcement details and settings</p>
            </div>
            <div>
                <a href="{{ route('admin.announcements.index') }}" class="back-btn">
                    <i class="bx bx-arrow-back me-1"></i>Back to Announcements
                </a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="card-header">
            <h4>Announcement Details</h4>
            <p>Update the announcement information</p>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.announcements.update', $announcement->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $announcement->title) }}"
                               placeholder="Enter announcement title"
                               required>
                        @error('title')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="image" class="form-label">Image (Optional)</label>
                        <input type="file" 
                               name="image" 
                               id="image" 
                               class="form-control @error('image') is-invalid @enderror"
                               accept="image/*">
                        @error('image')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Max 2MB, JPEG/PNG/JPG/GIF</div>
                        @if($announcement->image)
                            <div class="mt-2">
                                <img src="{{ Storage::url($announcement->image) }}" class="image-preview" alt="Current image">
                                <div class="form-text">Current image</div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" 
                              id="description" 
                              class="form-control @error('description') is-invalid @enderror" 
                              rows="4"
                              placeholder="Enter announcement description"
                              required>{{ old('description', $announcement->description) }}</textarea>
                    @error('description')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Link -->
                <div class="mb-3">
                    <label for="link" class="form-label">Link (Optional)</label>
                    <input type="url" 
                           name="link" 
                           id="link" 
                           class="form-control @error('link') is-invalid @enderror" 
                           value="{{ old('link', $announcement->link) }}"
                           placeholder="https://example.com">
                    @error('link')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Optional link for users to click</div>
                </div>
                
                <!-- Settings Row -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('status', $announcement->status->value) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $announcement->status->value) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="display_type" class="form-label">Display Type</label>
                        <select name="display_type" id="display_type" class="form-select @error('display_type') is-invalid @enderror" required>
                            <option value="">Select Display Type</option>
                            <option value="banner" {{ old('display_type', $announcement->display_type->value) == 'banner' ? 'selected' : '' }}>Banner</option>
                            <option value="popup" {{ old('display_type', $announcement->display_type->value) == 'popup' ? 'selected' : '' }}>Popup</option>
                            <option value="notification" {{ old('display_type', $announcement->display_type->value) == 'notification' ? 'selected' : '' }}>Notification</option>
                        </select>
                        @error('display_type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="">Select Priority</option>
                            <option value="high" {{ old('priority', $announcement->priority->value) == 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ old('priority', $announcement->priority->value) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ old('priority', $announcement->priority->value) == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                        @error('priority')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="audience_type" class="form-label">Audience Type</label>
                        <select name="audience_type" id="audience_type" class="form-select @error('audience_type') is-invalid @enderror" required>
                            <option value="">Select Audience</option>
                            <option value="all" {{ old('audience_type', $announcement->audience_type->value) == 'all' ? 'selected' : '' }}>All Users</option>
                            <option value="roles" {{ old('audience_type', $announcement->audience_type->value) == 'roles' ? 'selected' : '' }}>Specific Roles</option>
                            <option value="users" {{ old('audience_type', $announcement->audience_type->value) == 'users' ? 'selected' : '' }}>Specific Users</option>
                        </select>
                        @error('audience_type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Audience Configuration -->
                <div id="audience-roles" class="audience-section {{ old('audience_type', $announcement->audience_type->value) == 'roles' ? '' : 'hidden' }}">
                    <label class="form-label">Select Roles</label>
                    <div class="row">
                        @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" 
                                       name="audience_payload[]" 
                                       value="{{ $role }}" 
                                       class="form-check-input"
                                       {{ in_array($role, old('audience_payload', $announcement->audience_payload ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ ucfirst($role) }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <div id="audience-users" class="audience-section {{ old('audience_type', $announcement->audience_type->value) == 'users' ? '' : 'hidden' }}">
                    <label class="form-label">Select Users</label>
                    <select name="audience_users[]" id="audience_users" class="form-select" multiple>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ in_array($user->id, old('audience_users', $announcement->readers->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple users</div>
                </div>
                
                <!-- Date Range -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="datetime-local" 
                               name="start_date" 
                               id="start_date" 
                               class="form-control @error('start_date') is-invalid @enderror" 
                               value="{{ old('start_date', $announcement->start_date ? $announcement->start_date->format('Y-m-d\TH:i') : '') }}"
                               required>
                        @error('start_date')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="datetime-local" 
                               name="end_date" 
                               id="end_date" 
                               class="form-control @error('end_date') is-invalid @enderror" 
                               value="{{ old('end_date', $announcement->end_date ? $announcement->end_date->format('Y-m-d\TH:i') : '') }}"
                               required>
                        @error('end_date')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Options -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="is_pinned" 
                                   id="is_pinned" 
                                   class="form-check-input" 
                                   value="1"
                                   {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                            <label for="is_pinned" class="form-check-label">Pin this announcement</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="is_featured" 
                                   id="is_featured" 
                                   class="form-check-input" 
                                   value="1"
                                   {{ old('is_featured', $announcement->is_featured) ? 'checked' : '' }}>
                            <label for="is_featured" class="form-check-label">Feature this announcement</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   class="form-check-input" 
                                   value="1"
                                   {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-save me-2"></i>Update Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Handle audience type change
    $('#audience_type').change(function() {
        const audienceType = $(this).val();
        
        // Hide all audience sections
        $('.audience-section').addClass('hidden');
        
        // Show relevant section
        if (audienceType === 'roles') {
            $('#audience-roles').removeClass('hidden');
        } else if (audienceType === 'users') {
            $('#audience-users').removeClass('hidden');
        }
    });
    
    // Image preview
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Remove existing preview
                $('.image-preview').remove();
                
                // Add new preview
                $('#image').after('<img src="' + e.target.result + '" class="image-preview mt-2">');
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection

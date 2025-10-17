@extends('admin.layouts.app')

@section('title', 'Edit Role')

@section('styles')
<style>
    .role-card {
        border-left: 4px solid #0d6efd;
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header-info h5 {
        margin: 0;
        font-weight: 600;
    }
    
    .permission-group {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .permission-group:hover {
        background: #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .permission-checkbox {
        cursor: pointer;
    }
    
    .permission-label {
        cursor: pointer;
        user-select: none;
        font-weight: 500;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .role-info-card {
        border-left: 3px solid #0dcaf0;
    }
    
    .stats-badge {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
    
    .permission-counter {
        position: sticky;
        top: 20px;
        background: white;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Roles Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card role-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Role: {{ $role->name }}</h5>
                </div>
                
                <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body p-4">
                        <!-- Role Name Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <label for="name" class="form-label">
                                    Role Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name"
                                       name="name" 
                                       placeholder="Enter Role Name" 
                                       value="{{ old('name', $role->name) }}" 
                                       {{ $isDefaultRole ? 'readonly' : '' }} 
                                       required
                                       autofocus>
                                
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                
                                @if($isDefaultRole)
                                    <div class="alert alert-warning mt-2 mb-0">
                                        <i class="bx bx-info-circle me-2"></i>
                                        This is a system role and cannot be modified.
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Role Information Card -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card role-info-card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="mb-2">
                                                    <strong>Role ID:</strong> 
                                                    <span class="badge bg-secondary">{{ $role->id }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-2">
                                                    <strong>Current Permissions:</strong> 
                                                    <span class="badge bg-info stats-badge" id="permissionCount">
                                                        {{ $role->permissions->count() }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-2">
                                                    <strong>Created:</strong> 
                                                    {{ $role->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-2">
                                                    <strong>Last Updated:</strong> 
                                                    {{ $role->updated_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="card-body p-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                <i class="bx bx-shield-quarter me-2"></i>Assign Permissions
                            </h5>
                            @if(!$isDefaultRole && $permissions->count() > 0)
                                <div class="btn-group">
                                    <button type="button" id="selectAllBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-check-double me-1"></i>Select All
                                    </button>
                                    <button type="button" id="deselectAllBtn" class="btn btn-outline-secondary btn-sm">
                                        <i class="bx bx-x me-1"></i>Deselect All
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($permissions->count() > 0)
                            <div class="row">
                                @foreach ($permissions as $permission)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="permission-group">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" 
                                                       type="checkbox"
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       id="permission_{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}
                                                       {{ $isDefaultRole ? 'disabled' : '' }}>
                                                <label class="form-check-label permission-label" 
                                                       for="permission_{{ $permission->id }}">
                                                    {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                No permissions found. Please create permissions first.
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                @if(!$isDefaultRole && $permissions->count() > 0)
                                    <button type="button" class="btn btn-secondary px-4" id="resetFormBtn">
                                        <i class="bx bx-reset me-1"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Update Role
                                    </button>
                                @else
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Back to Roles
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const resetBtn = document.getElementById('resetFormBtn');
            const checkboxes = document.querySelectorAll('.permission-checkbox');
            const nameInput = document.getElementById('name');
            const permissionCount = document.getElementById('permissionCount');
            const isDefaultRole = {{ $isDefaultRole ? 'true' : 'false' }};

            // ✅ Update permission counter
            function updatePermissionCounter() {
                const checkedCount = document.querySelectorAll('.permission-checkbox:checked').length;
                if (permissionCount) {
                    permissionCount.textContent = checkedCount;
                }
            }

            // ✅ Helper: Update button states dynamically
            function updateButtonStates() {
                if (isDefaultRole) return;
                
                const checkedCount = document.querySelectorAll('.permission-checkbox:checked').length;
                const total = checkboxes.length;

                if (selectAllBtn) {
                    selectAllBtn.disabled = checkedCount === total;
                    selectAllBtn.classList.toggle('btn-outline-primary', !selectAllBtn.disabled);
                    selectAllBtn.classList.toggle('btn-outline-secondary', selectAllBtn.disabled);
                }

                if (deselectAllBtn) {
                    deselectAllBtn.disabled = checkedCount === 0;
                    deselectAllBtn.classList.toggle('btn-outline-secondary', deselectAllBtn.disabled);
                    deselectAllBtn.classList.toggle('btn-outline-primary', !deselectAllBtn.disabled);
                }
                
                updatePermissionCounter();
            }

            // ✅ Select all permissions
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    if (!isDefaultRole) {
                        checkboxes.forEach(checkbox => checkbox.checked = true);
                        updateButtonStates();
                    }
                });
            }

            // ✅ Deselect all permissions
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', () => {
                    if (!isDefaultRole) {
                        checkboxes.forEach(checkbox => checkbox.checked = false);
                        updateButtonStates();
                    }
                });
            }

            // ✅ Reset form
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    if (!isDefaultRole) {
                        nameInput.value = '{{ $role->name }}';
                        // Reset to original role permissions
                        checkboxes.forEach(checkbox => {
                            const permissionId = parseInt(checkbox.value);
                            checkbox.checked = {{ $role->permissions->pluck('id')->toJson() }}.includes(permissionId);
                        });
                        nameInput.classList.remove('is-invalid');
                        nameInput.focus();
                        updateButtonStates();
                    }
                });
            }

            // ✅ Update button states whenever a checkbox changes
            if (!isDefaultRole) {
                checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateButtonStates));
                // ✅ Initialize state on page load
                updateButtonStates();
            }
            
            // ✅ Add visual feedback on checkbox change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.closest('.permission-group');
                    if (this.checked) {
                        label.style.background = '#d1ecf1';
                        label.style.borderLeft = '3px solid #0dcaf0';
                    } else {
                        label.style.background = '#f8f9fa';
                        label.style.borderLeft = 'none';
                    }
                });
            });
            
            // ✅ Initialize visual state
            checkboxes.forEach(checkbox => {
                const label = checkbox.closest('.permission-group');
                if (checkbox.checked) {
                    label.style.background = '#d1ecf1';
                    label.style.borderLeft = '3px solid #0dcaf0';
                }
            });
        });
    </script>
@endsection

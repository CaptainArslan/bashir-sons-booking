@extends('admin.layouts.app')

@section('title', 'Create Role')

@section('styles')
    <style>
        .role-card {
            border-left: 4px solid #0d6efd;
        }

        .card-header-info {
            background: #fff;
            color: #495057;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .card-header-info h5 {
            margin: 0;
            font-weight: 600;
            color: #495057;
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .info-box i {
            color: #0d6efd;
        }

        .stats-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .module-header {
            cursor: pointer;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
            transition: background-color 0.2s;
        }

        .module-header:hover {
            background: #e9ecef;
        }

        .module-header h6 {
            margin: 0;
            color: #212529;
            font-weight: 600;
        }

        .module-toggle {
            transition: transform 0.3s;
        }

        .module-header[aria-expanded="false"] .module-toggle,
        .module-toggle.collapsed {
            transform: rotate(-90deg);
        }

        .module-body {
            padding: 1rem;
        }

        .select-all-link {
            color: #0d6efd;
            text-decoration: none;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .select-all-link:hover {
            text-decoration: underline;
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Role</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card role-card">
                <div class="card-header card-header-info">
                    <h5 class="mb-0"><i class="bx bx-plus-circle me-2"></i>Create New Role</h5>
                </div>

                <form action="{{ route('admin.roles.store') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="card-body p-4">
                        <!-- Role Name Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <label for="name" class="form-label">
                                    Role Name
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name"
                                    placeholder="Enter Role Name (e.g., Manager, Editor, Viewer)"
                                    value="{{ old('name') }}" required autofocus>

                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div class="info-box">
                                    <div class="d-flex align-items-start">
                                        <i class="bx bx-info-circle me-2 mt-1"></i>
                                        <div>
                                            <strong class="d-block mb-2">Role Naming Tips:</strong>
                                            <ul class="mb-0 ps-3">
                                                <li>Use descriptive names (e.g., Content Manager, Sales Executive)</li>
                                                <li>Capitalize first letter of each word</li>
                                                <li>Avoid special characters and numbers</li>
                                                <li>Keep names concise and clear</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permission Statistics -->
                        @if ($permissions->count() > 0)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <p class="mb-2">
                                                        <strong>Total Permissions:</strong>
                                                        <span class="badge bg-info stats-badge" id="totalPermissions">
                                                            {{ $permissions->count() }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-2">
                                                        <strong>Selected:</strong>
                                                        <span class="badge bg-success stats-badge" id="selectedPermissions">
                                                            0
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-2">
                                                        <strong>Remaining:</strong>
                                                        <span class="badge bg-warning stats-badge"
                                                            id="remainingPermissions">
                                                            {{ $permissions->count() }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Permissions Section -->
                    <div class="card-body p-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                <i class="bx bx-shield-quarter me-2"></i>Assign Permissions
                            </h5>
                            @if ($permissions->count() > 0)
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

                        @if ($permissions->count() > 0)
                            @foreach ($permissionsByModule as $module => $modulePermissions)
                                <div class="module-section mb-3">
                                    <div class="card border">
                                        <div class="module-header" data-bs-toggle="collapse"
                                            data-bs-target="#module-{{ Str::slug($module) }}" aria-expanded="true">
                                            <h6 class="mb-0 d-flex align-items-center justify-content-between">
                                                <span>
                                                    <i class="bx bx-folder me-2"></i>{{ $module }}
                                                    <span class="badge bg-secondary ms-2">{{ $modulePermissions->count() }}</span>
                                                </span>
                                                <i class="bx bx-chevron-down module-toggle"></i>
                                            </h6>
                                        </div>
                                        <div class="collapse show" id="module-{{ Str::slug($module) }}">
                                            <div class="module-body">
                                                <div class="mb-3">
                                                    <a href="#" class="select-all-link" data-module="{{ Str::slug($module) }}">
                                                        Select all
                                                    </a>
                                                </div>
                                                <div class="row">
                                                    @foreach ($modulePermissions as $permission)
                                                        <div class="col-md-3 col-sm-6 mb-2">
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input permission-checkbox module-{{ Str::slug($module) }}"
                                                                    type="checkbox" name="permissions[]"
                                                                    value="{{ $permission->id }}"
                                                                    id="permission_{{ $permission->id }}"
                                                                    data-module="{{ Str::slug($module) }}"
                                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                                <label class="form-check-label permission-label"
                                                                    for="permission_{{ $permission->id }}">
                                                                    {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
                                @if ($permissions->count() > 0)
                                    <button type="button" class="btn btn-secondary px-4" id="resetFormBtn">
                                        <i class="bx bx-reset me-1"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Role
                                    </button>
                                @else
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-info px-4">
                                        <i class="bx bx-plus me-1"></i>Create Permissions First
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
            const selectedPermissions = document.getElementById('selectedPermissions');
            const remainingPermissions = document.getElementById('remainingPermissions');
            const totalPermissions = document.getElementById('totalPermissions');

            // ✅ Update permission statistics
            function updatePermissionStats() {
                const checkedCount = document.querySelectorAll('.permission-checkbox:checked').length;
                const total = checkboxes.length;

                if (selectedPermissions) {
                    selectedPermissions.textContent = checkedCount;
                }

                if (remainingPermissions) {
                    remainingPermissions.textContent = total - checkedCount;
                }
            }

            // ✅ Helper: Update button states dynamically
            function updateButtonStates() {
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

                updatePermissionStats();
            }

            // ✅ Select all permissions
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(checkbox => checkbox.checked = true);
                    updateButtonStates();
                });
            }

            // ✅ Deselect all permissions
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(checkbox => checkbox.checked = false);
                    updateButtonStates();
                });
            }

            // ✅ Reset form
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    nameInput.value = '';
                    checkboxes.forEach(checkbox => checkbox.checked = false);
                    nameInput.classList.remove('is-invalid');
                    nameInput.focus();
                    updateButtonStates();
                });
            }

            // ✅ Update button states whenever a checkbox changes
            checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateButtonStates));

            // ✅ Initialize state on page load
            updateButtonStates();

            // ✅ Module collapse toggle animation
            document.querySelectorAll('.module-header').forEach(header => {
                header.addEventListener('click', function() {
                    const toggle = this.querySelector('.module-toggle');
                    toggle.classList.toggle('collapsed');
                });
            });

            // ✅ Select all for each module
            document.querySelectorAll('.select-all-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const moduleSlug = this.getAttribute('data-module');
                    const moduleCheckboxes = document.querySelectorAll(
                        `.permission-checkbox.module-${moduleSlug}`
                    );
                    const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                    
                    moduleCheckboxes.forEach(checkbox => {
                        checkbox.checked = !allChecked;
                    });
                    updateButtonStates();
                });
            });
        });
    </script>
@endsection

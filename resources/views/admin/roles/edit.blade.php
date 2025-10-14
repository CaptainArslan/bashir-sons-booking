@extends('admin.layouts.app')

@section('title', 'Roles')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Roles Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit Role</h5>
                        <div class="col-md-12">
                            <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" placeholder="Enter Role Name" 
                                value="{{ old('name', $role->name) }}" 
                                {{ $isDefaultRole ? 'readonly' : '' }} required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($isDefaultRole)
                                <div class="form-text text-warning">
                                    <i class="bx bx-info-circle me-1"></i>
                                    This is a system role and cannot be modified.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="mb-4">Assign Permissions</h5>

                        <div class="row">
                            @forelse ($permissions as $permission)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="{{ $permission->id }}"
                                            id="permission_{{ $permission->id }}"
                                            {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}
                                            {{ $isDefaultRole ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        No permissions found. Please create permissions first.
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        @if ($permissions->count() > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        @if(!$isDefaultRole)
                                            <div class="btn-group">
                                                <button type="button" id="selectAllBtn" class="btn btn-outline-primary btn-sm">
                                                    <i class="bx bx-check-double me-1"></i>Select All
                                                </button>
                                                <button type="button" id="deselectAllBtn"
                                                    class="btn btn-outline-secondary btn-sm ms-2">
                                                    <i class="bx bx-x me-1"></i>Deselect All
                                                </button>
                                            </div>
                                        @else
                                            <div class="text-muted">
                                                <i class="bx bx-lock me-1"></i>
                                                System role permissions cannot be modified
                                            </div>
                                        @endif
                                        <div>
                                            @if(!$isDefaultRole)
                                                <button type="button" class="btn btn-light px-4 me-2" id="resetFormBtn">
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
                            </div>
                        @endif
                    </div>
                </div>
            </form>
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
            const isDefaultRole = {{ $isDefaultRole ? 'true' : 'false' }};

            // ✅ Helper: Update button states dynamically
            function updateButtonStates() {
                if (isDefaultRole) return; // Skip for default roles
                
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
        });
    </script>
@endsection

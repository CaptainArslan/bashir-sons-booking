@extends('admin.layouts.app')

@section('title', 'Create User')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">User Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create User</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <form action="{{ route('admin.users.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Create User</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter Full Name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" placeholder="Enter Email Address" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                                    name="password" placeholder="Enter Password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                    id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="mb-4">Assign Roles</h5>
                        
                        <div class="row">
                            @forelse ($roles as $role)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" type="checkbox"
                                            name="roles[]" value="{{ $role->id }}"
                                            id="role_{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            <span class="badge bg-{{ $role->name === 'super_admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'employee' ? 'info' : 'success')) }} me-2">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        No roles found. Please create roles first.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        
                        @if($roles->count() > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div class="btn-group">
                                            <button type="button" id="selectAllRolesBtn" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-check-double me-1"></i>Select All
                                            </button>
                                            <button type="button" id="deselectAllRolesBtn" class="btn btn-outline-secondary btn-sm ms-2">
                                                <i class="bx bx-x me-1"></i>Deselect All
                                            </button>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.users.index') }}" class="btn btn-light px-4 me-2">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bx bx-save me-1"></i>Create User
                                            </button>
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
            const selectAllBtn = document.getElementById('selectAllRolesBtn');
            const deselectAllBtn = document.getElementById('deselectAllRolesBtn');
            const checkboxes = document.querySelectorAll('.role-checkbox');

            // Helper: Update button states dynamically
            function updateButtonStates() {
                const checkedCount = document.querySelectorAll('.role-checkbox:checked').length;
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

            // Select all roles
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(checkbox => checkbox.checked = true);
                    updateButtonStates();
                });
            }

            // Deselect all roles
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(checkbox => checkbox.checked = false);
                    updateButtonStates();
                });
            }

            // Update button states whenever a checkbox changes
            checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateButtonStates));

            // Initialize state on page load
            updateButtonStates();
        });
    </script>
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit User')

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
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit User</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter Full Name" 
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" placeholder="Enter Email Address" 
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                                    name="password" placeholder="Leave blank to keep current password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                    id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="mb-4">Profile Information</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                    name="phone" placeholder="Enter Phone Number" 
                                    value="{{ old('phone', $user->profile->phone ?? '') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cnic" class="form-label">CNIC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cnic') is-invalid @enderror" id="cnic"
                                    name="cnic" placeholder="Enter CNIC" 
                                    value="{{ old('cnic', $user->profile->cnic ?? '') }}" required>
                                @error('cnic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender }}" 
                                            {{ old('gender', $user->profile && $user->profile->gender ? $user->profile->gender->value : '') == $gender ? 'selected' : '' }}>
                                            {{ ucfirst($gender) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth"
                                    name="date_of_birth" 
                                    value="{{ old('date_of_birth', $user->profile && $user->profile->date_of_birth ? $user->profile->date_of_birth->format('Y-m-d') : '') }}" required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                    name="address" rows="3" placeholder="Enter Address" required>{{ old('address', $user->profile->address ?? '') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label for="reference_id" class="form-label">Reference ID</label>
                                <input type="text" class="form-control @error('reference_id') is-invalid @enderror" id="reference_id"
                                    name="reference_id" placeholder="Enter Reference ID (Optional)" 
                                    value="{{ old('reference_id', $user->profile->reference_id ?? '') }}">
                                @error('reference_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="mb-4">Manage Roles</h5>
                        
                        <div class="row">
                            @forelse ($roles as $role)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" type="checkbox"
                                            name="roles[]" value="{{ $role->id }}"
                                            id="role_{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                                <i class="bx bx-save me-1"></i>Update User
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

@extends('admin.layouts.app')

@section('title', 'Create User')

@section('styles')
<style>
    .user-card {
        border-left: 4px solid #0d6efd;
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header-info h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .role-group {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .role-group:hover {
        background: #e9ecef;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .role-checkbox {
        cursor: pointer;
    }
    
    .role-label {
        cursor: pointer;
        user-select: none;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .info-box {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 0.75rem;
        margin-top: 0.5rem;
    }
    
    .info-box i {
        color: #0d6efd;
    }
    
    .stats-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
    }
    
    .section-divider {
        border-top: 1px solid #dee2e6;
        margin: 1rem 0;
    }
    
    .form-control, .form-select {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .row {
        margin-bottom: 0.5rem;
    }
    
    .mb-4 {
        margin-bottom: 1rem !important;
    }
</style>
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
        <div class="col-xl-10 mx-auto">
            <div class="card user-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New User</h5>
                </div>
                
                <form action="{{ route('admin.users.store') }}" method="POST" class="row g-3">
                    @csrf
                    
                    <div class="card-body">
                        <!-- User Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Full Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name"
                                       name="name" 
                                       placeholder="Enter Full Name" 
                                       value="{{ old('name') }}" 
                                       required
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email Address 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email"
                                       name="email" 
                                       placeholder="Enter Email Address" 
                                       value="{{ old('email') }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Password Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    Password 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password"
                                       name="password" 
                                       placeholder="Enter Password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted" style="font-size: 0.75rem;">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Password must be at least 8 characters long
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">
                                    Confirm Password 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Confirm Password" 
                                       required>
                                @error('password_confirmation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Role Statistics -->
                        @if($roles->count() > 0)
                            <div class="row">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-body" style="padding: 0.75rem;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <p class="mb-1" style="font-size: 0.85rem;">
                                                        <strong>Total Roles:</strong> 
                                                        <span class="badge bg-info stats-badge" id="totalRoles">
                                                            {{ $roles->count() }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-1" style="font-size: 0.85rem;">
                                                        <strong>Selected:</strong> 
                                                        <span class="badge bg-success stats-badge" id="selectedRoles">
                                                            0
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-1" style="font-size: 0.85rem;">
                                                        <strong>Remaining:</strong> 
                                                        <span class="badge bg-warning stats-badge" id="remainingRoles">
                                                            {{ $roles->count() }}
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

                    <!-- Terminal Assignment Section -->
                    <div class="card-body section-divider" id="terminalSection" style="display: none;">
                        <h5 class="mb-3" style="font-size: 1rem;">
                            <i class="bx bx-map me-2"></i>Terminal Assignment
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <label for="terminal_id" class="form-label">
                                    Terminal Assignment 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('terminal_id') is-invalid @enderror" 
                                        id="terminal_id" 
                                        name="terminal_id">
                                    <option value="">Select Terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}" {{ old('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->name }} - {{ $terminal->city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('terminal_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted" style="font-size: 0.75rem;">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Required when assigning Employee role
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Information Section -->
                    <div class="card-body section-divider">
                        <h5 class="mb-3" style="font-size: 1rem;">
                            <i class="bx bx-user me-2"></i>Profile Information
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    Phone Number 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone"
                                       name="phone" 
                                       placeholder="Enter Phone Number" 
                                       value="{{ old('phone') }}" 
                                       required>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cnic" class="form-label">
                                    CNIC 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('cnic') is-invalid @enderror" 
                                       id="cnic"
                                       name="cnic" 
                                       placeholder="Enter CNIC" 
                                       value="{{ old('cnic') }}" 
                                       required>
                                @error('cnic')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="gender" class="form-label">
                                    Gender 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('gender') is-invalid @enderror" 
                                        id="gender" 
                                        name="gender" 
                                        required>
                                    <option value="">Select Gender</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender }}" {{ old('gender') == $gender ? 'selected' : '' }}>
                                            {{ ucfirst($gender) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">
                                    Date of Birth 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth"
                                       name="date_of_birth" 
                                       value="{{ old('date_of_birth') }}" 
                                       required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label for="address" class="form-label">
                                    Address 
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address"
                                          name="address" 
                                          rows="3" 
                                          placeholder="Enter Address" 
                                          required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label for="reference_id" class="form-label">Reference ID</label>
                                <input type="text" 
                                       class="form-control @error('reference_id') is-invalid @enderror" 
                                       id="reference_id"
                                       name="reference_id" 
                                       placeholder="Enter Reference ID (Optional)" 
                                       value="{{ old('reference_id') }}">
                                @error('reference_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Roles Section -->
                    <div class="card-body section-divider">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0" style="font-size: 1rem;">
                                <i class="bx bx-shield-quarter me-2"></i>Assign Roles
                            </h5>
                            @if($roles->count() > 0)
                                <div class="btn-group">
                                    <button type="button" id="selectAllRolesBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-check-double me-1"></i>Select All
                                    </button>
                                    <button type="button" id="deselectAllRolesBtn" class="btn btn-outline-secondary btn-sm">
                                        <i class="bx bx-x me-1"></i>Deselect All
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        @if($roles->count() > 0)
                            <div class="row">
                                @foreach ($roles as $role)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="role-group">
                                            <div class="form-check">
                                                <input class="form-check-input role-checkbox" 
                                                       type="checkbox"
                                                       name="roles[]" 
                                                       value="{{ $role->id }}"
                                                       id="role_{{ $role->id }}"
                                                       {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label role-label" 
                                                       for="role_{{ $role->id }}">
                                                    <span class="badge bg-{{ $role->name === 'super_admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'employee' ? 'info' : 'success')) }} me-2">
                                                        {{ ucfirst($role->name) }}
                                                    </span>
                                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                No roles found. Please create roles first.
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                @if($roles->count() > 0)
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-4">
                                        <i class="bx bx-x me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create User
                                    </button>
                                @else
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-info px-4">
                                        <i class="bx bx-plus me-1"></i>Create Roles First
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
            const selectAllBtn = document.getElementById('selectAllRolesBtn');
            const deselectAllBtn = document.getElementById('deselectAllRolesBtn');
            const checkboxes = document.querySelectorAll('.role-checkbox');
            const selectedRoles = document.getElementById('selectedRoles');
            const remainingRoles = document.getElementById('remainingRoles');
            const totalRoles = document.getElementById('totalRoles');

            // ✅ Update role statistics
            function updateRoleStats() {
                const checkedCount = document.querySelectorAll('.role-checkbox:checked').length;
                const total = checkboxes.length;
                
                if (selectedRoles) {
                    selectedRoles.textContent = checkedCount;
                }
                
                if (remainingRoles) {
                    remainingRoles.textContent = total - checkedCount;
                }
            }

            // ✅ Helper: Update button states dynamically
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
                
                updateRoleStats();
            }

            // ✅ Select all roles
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(checkbox => checkbox.checked = true);
                    updateButtonStates();
                });
            }

            // ✅ Deselect all roles
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', () => {
                    checkboxes.forEach(checkbox => checkbox.checked = false);
                    updateButtonStates();
                });
            }

            // ✅ Update button states whenever a checkbox changes
            checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateButtonStates));
            
            // ✅ Add visual feedback on checkbox change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.closest('.role-group');
                    if (this.checked) {
                        label.style.background = '#d1ecf1';
                        label.style.borderLeft = '3px solid #0dcaf0';
                    } else {
                        label.style.background = '#f8f9fa';
                        label.style.borderLeft = 'none';
                    }
                });
            });

            // ✅ Handle terminal section visibility based on Employee role
            function toggleTerminalSection() {
                const terminalSection = document.getElementById('terminalSection');
                const terminalSelect = document.getElementById('terminal_id');
                const employeeRoleCheckbox = document.querySelector('input[name="roles[]"][value="' + getEmployeeRoleId() + '"]');
                
                if (employeeRoleCheckbox && employeeRoleCheckbox.checked) {
                    terminalSection.style.display = 'block';
                    terminalSelect.required = true;
                } else {
                    terminalSection.style.display = 'none';
                    terminalSelect.required = false;
                    terminalSelect.value = '';
                }
            }

            // ✅ Get Employee role ID dynamically
            function getEmployeeRoleId() {
                const employeeRoleCheckbox = document.querySelector('input[name="roles[]"]');
                if (employeeRoleCheckbox) {
                    const roleLabel = employeeRoleCheckbox.closest('.role-group').querySelector('.role-label');
                    if (roleLabel && roleLabel.textContent.toLowerCase().includes('employee')) {
                        return employeeRoleCheckbox.value;
                    }
                }
                // Fallback: look for role with "employee" in the text
                const allRoleCheckboxes = document.querySelectorAll('input[name="roles[]"]');
                for (let checkbox of allRoleCheckboxes) {
                    const roleLabel = checkbox.closest('.role-group').querySelector('.role-label');
                    if (roleLabel && roleLabel.textContent.toLowerCase().includes('employee')) {
                        return checkbox.value;
                    }
                }
                return null;
            }

            // ✅ Add event listeners for role changes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleTerminalSection);
            });
            
            // ✅ Initialize state on page load
            updateButtonStates();
            toggleTerminalSection();
        });
    </script>
@endsection

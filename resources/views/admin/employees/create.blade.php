@extends('admin.layouts.app')

@section('title', 'Create Employee')

@section('styles')
    <style>
        .form-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 2rem;
        }

        .form-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .form-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .form-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h6 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .employee-info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <h4><i class="bx bx-user-plus me-2"></i>Create New Employee</h4>
            <p>Add a new employee and assign them to a terminal</p>
        </div>

        <form action="{{ route('admin.employees.store') }}" method="POST">
            @csrf

            <!-- User Information -->
            <div class="form-section">
                <h6><i class="bx bx-user me-2"></i>Employee Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="name" class="form-label required-field">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label required-field">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label required-field">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label required-field">Confirm Password</label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                               id="password_confirmation" name="password_confirmation" required>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Terminal Assignment -->
            <div class="form-section">
                <h6><i class="bx bx-map me-2"></i>Terminal Assignment</h6>
                <div class="row">
                    <div class="col-md-12">
                        <label for="terminal_id" class="form-label required-field">Terminal Assignment</label>
                        <select class="form-select @error('terminal_id') is-invalid @enderror" 
                                id="terminal_id" name="terminal_id" required>
                            <option value="">Select Terminal</option>
                            @foreach($terminals as $terminal)
                                <option value="{{ $terminal->id }}" {{ old('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                    {{ $terminal->name }} - {{ $terminal->city->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('terminal_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted">
                            <i class="bx bx-info-circle me-1"></i>
                            Every employee must be assigned to a terminal
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="form-section">
                <h6><i class="bx bx-user me-2"></i>Profile Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="phone" class="form-label required-field">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="cnic" class="form-label required-field">CNIC</label>
                        <input type="text" class="form-control @error('cnic') is-invalid @enderror" 
                               id="cnic" name="cnic" value="{{ old('cnic') }}" required>
                        @error('cnic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="gender" class="form-label required-field">Gender</label>
                        <select class="form-select @error('gender') is-invalid @enderror" 
                                id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}" {{ old('gender') == $gender ? 'selected' : '' }}>
                                    {{ ucfirst($gender) }}
                                </option>
                            @endforeach
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="date_of_birth" class="form-label required-field">Date of Birth</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label for="address" class="form-label required-field">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label for="reference_id" class="form-label">Reference ID</label>
                        <input type="text" class="form-control @error('reference_id') is-invalid @enderror" 
                               id="reference_id" name="reference_id" value="{{ old('reference_id') }}">
                        @error('reference_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-x me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bx bx-save me-1"></i>Create Employee
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                toastr.error('Please fill in all required fields');
            }
        });
    </script>
@endsection

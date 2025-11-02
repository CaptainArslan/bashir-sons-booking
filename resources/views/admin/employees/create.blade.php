@extends('admin.layouts.app')

@section('title', 'Create Employee')

@section('styles')
    <style>
        .employee-card {
            border-left: 4px solid #28a745;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
        }

        .card-header-info {
            background: #28a745;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px 8px 0 0;
        }

        .card-header-info h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .card-body {
            padding: 1rem !important;
        }

        .row {
            margin-bottom: 0.5rem;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
        }

        .info-box {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border-left: 4px solid #4caf50;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .info-box p {
            margin: 0;
            font-size: 0.85rem;
            color: #2e7d32;
        }

        .section-header {
            color: #495057;
            font-weight: 600;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
            font-size: 0.95rem;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }
    </style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Employees Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Employee</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card employee-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-user-plus me-2"></i>Create New Employee</h5>
                </div>

                <form action="{{ route('admin.employees.store') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Fill in all required fields to
                                create a new employee. Terminal assignment is mandatory.</p>
                        </div>

                        <!-- User Information -->
                        <div class="section-header">
                            <i class="bx bx-user me-2"></i>Employee Information
                        </div>
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
                        <div class="row">
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
                                <label for="password_confirmation" class="form-label required-field">Confirm
                                    Password</label>
                                <input type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    id="password_confirmation" name="password_confirmation" required>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Terminal Assignment -->
                        <div class="section-header mt-3">
                            <i class="bx bx-map me-2"></i>Terminal Assignment
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="terminal_id" class="form-label required-field">Terminal Assignment</label>
                                <select class="form-select @error('terminal_id') is-invalid @enderror" id="terminal_id"
                                    name="terminal_id" required>
                                    <option value="">Select Terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}"
                                            {{ old('terminal_id') == $terminal->id ? 'selected' : '' }}>
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
                        <div class="row">
                            <div class="col-md-12">
                                <label for="routes" class="form-label">Route Assignments</label>
                                <select class="form-select @error('routes') is-invalid @enderror" id="routes"
                                    name="routes[]" multiple disabled>
                                    <option value="">Select a terminal first to load routes</option>
                                </select>
                                @error('routes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Select a terminal first, then routes for that terminal will be loaded
                                </div>
                                <div id="routes-loading" class="form-text text-primary d-none">
                                    <i class="bx bx-loader bx-spin me-1"></i>Loading routes...
                                </div>
                            </div>
                        </div>

                        <!-- Profile Information -->
                        <div class="section-header mt-3">
                            <i class="bx bx-user me-2"></i>Profile Information
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="phone" class="form-label required-field">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}" placeholder="0317-7777777" required maxlength="12">
                                <div class="form-text text-muted" style="font-size: 0.75rem;">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Format: XXXX-XXXXXXX (e.g., 0317-7777777)
                                </div>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="cnic" class="form-label required-field">CNIC</label>
                                <input type="text" class="form-control @error('cnic') is-invalid @enderror"
                                    id="cnic" name="cnic" value="{{ old('cnic') }}" placeholder="34101-1111111-1" required maxlength="15">
                                <div class="form-text text-muted" style="font-size: 0.75rem;">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Format: XXXXX-XXXXXXX-X (e.g., 34101-1111111-1)
                                </div>
                                @error('cnic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="gender" class="form-label required-field">Gender</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender"
                                    name="gender" required>
                                    <option value="">Select Gender</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender }}"
                                            {{ old('gender') == $gender ? 'selected' : '' }}>
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
                        <div class="row">
                            <div class="col-md-12">
                                <label for="address" class="form-label required-field">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                    required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <input type="text" class="form-control @error('notes') is-invalid @enderror"
                                    id="notes" name="notes" value="{{ old('notes') }}">
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.employees.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bx bx-save me-1"></i>Create Employee
                                </button>
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
        $(document).ready(function() {
            // Initialize Select2 for routes
            $('#terminal_id').select2({
                placeholder: 'Select Terminal',
                width: 'resolve',
            });

            $('#routes').select2({
                placeholder: 'Select routes (optional)',
                allowClear: true,
                width: 'resolve',
                dropdownCssClass: 'select2-dropdown-compact'
            });

            // Load routes when terminal is selected
            $('#terminal_id').on('change', function() {
                const terminalId = $(this).val();
                const routesSelect = $('#routes');
                const loadingIndicator = $('#routes-loading');

                if (!terminalId) {
                    routesSelect.html('<option value="">Select a terminal first to load routes</option>');
                    routesSelect.prop('disabled', true).trigger('change');
                    return;
                }

                // Show loading
                loadingIndicator.removeClass('d-none');
                routesSelect.prop('disabled', true);

                // Fetch routes for selected terminal
                $.ajax({
                    url: '{{ route('admin.employees.routes-by-terminal') }}',
                    method: 'GET',
                    data: {
                        terminal_id: terminalId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        routesSelect.empty();

                        if (response.routes && response.routes.length > 0) {
                            routesSelect.append(
                                '<option value="">Select routes (optional)</option>');
                            response.routes.forEach(function(route) {
                                let routeText = route.code + ' - ' + route.name;
                                if (route.first_terminal && route.last_terminal) {
                                    routeText += ' (' + route.first_terminal + ' → ' +
                                        route.last_terminal + ')';
                                }
                                routesSelect.append($('<option></option>')
                                    .attr('value', route.id)
                                    .text(routeText));
                            });
                            routesSelect.prop('disabled', false);
                        } else {
                            routesSelect.append(
                                '<option value="">No routes available for this terminal</option>'
                            );
                        }

                        routesSelect.trigger('change');
                        loadingIndicator.addClass('d-none');
                    },
                    error: function(xhr) {
                        console.error('Error loading routes:', xhr);
                        routesSelect.html(
                            '<option value="">Error loading routes. Please try again.</option>'
                        );
                        routesSelect.prop('disabled', true);
                        loadingIndicator.addClass('d-none');

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Failed to load routes for selected terminal');
                        }
                    }
                });
            });

            // Trigger change on page load if terminal is already selected (for old input)
            @if (old('terminal_id'))
                $('#terminal_id').trigger('change');
            @endif

            // Add compact styling to Select2
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    .select2-container--default .select2-selection--multiple {
                        min-height: calc(1.5em + 0.6rem + 2px) !important;
                        padding: 0.15rem 0.3rem !important;
                    }
                    .select2-container--default .select2-selection--multiple .select2-selection__choice {
                        font-size: 0.75rem !important;
                        padding: 0.15rem 0.4rem !important;
                        margin-top: 0.15rem !important;
                        margin-bottom: 0.15rem !important;
                    }
                    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
                        padding: 0 !important;
                    }
                    .select2-container--default .select2-selection--multiple .select2-selection__clear {
                        font-size: 0.8rem !important;
                    }
                `)
                .appendTo('head');
        });

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

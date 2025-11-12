@extends('admin.layouts.app')

@section('title', 'Create Terminal')

@section('styles')
    <style>
        .terminal-card {
            border-left: 4px solid #17a2b8;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header-info {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
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
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-left: 4px solid #17a2b8;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .info-box p {
            margin: 0;
            font-size: 0.85rem;
            color: #138496;
        }

        .preview-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #6c757d;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .preview-content {
            font-size: 0.85rem;
            color: #495057;
        }

        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        }
    </style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Terminals Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.counter-terminals.index') }}">Counter Terminals</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Create Terminal</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card terminal-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New Terminal</h5>
                </div>

                <form action="{{ route('admin.counter-terminals.store') }}" method="POST" id="terminal-form">
                    @csrf

                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Enter complete terminal
                                information including location, contact details, and optional GPS coordinates. Terminal code
                                will be auto-generated based on city and name.</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="city_id" class="form-label">
                                    City
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('city_id') is-invalid @enderror" id="city_id"
                                    name="city_id" required>
                                    <option value="">Select City</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}"
                                            {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('city_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Terminal Name
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" placeholder="Enter Terminal Name"
                                    value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="code" class="form-label">
                                    Terminal Code
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" placeholder="Enter Terminal Code (e.g., KAR01)"
                                    value="{{ old('code') }}" style="text-transform: uppercase;" required>
                                <div class="form-text">Enter unique terminal code (e.g., KAR01, LAH02)</div>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">
                                    Status
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}"
                                            {{ old('status', 'active') == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="address" class="form-label">
                                    Address
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                    placeholder="Enter Terminal Address" required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    Phone Number
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" placeholder="0317-7777777"
                                    value="{{ old('phone') }}" maxlength="12" required>
                                <div class="form-text text-muted" style="font-size: 0.75rem;">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Format: XXXX-XXXXXXX (e.g., 0317-7777777)
                                </div>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email Address
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" placeholder="Enter Email Address"
                                    value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="landmark" class="form-label">
                                    Landmark
                                </label>
                                <input type="text" class="form-control @error('landmark') is-invalid @enderror"
                                    id="landmark" name="landmark" placeholder="Enter Landmark"
                                    value="{{ old('landmark') }}">
                                @error('landmark')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="latitude" class="form-label">
                                    Latitude
                                </label>
                                <input type="text" class="form-control @error('latitude') is-invalid @enderror"
                                    id="latitude" name="latitude" placeholder="Enter Latitude"
                                    value="{{ old('latitude') }}">
                                @error('latitude')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="longitude" class="form-label">
                                    Longitude
                                </label>
                                <input type="text" class="form-control @error('longitude') is-invalid @enderror"
                                    id="longitude" name="longitude" placeholder="Enter Longitude"
                                    value="{{ old('longitude') }}">
                                @error('longitude')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.counter-terminals.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary px-4" id="resetFormBtn">
                                    <i class="bx bx-reset me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Create Terminal
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
        document.addEventListener('DOMContentLoaded', function() {
            const resetBtn = document.getElementById('resetFormBtn');
            const form = document.getElementById('terminal-form');
            const citySelect = document.getElementById('city_id');
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            // Auto-generate terminal code based on city and name
            function generateTerminalCode() {
                const cityOption = citySelect.selectedOptions[0];
                const name = nameInput.value.trim();

                if (cityOption && name) {   
                    const cityName = cityOption.textContent;
                    const cityCode = cityName.substring(0, 3).toUpperCase();
                    const nameCode = name.substring(0, 2).toUpperCase();
                    const number = Math.floor(Math.random() * 99) + 1;

                    codeInput.value = cityCode + nameCode + number.toString().padStart(2, '0');
                }
            }

            // Event listeners for terminal code generation
            [citySelect, nameInput].forEach(element => {
                element.addEventListener('input', generateTerminalCode);
                element.addEventListener('change', generateTerminalCode);
            });

            // Reset form functionality
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'All entered data will be lost!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, reset it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.reset();
                            Swal.fire({
                                title: 'Reset!',
                                text: 'The form has been cleared.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                });
            }


            // Initialize select2
            $('.select2').select2({
                width: 'resolve'
            });

            // Initial terminal code generation
            generateTerminalCode();
        });
    </script>
@endsection

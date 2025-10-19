@extends('admin.layouts.app')

@section('title', 'Edit Terminal')

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

        .current-info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 0.75rem;
            border-left: 4px solid #6c757d;
            margin-bottom: 1rem;
        }

        .info-item {
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.875rem;
        }

        .info-value {
            color: #6c757d;
            font-size: 0.875rem;
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
                    <li class="breadcrumb-item active" aria-current="page">Edit Terminal</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card terminal-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Terminal</h5>
                </div>

                <form action="{{ route('admin.counter-terminals.update', $terminal->id) }}" method="POST"
                    id="terminal-form">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Update terminal information as
                                needed. Changes will be saved immediately when you submit the form.</p>
                        </div>

                        <!-- Current Terminal Information -->
                        <div class="current-info-box">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <div class="info-label">Current Name:</div>
                                        <div class="info-value text-primary">{{ $terminal->name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <div class="info-label">Current Code:</div>
                                        <div class="info-value text-info">{{ $terminal->code }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <div class="info-label">Current City:</div>
                                        <div class="info-value text-success">{{ $terminal->city->name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <div class="info-label">Current Status:</div>
                                        <div class="info-value">
                                            <span
                                                class="badge {{ $terminal->status->value === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($terminal->status->value) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                            {{ old('city_id', $terminal->city_id) == $city->id ? 'selected' : '' }}>
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
                                    value="{{ old('name', $terminal->name) }}" required autofocus>
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
                                    value="{{ old('code', $terminal->code) }}" style="text-transform: uppercase;" required>
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
                                            {{ old('status', $terminal->status->value) == $status ? 'selected' : '' }}>
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
                                    placeholder="Enter Terminal Address" required>{{ old('address', $terminal->address) }}</textarea>
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
                                    id="phone" name="phone" placeholder="Enter Phone Number"
                                    value="{{ old('phone', $terminal->phone) }}" required>
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
                                    value="{{ old('email', $terminal->email) }}">
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
                                    value="{{ old('landmark', $terminal->landmark) }}">
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
                                    value="{{ old('latitude', $terminal->latitude) }}">
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
                                    value="{{ old('longitude', $terminal->longitude) }}">
                                @error('longitude')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Terminal Preview Section -->
                        <div class="preview-section" id="terminal-preview">
                            <div class="preview-content" id="terminal-details"></div>
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
                                    <i class="bx bx-save me-1"></i>Update Terminal
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
            const addressTextarea = document.getElementById('address');
            const landmarkInput = document.getElementById('landmark');
            const phoneInput = document.getElementById('phone');
            const emailInput = document.getElementById('email');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const statusSelect = document.getElementById('status');

            // ✅ Update terminal preview
            function updateTerminalPreview() {
                const cityOption = citySelect.selectedOptions[0];
                const name = nameInput.value.trim();
                const code = codeInput.value.trim();
                const address = addressTextarea.value.trim();
                const phone = phoneInput.value.trim();
                const status = statusSelect.value;

                if (cityOption && name && code) {
                    const statusBadge = status === 'active' ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-secondary">Inactive</span>';

                    let previewHtml = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong>${name}</strong> <small class="text-muted">(${code})</small>
                    </div>
                    <div>${statusBadge}</div>
                </div>
                <div class="mb-1"><strong>City:</strong> ${cityOption.textContent}</div>
            `;

                    if (address) {
                        previewHtml += `<div class="mb-1"><strong>Address:</strong> ${address}</div>`;
                    }

                    if (phone) {
                        previewHtml += `<div class="mb-1"><strong>Phone:</strong> ${phone}</div>`;
                    }

                    if (emailInput.value.trim()) {
                        previewHtml += `<div class="mb-1"><strong>Email:</strong> ${emailInput.value.trim()}</div>`;
                    }

                    if (landmarkInput.value.trim()) {
                        previewHtml +=
                            `<div class="mb-1"><strong>Landmark:</strong> ${landmarkInput.value.trim()}</div>`;
                    }

                    if (latitudeInput.value.trim() && longitudeInput.value.trim()) {
                        previewHtml +=
                            `<div class="mb-1"><strong>Coordinates:</strong> ${latitudeInput.value.trim()}, ${longitudeInput.value.trim()}</div>`;
                    }

                    document.getElementById('terminal-details').innerHTML = previewHtml;
                }
            }

            // ✅ Event listeners
            [citySelect, nameInput, codeInput, addressTextarea, landmarkInput, phoneInput, emailInput,
                latitudeInput, longitudeInput, statusSelect
            ].forEach(element => {
                element.addEventListener('input', updateTerminalPreview);
                element.addEventListener('change', updateTerminalPreview);
            });

            // ✅ Reset form functionality
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
                        location.reload();
                    }
                });
            }

            // ✅ Initial update
            updateTerminalPreview();
            
            $('.select2').select2({
                width: 'resolve',
                // theme: "classic"
            });
        });
    </script>
@endsection

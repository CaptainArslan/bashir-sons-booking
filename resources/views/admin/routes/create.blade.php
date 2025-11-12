@extends('admin.layouts.app')

@section('title', 'Create Route')

@section('styles')
    <style>
        .route-card {
            border-left: 4px solid #0d6efd;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
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
            border-left: 4px solid #2196f3;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .info-box p {
            margin: 0;
            font-size: 0.85rem;
            color: #1976d2;
        }

        .section-divider {
            border-top: 1px solid #e9ecef;
            margin: 1rem 0;
            padding-top: 1rem;
        }

        .section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .form-text {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .stops-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid #0dcaf0;
        }

        .stop-item {
            transition: all 0.2s ease;
            border-left: 3px solid #0d6efd !important;
            background: #ffffff;
            border-radius: 6px;
        }

        .stop-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
            transform: translateX(2px);
        }

        .form-check-label {
            cursor: pointer;
            font-size: 0.875rem;
            transition: color 0.2s ease;
        }

        .form-check-input:checked+.form-check-label {
            color: #0d6efd;
            font-weight: 600;
        }

        .add-stop-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .add-stop-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.25);
            color: white;
        }

        .badge {
            font-size: 0.75rem;
            width: 24px;
            height: 24px;
        }

        .stop-header {
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Route</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-12 mx-auto">
            <div class="card route-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-plus-circle me-2"></i>Create New Route</h5>
                </div>

                <form action="{{ route('admin.routes.store') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Tip:</strong> Select from and to cities. The
                                route code and name will be auto-generated based on the selected cities.
                            </p>
                        </div>

                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-route me-1"></i>Basic Information
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="from_city_id" class="form-label">
                                    From City
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('from_city_id') is-invalid @enderror"
                                    id="from_city_id" name="from_city_id" required autofocus>
                                    <option value="">Select From City</option>
                                    @foreach ($cities as $city)
                                        @php
                                            $cityCode = strtoupper(
                                                substr(preg_replace('/[^a-zA-Z0-9]/', '', $city->name), 0, 3),
                                            );
                                        @endphp
                                        <option value="{{ $city->id }}" data-code="{{ $cityCode }}"
                                            {{ old('from_city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_city_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="to_city_id" class="form-label">
                                    To City
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('to_city_id') is-invalid @enderror"
                                    id="to_city_id" name="to_city_id" required>
                                    <option value="">Select To City</option>
                                    @foreach ($cities as $city)
                                        @php
                                            $cityCode = strtoupper(
                                                substr(preg_replace('/[^a-zA-Z0-9]/', '', $city->name), 0, 3),
                                            );
                                        @endphp
                                        <option value="{{ $city->id }}" data-code="{{ $cityCode }}"
                                            {{ old('to_city_id') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_city_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="code" class="form-label">
                                    Route Code (Auto-generated)
                                </label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" placeholder="Route code will be auto-generated"
                                    value="{{ old('code') }}" style="text-transform: uppercase;" readonly>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Code will be auto-generated based on city names (first 3 letters)
                                </div>
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
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Route Terminals Section -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-map me-1"></i>Route Terminals
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="stops-section">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-map text-primary me-2" style="font-size: 1.2rem;"></i>
                                            <h6 class="mb-0 fw-bold text-primary">Route Terminals</h6>
                                        </div>
                                        <button type="button" class="add-stop-btn" id="add-terminal-btn">
                                            <i class="bx bx-plus me-1"></i>Add Terminal
                                        </button>
                                    </div>
                                    <div id="stops-container" class="row g-4">
                                        <!-- Terminals will be added here dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Action Buttons -->
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('admin.routes.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Back to List
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary px-4">
                                        <i class="bx bx-x me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Route
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

            // ----------------------------------
            // Auto-generate Route Code
            // ----------------------------------
            function generateRouteInfo() {
                const $fromOption = $('#from_city_id option:selected');
                const $toOption = $('#to_city_id option:selected');

                const fromCode = $fromOption.attr('data-code');
                const toCode = $toOption.attr('data-code');

                if (fromCode && toCode) {
                    $('#code').val(`${fromCode}-${toCode}`);
                } else if (fromCode) {
                    $('#code').val(`${fromCode}-`);
                } else {
                    $('#code').val('');
                }
            }

            $('#from_city_id, #to_city_id').on('change', generateRouteInfo);
            generateRouteInfo();

            // ----------------------------------
            // Route Terminals Management
            // ----------------------------------
            let stopCounter = 0;
            const stopsContainer = document.getElementById('stops-container');
            const addTerminalBtn = document.getElementById('add-terminal-btn');

            // Add first stop by default
            addStop();

            addTerminalBtn.addEventListener('click', function() {
                addStop();
            });

            // -----------------------------
            // Add Stop
            // -----------------------------
            function addStop() {
                const currentStops = stopsContainer.querySelectorAll('.stop-item').length;
                stopCounter = currentStops + 1;

                const stopDiv = document.createElement('div');
                stopDiv.className = 'stop-item border rounded p-3 mb-1 col-md-4';
                stopDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                            ${stopCounter}
                        </div>
                        <span class="stop-header text-primary">Terminal ${stopCounter}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn" title="Remove this stop">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
    
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Terminal <span class="text-danger">*</span></label>
                        <select class="form-select select2 terminal-select" name="stops[${stopCounter}][terminal_id]" required>
                            <option value="">Select Terminal</option>
                            @foreach ($terminals as $terminal)
                                <option value="{{ $terminal->id }}">
                                    {{ $terminal->name }} - {{ $terminal->city->name }} ({{ $terminal->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
    
                    <input type="hidden" class="sequence-input" name="stops[${stopCounter}][sequence]" value="${stopCounter}">
    
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="stops[${stopCounter}][online_booking_allowed]" value="1" id="online_booking_${stopCounter}" checked>
                            <label class="form-check-label" for="online_booking_${stopCounter}">
                                Allow Online Booking from this stop
                            </label>
                        </div>
                    </div>
                </div>
            `;

                stopsContainer.appendChild(stopDiv);

                // Initialize Select2 for new stop
                const terminalSelect = stopDiv.querySelector('.terminal-select');
                $(terminalSelect).select2({
                    width: 'resolve',
                    placeholder: 'Select Terminal'
                });

                // Remove stop and reindex
                const removeBtn = stopDiv.querySelector('.remove-stop-btn');
                removeBtn.addEventListener('click', function() {
                    $(terminalSelect).select2('destroy');
                    stopDiv.remove();
                    updateSequences();
                });
            }

            // -----------------------------
            // Update Stop Sequences and Numbers
            // -----------------------------
            function updateSequences() {
                const stopItems = stopsContainer.querySelectorAll('.stop-item');

                stopItems.forEach((item, index) => {
                    const newIndex = index + 1;

                    // Update hidden sequence input
                    const sequenceInput = item.querySelector('.sequence-input');
                    if (sequenceInput) sequenceInput.value = newIndex;

                    // Update badge number
                    const badge = item.querySelector('.badge');
                    if (badge) badge.textContent = newIndex;

                    // Update terminal title
                    const stopHeader = item.querySelector('.stop-header');
                    if (stopHeader) stopHeader.textContent = `Terminal ${newIndex}`;

                    // Update input names
                    const inputs = item.querySelectorAll('select, input');
                    inputs.forEach(input => {
                        if (input.name && input.name.includes('stops[')) {
                            input.name = input.name.replace(/stops\[\d+\]/, `stops[${newIndex}]`);
                        }
                    });

                    // Update checkbox IDs and labels
                    const checkbox = item.querySelector('.form-check-input');
                    const label = item.querySelector('.form-check-label');
                    if (checkbox && label) {
                        const newId = `online_booking_${newIndex}`;
                        checkbox.id = newId;
                        label.setAttribute('for', newId);
                    }
                });

                // Update global counter to reflect actual number of stops
                stopCounter = stopItems.length;
            }

            // Initialize Select2 for existing elements
            $('.select2').select2({
                width: 'resolve',
            });
        });
    </script>

@endsection

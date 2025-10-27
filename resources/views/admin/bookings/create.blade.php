@extends('admin.layouts.app')

@section('title', 'Create Booking')

@section('styles')
    <style>
        .booking-card {
            border-left: 4px solid #0d6efd;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header-booking {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px 8px 0 0;
        }

        .card-header-booking h5 {
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

        .search-icon {
            font-size: 1.5rem;
            color: #667eea;
        }
    </style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Booking Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Booking</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card booking-card">
                <div class="card-header-booking">
                    <h5><i class="bx bx-search-alt me-2"></i>Search Available Seats</h5>
                </div>

                <form action="{{ route('admin.bookings.search') }}" method="POST">
                    @csrf

                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Select departure and destination terminals, then choose date and available time.</p>
                        </div>

                        <!-- Terminal Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="from_terminal_id" class="form-label">
                                    From Terminal <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('from_terminal_id') is-invalid @enderror"
                                    id="from_terminal_id" name="from_terminal_id" required>
                                    <option value="">Select starting terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}"
                                            {{ old('from_terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->name }} - {{ $terminal->city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_terminal_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="to_terminal_id" class="form-label">
                                    To Terminal <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('to_terminal_id') is-invalid @enderror"
                                    id="to_terminal_id" name="to_terminal_id" required>
                                    <option value="">Select destination terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}"
                                            {{ old('to_terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->name }} - {{ $terminal->city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_terminal_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date Selection -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="departure_date" class="form-label">
                                    Departure Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('departure_date') is-invalid @enderror"
                                    id="departure_date" name="departure_date" value="{{ old('departure_date', date('Y-m-d')) }}"
                                    min="{{ date('Y-m-d') }}" required>
                                @error('departure_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Available Times (loaded dynamically) -->
                        <div class="row mb-3" id="time-selection-container" style="display: none;">
                            <div class="col-md-12">
                                <label for="departure_time" class="form-label">
                                    Available Departure Times <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('departure_time') is-invalid @enderror"
                                    id="departure_time" name="departure_time" required>
                                    <option value="">Select departure time</option>
                                </select>
                                <small class="text-muted">Times are based on active timetables for this route</small>
                                @error('departure_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Route Info (hidden field, populated dynamically) -->
                        <input type="hidden" id="route_id" name="route_id" value="{{ old('route_id') }}">

                        <!-- Loading Indicator -->
                        <div class="row mb-3" id="loading-indicator" style="display: none;">
                            <div class="col-12 text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading available times...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-light px-4">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-search me-1"></i>Search Seats
                            </button>
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
            $('.select2').select2({
                width: 'resolve'
            });

            // Trigger time loading when all required fields are selected
            function loadAvailableTimes() {
                const fromTerminalId = $('#from_terminal_id').val();
                const toTerminalId = $('#to_terminal_id').val();
                const departureDate = $('#departure_date').val();

                // Reset time selection
                $('#departure_time').html('<option value="">Select departure time</option>');
                $('#time-selection-container').hide();
                $('#route_id').val('');

                // Validate all fields are selected
                if (!fromTerminalId || !toTerminalId || !departureDate) {
                    return;
                }

                // Validate from and to are different
                if (fromTerminalId === toTerminalId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Selection',
                        text: 'Departure and destination terminals must be different.'
                    });
                    return;
                }

                // Show loading indicator
                $('#loading-indicator').show();

                // Make AJAX request to get available times
                $.ajax({
                    url: "{{ route('admin.bookings.get-available-times') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        from_terminal_id: fromTerminalId,
                        to_terminal_id: toTerminalId,
                        departure_date: departureDate
                    },
                    success: function(response) {
                        $('#loading-indicator').hide();
                        
                        if (response.success && response.data.times.length > 0) {
                            // Populate time dropdown
                            let options = '<option value="">Select departure time</option>';
                            response.data.times.forEach(function(time) {
                                options += `<option value="${time.time}" data-route-id="${time.route_id}">
                                    ${time.time} - ${time.route_name}
                                </option>`;
                            });
                            
                            $('#departure_time').html(options);
                            $('#time-selection-container').show();
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'No Available Times',
                                text: 'No timetables found for this route and date. You can still proceed with custom time.',
                                showCancelButton: true,
                                confirmButtonText: 'Enter Custom Time',
                                cancelButtonText: 'Change Selection'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Allow manual time entry
                                    $('#departure_time').replaceWith(
                                        '<input type="time" class="form-control" id="departure_time" name="departure_time" required>'
                                    );
                                    if (response.data.route_id) {
                                        $('#route_id').val(response.data.route_id);
                                    }
                                    $('#time-selection-container').show();
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#loading-indicator').hide();
                        
                        let errorMessage = 'Unable to load available times. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            }

            // When departure time is selected, set the route_id
            $(document).on('change', '#departure_time', function() {
                const selectedOption = $(this).find('option:selected');
                const routeId = selectedOption.data('route-id');
                if (routeId) {
                    $('#route_id').val(routeId);
                }
            });

            // Trigger loading when terminals or date changes
            $('#from_terminal_id, #to_terminal_id, #departure_date').on('change', loadAvailableTimes);
        });
    </script>
@endsection


@extends('admin.layouts.app')

@section('title', 'Manage Fares - ' . $route->name)


@section('styles')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        }

        .bg-gradient-light {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        }

        .fare-item {
            transition: all 0.3s ease;
        }

        .fare-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            font-weight: 600;
        }

        .final-fare {
            transition: all 0.3s ease;
        }


        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card {
            border-radius: 10px;
            overflow: hidden;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }

        .route-stops-preview .badge {
            font-size: 0.7rem;
            margin: 2px;
        }

        .validation-errors {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .fare-item.has-errors {
            border-left-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.1) !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Manage Fares</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $route->name }} - Fares</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Routes
                </a>
            </div>
        </div>

        <!-- Route Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bx bx-route me-2"></i>Route Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary">{{ $route->name }}</h5>
                        <p class="text-muted mb-1"><strong>Code:</strong> {{ $route->code }}</p>
                        <p class="text-muted mb-1"><strong>Direction:</strong>
                            <span class="badge {{ $route->direction === 'forward' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($route->direction) }}
                            </span>
                        </p>
                        <p class="text-muted mb-0"><strong>Status:</strong>
                            {!! \App\Enums\RouteStatusEnum::getStatusBadge(
                                $route->status instanceof \App\Enums\RouteStatusEnum ? $route->status->value : $route->status,
                            ) !!}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-secondary">Route Stops ({{ $routeStops->count() }})</h6>
                        <div class="route-stops-preview">
                            @foreach ($routeStops as $index => $stop)
                                <span class="badge bg-light text-dark me-1 mb-1">
                                    {{ $stop->sequence }}. {{ $stop->terminal->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fares Management Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-gradient-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="bx bx-money me-2"></i>Fare Management
                    <small class="ms-2 opacity-75">Set fares for all route segments</small>
                </h6>
                <div>
                    <button type="button" class="btn btn-sm btn-success" onclick="saveAllFares()">
                        <i class="bx bx-save me-1"></i>Save All Fares
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <i class="bx bx-info-circle me-2 fs-5"></i>
                    <div>
                        <strong>Instructions:</strong> Set base fares and optional discounts for each route segment.
                        Final fares are calculated automatically. All fares are set to <strong>Active</strong> by default.
                        <br><small class="text-muted mt-1 d-block">
                            <i class="bx bx-info-circle me-1"></i>
                            All route segments are automatically generated. You can modify fares but cannot remove route
                            segments.
                        </small>
                    </div>
                </div>

                <form id="faresForm">
                    @csrf
                    <div id="faresContainer">
                        @php
                            $fareIndex = 0;
                        @endphp
                        @for ($i = 0; $i < $routeStops->count(); $i++)
                            @for ($j = $i + 1; $j < $routeStops->count(); $j++)
                                @php
                                    $fromStop = $routeStops[$i];
                                    $toStop = $routeStops[$j];
                                    $fareKey = $fromStop->id . '_' . $toStop->id;
                                    $existingFare = $existingFares[$fareKey] ?? null;
                                @endphp
                                <div class="card mb-4 fare-item border-left-primary shadow-sm"
                                    data-from="{{ $fromStop->id }}" data-to="{{ $toStop->id }}">
                                    <div class="card-header bg-gradient-light border-bottom-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="bx bx-route text-primary fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 text-primary fw-bold">
                                                        {{ $fromStop->terminal->name }} → {{ $toStop->terminal->name }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="bx bx-map me-1"></i>
                                                        {{ $fromStop->terminal->city->name }} →
                                                        {{ $toStop->terminal->city->name }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary">{{ $fromStop->sequence }} →
                                                    {{ $toStop->sequence }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bx bx-money me-1 text-success"></i>Base Fare (PKR)
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">PKR</span>
                                                    <input type="number" class="form-control base-fare"
                                                        name="fares[{{ $fareIndex }}][base_fare]"
                                                        value="{{ $existingFare ? $existingFare->base_fare : '' }}"
                                                        min="1" max="100000" step="0.01" required
                                                        placeholder="Enter base fare">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    <i class="bx bx-percent me-1 text-warning"></i>Discount Type
                                                </label>
                                                <select class="form-select discount-type"
                                                    name="fares[{{ $fareIndex }}][discount_type]">
                                                    <option value="">No Discount</option>
                                                    <option value="flat"
                                                        {{ $existingFare && $existingFare->discount_type === 'flat' ? 'selected' : '' }}>
                                                        Flat Amount</option>
                                                    <option value="percent"
                                                        {{ $existingFare && $existingFare->discount_type === 'percent' ? 'selected' : '' }}>
                                                        Percentage</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    <i class="bx bx-calculator me-1 text-info"></i>Discount Value
                                                </label>
                                                <input type="number" class="form-control discount-value"
                                                    name="fares[{{ $fareIndex }}][discount_value]"
                                                    value="{{ $existingFare ? $existingFare->discount_value : '' }}"
                                                    min="0" step="0.01" placeholder="0.00" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    <i class="bx bx-check-circle me-1 text-success"></i>Final Fare (PKR)
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white">PKR</span>
                                                    <input type="number" class="form-control final-fare" readonly
                                                        value="{{ $existingFare ? $existingFare->final_fare : '' }}"
                                                        style="background-color: #d4edda; font-weight: bold; color: #155724;">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    <i class="bx bx-info-circle me-1 text-info"></i>Info
                                                </label>
                                                <div class="form-control-plaintext bg-light p-2 rounded">
                                                    <small class="text-muted">
                                                        <i class="bx bx-check-circle text-success me-1"></i>Active
                                                    </small>
                                                </div>
                                                <input type="hidden" name="fares[{{ $fareIndex }}][status]" value="active">
                                            </div>
                                        </div>
                                        <input type="hidden" name="fares[{{ $fareIndex }}][from_stop_id]"
                                            value="{{ $fromStop->id }}">
                                        <input type="hidden" name="fares[{{ $fareIndex }}][to_stop_id]"
                                            value="{{ $toStop->id }}">
                                    </div>
                                </div>
                                @php
                                    $fareIndex++;
                                @endphp
                            @endfor
                        @endfor
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Saving fares...</p>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize fare calculation event listeners
            $('.base-fare, .discount-type, .discount-value').on('input change', calculateFare);
            
            // Handle discount type change to enable/disable discount value field
            $(document).on('change', '.discount-type', function() {
                const card = $(this).closest('.fare-item');
                const discountValueInput = card.find('.discount-value');
                const discountType = $(this).val();
                
                if (discountType) {
                    discountValueInput.prop('disabled', false);
                    discountValueInput.prop('required', true);
                    
                    if (discountType === 'percent') {
                        discountValueInput.attr('max', '100');
                        discountValueInput.attr('placeholder', '0-100%');
                    } else if (discountType === 'flat') {
                        discountValueInput.removeAttr('max');
                        discountValueInput.attr('placeholder', '0.00');
                    }
                } else {
                    discountValueInput.prop('disabled', true);
                    discountValueInput.prop('required', false);
                    discountValueInput.val('');
                    discountValueInput.removeClass('is-invalid');
                }
                
                // Trigger calculation
                calculateFare.call(this);
            });
        });

        function calculateFare() {
            const card = $(this).closest('.fare-item');
            const baseFareInput = card.find('.base-fare');
            const discountTypeSelect = card.find('.discount-type');
            const discountValueInput = card.find('.discount-value');
            const finalFareInput = card.find('.final-fare');

            const baseFare = parseFloat(baseFareInput.val()) || 0;
            const discountType = discountTypeSelect.val();
            const discountValue = parseFloat(discountValueInput.val()) || 0;

            // Clear previous validation states
            clearValidationStates(card);

            let finalFare = baseFare;
            let validationErrors = [];

            // Validate base fare
            if (baseFare <= 0) {
                validationErrors.push('Base fare must be greater than 0');
                baseFareInput.addClass('is-invalid');
            } else if (baseFare > 100000) {
                validationErrors.push('Base fare cannot exceed PKR 100,000');
                baseFareInput.addClass('is-invalid');
            }

            // Enable/disable discount value input based on discount type
            if (discountType) {
                discountValueInput.prop('disabled', false);
                discountValueInput.prop('required', true);

                if (discountType === 'percent') {
                    discountValueInput.attr('max', '100');
                    discountValueInput.attr('placeholder', '0-100%');

                    // Validate percentage discount value
                    if (discountValue <= 0) {
                        validationErrors.push('Discount percentage must be greater than 0');
                        discountValueInput.addClass('is-invalid');
                    } else if (discountValue > 100) {
                        validationErrors.push('Discount percentage cannot exceed 100%');
                        discountValueInput.addClass('is-invalid');
                    }

                    // Calculate percentage discount
                    if (discountValue > 0 && baseFare > 0) {
                        const discountAmount = (baseFare * discountValue / 100);
                        finalFare = Math.max(0, baseFare - discountAmount);
                    }
                } else if (discountType === 'flat') {
                    discountValueInput.removeAttr('max');
                    discountValueInput.attr('placeholder', '0.00');

                    // Validate flat discount value
                    if (discountValue <= 0) {
                        validationErrors.push('Discount amount must be greater than 0');
                        discountValueInput.addClass('is-invalid');
                    } else if (discountValue > baseFare) {
                        validationErrors.push('Discount amount cannot exceed base fare');
                        discountValueInput.addClass('is-invalid');
                    }

                    // Calculate flat discount
                    if (discountValue > 0 && baseFare > 0) {
                        const discountAmount = Math.min(discountValue, baseFare);
                        finalFare = Math.max(0, baseFare - discountAmount);
                    }
                }
            } else {
                discountValueInput.prop('disabled', true);
                discountValueInput.prop('required', false);
                discountValueInput.val('');
                discountValueInput.removeClass('is-invalid');
            }

            // Update final fare
            finalFareInput.val(finalFare.toFixed(2));

            // Add visual feedback
            if (validationErrors.length > 0) {
                finalFareInput.css({
                    'background-color': '#f8d7da',
                    'color': '#721c24',
                    'border-color': '#f5c6cb'
                });
                showValidationErrors(card, validationErrors);
            } else {
                finalFareInput.css({
                    'background-color': '#d4edda',
                    'color': '#155724',
                    'border-color': '#c3e6cb'
                });
                hideValidationErrors(card);
            }
        }


        function clearValidationStates(card) {
            card.find('.is-invalid').removeClass('is-invalid');
            card.find('.validation-errors').remove();
            card.removeClass('has-errors');
        }

        function showValidationErrors(card, errors) {
            // Remove existing error messages
            card.find('.validation-errors').remove();

            if (errors.length > 0) {
                card.addClass('has-errors');
                const errorHtml = `
            <div class="validation-errors mt-2">
                <div class="alert alert-danger py-2 mb-0">
                    <ul class="mb-0 ps-3">
                        ${errors.map(error => `<li class="small">${error}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;
                card.find('.card-body').append(errorHtml);
            }
        }

        function hideValidationErrors(card) {
            card.find('.validation-errors').remove();
            card.removeClass('has-errors');
        }


        function saveAllFares() {
            let validationErrors = [];
            let emptyFares = 0;
            let totalFares = 0;

            $('.fare-item').each(function() {
                totalFares++;
                const card = $(this);
                const baseFare = parseFloat(card.find('.base-fare').val()) || 0;
                const discountType = card.find('.discount-type').val();
                const discountValue = parseFloat(card.find('.discount-value').val()) || 0;

                let fareErrors = [];

                // Validate base fare
                if (!baseFare || baseFare <= 0) {
                    fareErrors.push('Base fare is required');
                    card.find('.base-fare').addClass('is-invalid');
                    emptyFares++;
                }

                // Validate discount value if discount type is selected
                if (discountType) {
                    if (!discountValue || discountValue <= 0) {
                        fareErrors.push('Discount value is required when discount type is selected');
                        card.find('.discount-value').addClass('is-invalid');
                    } else {
                        // Additional validation based on discount type
                        if (discountType === 'percent' && discountValue > 100) {
                            fareErrors.push('Discount percentage cannot exceed 100%');
                            card.find('.discount-value').addClass('is-invalid');
                        } else if (discountType === 'flat' && discountValue > baseFare) {
                            fareErrors.push('Discount amount cannot exceed base fare');
                            card.find('.discount-value').addClass('is-invalid');
                        }
                    }
                }

                // Show or hide errors for this fare
                if (fareErrors.length > 0) {
                    showValidationErrors(card, fareErrors);
                    validationErrors = validationErrors.concat(fareErrors);
                } else {
                    hideValidationErrors(card);
                    card.find('.is-invalid').removeClass('is-invalid');
                }
            });

            if (totalFares === 0) {
                Swal.fire({
                    title: 'No Fares!',
                    text: 'No fares to save. Please refresh the page.',
                    icon: 'info',
                    confirmButtonColor: '#17a2b8',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (validationErrors.length > 0) {
                let errorMessage = 'Please fix the following errors before saving:<br>';
                errorMessage += validationErrors.slice(0, 5).map(error => `• ${error}`).join('<br>');
                if (validationErrors.length > 5) {
                    errorMessage += `<br>... and ${validationErrors.length - 5} more errors`;
                }
                Swal.fire({
                    title: 'Validation Error!',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (emptyFares > 0) {
                Swal.fire({
                    title: 'Missing Fares!',
                    text: `Please fill in base fares for all ${emptyFares} route segment(s).`,
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Save Fares?',
                text: `Are you sure you want to save ${totalFares} fare(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Save All!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithSave();
                }
            });
        }
        
        function proceedWithSave() {
            const form = $('#faresForm');
            const formData = new FormData(form[0]);

            // Remove empty discount_value fields when no discount type is selected
            $('.fare-item').each(function() {
                const discountType = $(this).find('.discount-type').val();
                const discountValueInput = $(this).find('.discount-value');
                
                if (!discountType || discountType === '') {
                    // Remove the discount_value input from form data if no discount type
                    const inputName = discountValueInput.attr('name');
                    if (inputName) {
                        formData.delete(inputName);
                    }
                }
            });

            $('#loadingModal').modal('show');

            $.ajax({
                url: '{{ route('admin.routes.fares.store', $route->id) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#loadingModal').modal('hide');

                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Handle error response with detailed error messages
                        let errorMessage = response.message || 'Failed to save fares';
                        
                        if (response.errors && response.errors.length > 0) {
                            errorMessage += '<br><br><strong>Errors:</strong><br>';
                            errorMessage += response.errors.slice(0, 10).map(error => `• ${error}`).join('<br>');
                            
                            if (response.errors.length > 10) {
                                errorMessage += `<br>... and ${response.errors.length - 10} more errors`;
                            }
                        }

                        Swal.fire({
                            title: 'Error!',
                            html: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    const response = xhr.responseJSON;
                    let errorMessage = 'An error occurred while saving fares';

                    if (response?.errors) {
                        const errors = Object.values(response.errors).flat();
                        errorMessage = errors.join('<br>');
                    } else if (response?.message) {
                        errorMessage = response.message;
                    }

                    Swal.fire({
                        title: 'Error!',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

    </script>
@endsection

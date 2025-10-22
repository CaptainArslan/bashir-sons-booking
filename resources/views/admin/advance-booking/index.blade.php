@extends('admin.layouts.app')

@section('title', 'Advance Booking Settings')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Advance Booking Settings</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Advance Booking Settings</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Advance Booking Configuration</h4>
                        <p class="text-muted mb-0">Enable or disable advance booking functionality for your transport system.</p>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form id="advance-booking-form" method="POST" action="{{ route('admin.advance-booking.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Advance Booking Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="advance_booking_enable" 
                                                   name="advance_booking_enable" 
                                                   value="1"
                                                   {{ ($settings && $settings->advance_booking_enable) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="advance_booking_enable">
                                                <span id="status-text">{{ ($settings && $settings->advance_booking_enable) ? 'Enabled' : 'Disabled' }}</span>
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            <i class="bx bx-info-circle me-1"></i>
                                            When enabled, customers will be able to book tickets in advance. When disabled, only same-day bookings will be allowed.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i>
                                            Save Settings
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="bx bx-info-circle me-2"></i>
                                            About Advance Booking
                                        </h6>
                                        <p class="mb-2">
                                            <strong>When Enabled:</strong> Customers can book tickets for future dates based on your system's availability.
                                        </p>
                                        <p class="mb-0">
                                            <strong>When Disabled:</strong> Only same-day bookings will be available to customers.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Status Display -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded-circle bg-{{ ($settings && $settings->advance_booking_enable) ? 'success' : 'danger' }} bg-soft">
                                                        <div class="avatar-title rounded-circle bg-{{ ($settings && $settings->advance_booking_enable) ? 'success' : 'danger' }} text-white">
                                                            <i class="bx {{ ($settings && $settings->advance_booking_enable) ? 'bx-check' : 'bx-x' }} font-size-20"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">
                                                        Current Status: 
                                                        <span class="badge bg-{{ ($settings && $settings->advance_booking_enable) ? 'success' : 'danger' }}">
                                                            {{ ($settings && $settings->advance_booking_enable) ? 'Enabled' : 'Disabled' }}
                                                        </span>
                                                    </h6>
                                                    <p class="text-muted mb-0">
                                                        {{ ($settings && $settings->advance_booking_enable) 
                                                            ? 'Advance booking is currently active and available to customers.' 
                                                            : 'Advance booking is currently disabled. Only same-day bookings are allowed.' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle form switch change
    $('#advance_booking_enable').change(function() {
        const isChecked = $(this).is(':checked');
        $('#status-text').text(isChecked ? 'Enabled' : 'Disabled');
        
        // Update status badge color
        const statusBadge = $('.badge');
        if (isChecked) {
            statusBadge.removeClass('bg-danger').addClass('bg-success').text('Enabled');
        } else {
            statusBadge.removeClass('bg-success').addClass('bg-danger').text('Disabled');
        }
    });

    // Handle form submission
    $('#advance-booking-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isEnabled = $('#advance_booking_enable').is(':checked');
        formData.set('advance_booking_enable', isEnabled ? '1' : '0');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Show success message
                toastr.success(response.message || 'Settings updated successfully!');
                
                // Reload page after a short delay
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.keys(errors).forEach(function(key) {
                        toastr.error(errors[key][0]);
                    });
                } else {
                    toastr.error('An error occurred while updating settings.');
                }
            }
        });
    });
});
</script>
@endpush

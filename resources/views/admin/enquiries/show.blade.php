@extends('admin.layouts.app')

@section('title', 'Enquiry Details')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Customer Support</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.enquiries.index') }}">Customer Enquiries</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Enquiry Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Enquiry Details</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.enquiries.index') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteEnquiry({{ $enquiry->id }})">
                                <i class="bx bx-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Contact Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $enquiry->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>
                                        <a href="tel:{{ $enquiry->phone }}">{{ $enquiry->phone }}</a>
                                    </td>
                                </tr>
                                @if($enquiry->service)
                                <tr>
                                    <td><strong>Service:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $enquiry->service }}</span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Enquiry Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Submitted:</strong></td>
                                    <td>{{ $enquiry->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Enquiry ID:</strong></td>
                                    <td>#{{ $enquiry->id }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Message</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-0">{{ $enquiry->message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="mailto:{{ $enquiry->email }}?subject=Re: Your Enquiry #{{ $enquiry->id }}" class="btn btn-primary">
                                    <i class="bx bx-envelope me-1"></i>Reply via Email
                                </a>
                                <a href="tel:{{ $enquiry->phone }}" class="btn btn-success">
                                    <i class="bx bx-phone me-1"></i>Call Customer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Delete enquiry function
        function deleteEnquiry(enquiryId) {
            if (confirm('Are you sure you want to delete this enquiry?')) {
                $.ajax({
                    url: "{{ route('admin.enquiries.destroy', ':id') }}".replace(':id', enquiryId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('admin.enquiries.index') }}";
                            }, 1500);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        toastr.error(response.message || 'An error occurred while deleting the enquiry.');
                    }
                });
            }
        }
    </script>
@endsection

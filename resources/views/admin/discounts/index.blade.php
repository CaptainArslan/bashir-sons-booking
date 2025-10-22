@extends('admin.layouts.app')

@section('title', 'Discount Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Discount Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Discount Management</li>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">All Discounts</h4>
                            @can('create discounts')
                                <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i>
                                    Create Discount
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="discounts-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Route</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Platforms</th>
                                        <th>Status</th>
                                        <th>Validity Period</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
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
    // Initialize DataTable
    $('#discounts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.discounts.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'route_name', name: 'route_name' },
            { data: 'discount_type_badge', name: 'discount_type_badge', orderable: false },
            { data: 'formatted_value', name: 'formatted_value' },
            { data: 'platforms', name: 'platforms', orderable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'validity_period', name: 'validity_period' },
            { data: 'creator_name', name: 'creator_name' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });

    // Toggle status function
    window.toggleStatus = function(discountId, isActive) {
        if (confirm('Are you sure you want to ' + (isActive ? 'activate' : 'deactivate') + ' this discount?')) {
            $.ajax({
                url: "{{ route('admin.discounts.index') }}/" + discountId + "/toggle-status",
                method: 'PATCH',
                data: {
                    is_active: isActive,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#discounts-table').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error('An error occurred while updating the discount.');
                    }
                }
            });
        }
    };

    // Delete discount function
    window.deleteDiscount = function(discountId) {
        if (confirm('Are you sure you want to delete this discount? This action cannot be undone.')) {
            $.ajax({
                url: "{{ route('admin.discounts.index') }}/" + discountId,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#discounts-table').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error('An error occurred while deleting the discount.');
                    }
                }
            });
        }
    };
});
</script>
@endpush

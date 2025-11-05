@extends('admin.layouts.app')

@section('title', 'Discount Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Discounts</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Discounts</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-discount me-2"></i>
                                Discount Management
                            </h5>
                            @can('create discounts')
                                <a href="{{ route('admin.discounts.create') }}" class="btn btn-light btn-sm">
                                    <i class="bx bx-plus me-1"></i>Add New
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="discounts-table" class="table table-striped table-bordered">
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

@section('scripts')
@include('admin.layouts.datatables')
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
            { data: 'discount_type_badge', name: 'discount_type', orderable: false },
            { data: 'formatted_value', name: 'value', orderable: false },
            { data: 'platforms', name: 'platforms', orderable: false, searchable: false },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'validity_period', name: 'starts_at', orderable: true },
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
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to ' + (isActive ? 'activate' : 'deactivate') + ' this discount?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, ' + (isActive ? 'activate' : 'deactivate') + ' it!'
        }).then((result) => {
            if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.discounts.index') }}/" + discountId + "/toggle-status",
                method: 'PATCH',
                data: {
                    is_active: isActive,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        $('#discounts-table').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'An error occurred while updating the discount.';
                    Swal.fire('Error!', message, 'error');
                }
            });
            }
        });
    };

    // Delete discount function
    window.deleteDiscount = function(discountId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.discounts.index') }}/" + discountId,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message || 'Discount has been deleted.', 'success');
                        $('#discounts-table').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'An error occurred while deleting the discount.';
                    Swal.fire('Error!', message, 'error');
                }
            });
            }
        });
    };
});
</script>
@endsection

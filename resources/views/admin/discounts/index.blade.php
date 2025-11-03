@extends('admin.layouts.app')

@section('title', 'Discount Management')
@section('styles')

    <style>
        /* Compact Discounts Index Styling */
        .discounts-header {
            background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .discounts-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .discounts-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .add-discount-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-discount-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <!-- Compact Header -->
    <div class="discounts-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-discount me-2"></i>Discount Management</h4>
                <p>Manage discounts and promotional offers</p>
            </div>
            <div>
                @can('create discounts')
                    <a href="{{ route('admin.discounts.create') }}" class="add-discount-btn">
                        <i class="bx bx-plus me-1"></i>Add New Discount
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Compact Table -->
    <div class="table-container">
        <div class="p-3">
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
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
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
@endsection

@extends('admin.layouts.app')

@section('title', 'Fares Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-money me-2"></i>Fares Management
            </h1>
            <p class="text-muted mb-0">Manage transportation fares between terminals</p>
        </div>
        @can('create fares')
            <a href="{{ route('admin.fares.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Add New Fare
            </a>
        @endcan
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Fares
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-fares">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-money fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Fares
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-fares">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Fare
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="average-fare">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-trending-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-revenue">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bx bx-list-ul me-2"></i>All Fares
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshTable()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="fares-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Route Path</th>
                            <th>Fare Information</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this fare? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Fare</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#fares-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.fares.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'route_path', name: 'route_path', orderable: false, searchable: false },
            { data: 'fare_info', name: 'fare_info', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: true, searchable: false },
            { data: 'created_at', name: 'created_at', orderable: true, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No fares found",
            zeroRecords: "No matching fares found"
        }
    });

    // Load statistics
    loadStatistics();

    // Refresh table function
    window.refreshTable = function() {
        table.ajax.reload();
        loadStatistics();
    };

    // Load statistics function
    function loadStatistics() {
        $.ajax({
            url: "{{ route('admin.fares.data') }}",
            type: 'GET',
            data: { statistics: true },
            success: function(response) {
                $('#total-fares').text(response.total_fares || 0);
                $('#active-fares').text(response.active_fares || 0);
                $('#average-fare').text(response.average_fare || 'PKR 0.00');
                $('#total-revenue').text(response.total_revenue || 'PKR 0.00');
            },
            error: function() {
                $('#total-fares').text('0');
                $('#active-fares').text('0');
                $('#average-fare').text('PKR 0.00');
                $('#total-revenue').text('PKR 0.00');
            }
        });
    }
});

// Delete fare function
function deleteFare(fareId) {
    $('#deleteModal').modal('show');
    
    $('#confirmDelete').off('click').on('click', function() {
        $.ajax({
            url: "{{ route('admin.fares.destroy', ':id') }}".replace(':id', fareId),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    toastr.success(response.message);
                    $('#fares-table').DataTable().ajax.reload();
                    loadStatistics();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response.message || 'An error occurred while deleting the fare.');
            }
        });
    });
}
</script>
@endpush

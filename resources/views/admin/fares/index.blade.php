@extends('admin.layouts.app')

@section('title', 'Fare Management')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        /* Compact Fare Index Styling */
        .fares-header {
            background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .fares-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .fares-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .add-fare-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .add-fare-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .fare-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .fare-final {
            font-weight: 600;
            color: #28a745;
            font-size: 0.95rem;
        }

        .fare-base {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .fare-discount {
            color: #17a2b8;
            font-size: 0.8rem;
        }

        .route-path {
            font-weight: 500;
            color: #495057;
        }

        .route-cities {
            color: #6c757d;
            font-size: 0.85rem;
        }
    </style>
@endsection

@section('content')
    <!-- Compact Header -->
    <div class="fares-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-money me-2"></i>Fare Management</h4>
                <p>Manage transportation fares between terminals</p>
            </div>
            @can('create fares')
                <a href="{{ route('admin.fares.create') }}" class="add-fare-btn">
                    <i class="bx bx-plus me-1"></i>Add New Fare
                </a>
            @endcan
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-dark">
                    <i class="bx bx-list-ul me-2"></i>All Fares
                </h6>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="fares-table" width="100%" cellspacing="0">
                    <thead class="table-light">
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

@section('scripts')
    @include('admin.layouts.datatables')
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
                columns: [{
                        data: 'route_path',
                        name: 'route_path',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<div class="route-path">' + data + '</div>';
                        }
                    },
                    {
                        data: 'fare_info',
                        name: 'fare_info',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<div class="fare-info">' + data + '</div>';
                        }
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: "No fares found",
                    zeroRecords: "No matching fares found"
                }
            });

            // Refresh table function
            window.refreshTable = function() {
                table.ajax.reload();
            };
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
@endsection

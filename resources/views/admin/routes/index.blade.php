@extends('admin.layouts.app')

@section('title', 'Routes')
@section('styles')
    <style>
        /* Compact Routes Index Styling */
        .routes-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .routes-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .routes-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .add-route-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-route-btn:hover {
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
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #ced4da;
        }
        
        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            margin: 0 2px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            border: none;
            color: white;
        }
    </style>
@endsection

@section('content')
    <!-- Compact Header -->
    <div class="routes-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-map me-2"></i>Routes Management</h4>
                <p>Manage bus routes, stops, and fare information</p>
            </div>
            <div>
                @can('create routes')
                    <a href="{{ route('admin.routes.create') }}" class="add-route-btn">
                        <i class="bx bx-plus me-1"></i>Add New Route
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Compact Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="routes-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Route</th>
                            <th>Direction</th>
                            <th>Return Route</th>
                            <th>Stops</th>
                            {{-- <th>Total Fare</th> --}}
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- @include('admin.layouts.datatables') --}}
    <script>
        $(document).ready(function() {
            $('#routes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.routes.data') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'formatted_name',
                        name: 'name',
                    },
                    {
                        data: 'direction_badge',
                        name: 'direction',
                    },
                    {
                        data: 'return_route',
                        name: 'return_route',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'stops_count',
                        name: 'stops_count',
                        orderable: false,
                        searchable: false,
                    },
                    // {
                    //     data: 'total_fare',
                    //     name: 'total_fare',
                    //     orderable: false,
                    //     searchable: false,
                    // },
                    {
                        data: 'status_badge',
                        name: 'status',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                    }
                ],
            });
        });

        // Delete route function
        function deleteRoute(routeId) {
            if (confirm('Are you sure you want to delete this route?')) {
                $.ajax({
                    url: "{{ route('admin.routes.destroy', ':id') }}".replace(':id', routeId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#routes-table').DataTable().ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        toastr.error(response.message || 'An error occurred while deleting the route.');
                    }
                });
            }
        }
    </script>
@endsection

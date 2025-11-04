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
                            <th>Stops</th>
                            <th>Total Fare</th>
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
                        data: 'stops_count',
                        name: 'stops_count',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'total_fare',
                        name: 'total_fare',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    }
                    @if(auth()->user()->can('edit routes') || auth()->user()->can('delete routes') || auth()->user()->can('view routes'))
                    ,{
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                    }
                    @endif
                ],
            });
        });

        // Delete route function with SweetAlert
        function deleteRoute(routeId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ url('admin/routes') }}/" + routeId,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message || 'Route has been deleted.',
                                    'success'
                                ).then(() => {
                                    $('#routes-table').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Failed to delete route.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while deleting the route.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'Route not found.';
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to delete routes.';
                            }
                            Swal.fire(
                                'Error!',
                                errorMessage,
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection

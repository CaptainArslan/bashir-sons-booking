@extends('admin.layouts.app')

@section('title', 'Buses')
@section('styles')
    <style>
        /* Compact Buses Index Styling */
        .buses-header {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .buses-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .buses-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .add-bus-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-bus-btn:hover {
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
    <div class="buses-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-bus me-2"></i>Bus Management</h4>
                <p>Manage bus fleet and vehicle information</p>
            </div>
            <div>
                @can('create buses')
                    <a href="{{ route('admin.buses.create') }}" class="add-bus-btn">
                        <i class="bx bx-plus me-1"></i>Add New Bus
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Compact Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="buses-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bus Details</th>
                            <th>Description</th>
                            <th>Bus Info</th>
                            <th>Type</th>
                            <th>Layout</th>
                            <th>Facilities</th>
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
            $('#buses-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.buses.data') }}",
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
                        data: 'description_preview',
                        name: 'description',
                        width: '100px',
                    },
                    {
                        data: 'bus_info',
                        name: 'bus_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'type_info',
                        name: 'type_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'layout_info',
                        name: 'layout_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'facilities_list',
                        name: 'facilities_list',
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

        // Delete bus function with SweetAlert
        function deleteBus(busId) {
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
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('admin.buses.destroy', ':id') }}".replace(':id', busId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message || 'Bus has been deleted.',
                                    'success'
                                ).then(() => {
                                    $('#buses-table').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Failed to delete bus.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            let errorMessage = 'An error occurred while deleting the bus.';

                            if (response && response.message) {
                                errorMessage = response.message;
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to delete buses.';
                            } else if (xhr.status === 404) {
                                errorMessage = 'Bus not found.';
                            } else if (xhr.status === 400) {
                                // Handle association errors
                                errorMessage = response.message || 'Cannot delete bus due to associated data.';
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

@extends('admin.layouts.app')

@section('title', 'Bus Types')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Bus Types</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @can('create bus types')
                <a href="{{ route('admin.bus-types.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add New Bus Type
                </a>
            @endcan
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="bus-types-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bus Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Buses Count</th>
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
            $('#bus-types-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.bus-types.data') }}",
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
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                    },
                    {
                        data: 'buses_count',
                        name: 'buses_count',
                        orderable: false,
                        searchable: false,
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

        // Delete bus type function with SweetAlert
        function deleteBusType(busTypeId) {
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
                        url: "{{ route('admin.bus-types.destroy', ':id') }}".replace(':id', busTypeId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message || 'Bus type has been deleted.',
                                    'success'
                                ).then(() => {
                                    $('#bus-types-table').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Failed to delete bus type.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            let errorMessage = 'An error occurred while deleting the bus type.';
                            
                            if (response && response.message) {
                                errorMessage = response.message;
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to delete bus types.';
                            } else if (xhr.status === 404) {
                                errorMessage = 'Bus type not found.';
                            } else if (xhr.status === 400) {
                                // Handle association errors
                                errorMessage = response.message || 'Cannot delete bus type due to associated data.';
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

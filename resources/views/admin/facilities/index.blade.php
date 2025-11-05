@extends('admin.layouts.app')

@section('title', 'Facilities')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Facilities</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @can('create facilities')
                <a href="{{ route('admin.facilities.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add New Facility
                </a>
            @endcan
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="facilities-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Facility</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Buses Count</th>
                            <th>Created Date</th>
                            @if(auth()->user()->can('edit facilities') || auth()->user()->can('delete facilities'))
                                <th>Actions</th>
                            @endif
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
            $('#facilities-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.facilities.data') }}",
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
                    }
                    @if(auth()->user()->can('edit facilities') || auth()->user()->can('delete facilities'))
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

        // Delete facility function with SweetAlert
        function deleteFacility(facilityId) {
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
                        url: "{{ url('admin/facilities') }}/" + facilityId,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message || 'Facility has been deleted.',
                                    'success'
                                ).then(() => {
                                    $('#facilities-table').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Failed to delete facility.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while deleting the facility.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'Facility not found.';
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to delete facilities.';
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

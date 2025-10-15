@extends('admin.layouts.app')

@section('title', 'Buses')
@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Buses</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
                <a href="{{ route('admin.buses.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Bus
            </a>
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
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
    @include('admin.layouts.datatables')
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

        // Delete bus function
        function deleteBus(busId) {
            if (confirm('Are you sure you want to delete this bus?')) {
                $.ajax({
                    url: "{{ route('admin.buses.destroy', ':id') }}".replace(':id', busId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#buses-table').DataTable().ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        toastr.error(response.message || 'An error occurred while deleting the bus.');
                    }
                });
            }
        }
    </script>
@endsection

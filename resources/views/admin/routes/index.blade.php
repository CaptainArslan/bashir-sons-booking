@extends('admin.layouts.app')

@section('title', 'Routes')
@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Routes</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @can('create routes')
                <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add New Route
                </a>
            @endcan
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="routes-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Route</th>
                            <th>Direction</th>
                            <th>Return Route</th>
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
    @include('admin.layouts.datatables')
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

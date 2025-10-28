@extends('admin.layouts.app')

@section('title', 'Route Stops')
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
                    <li class="breadcrumb-item active" aria-current="page">Route Stops</li>
                </ol>
            </nav>
        </div>
        {{-- <div class="ms-auto">
            @can('create route stops')
                <a href="{{ route('admin.route-stops.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add New Route Stop
                </a>
            @endcan
        </div> --}}
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="route-stops-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Route</th>
                            <th>Terminal</th>
                            <th>Sequence</th>
                            <th>Created Date</th>
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
            $('#route-stops-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.route-stops.data') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'route_info',
                        name: 'route_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'terminal_info',
                        name: 'terminal_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'sequence_badge',
                        name: 'sequence',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    },
                    // {
                    //     data: 'actions',
                    //     name: 'actions',
                    //     orderable: false,
                    //     searchable: false,
                    // }
                ],
            });
        });
    </script>
@endsection

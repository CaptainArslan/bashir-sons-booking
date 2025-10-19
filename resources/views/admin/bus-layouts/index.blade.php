@extends('admin.layouts.app')

@section('title', 'Bus Layouts')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Bus Layouts</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
                <a href="{{ route('admin.bus-layouts.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Bus Layout
            </a>
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="bus-layouts-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Layout Name</th>
                            <th>Description</th>
                            <th>Seats Info</th>
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
    @include('admin.layouts.datatables')
    <script>
        $(document).ready(function() {
            $('#bus-layouts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.bus-layouts.data') }}",
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
                        data: 'seats_info',
                        name: 'seats_info',
                        orderable: false,
                        searchable: false,
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

        // Delete bus layout function
        function deleteBusLayout(busLayoutId) {
            if (confirm('Are you sure you want to delete this bus layout?')) {
                $.ajax({
                    url: "{{ route('admin.bus-layouts.destroy', ':id') }}".replace(':id', busLayoutId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#bus-layouts-table').DataTable().ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        toastr.error(response.message || 'An error occurred while deleting the bus layout.');
                    }
                });
            }
        }
    </script>
@endsection

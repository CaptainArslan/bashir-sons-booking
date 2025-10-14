@extends('admin.layouts.app')

@section('title', 'Counter Terminals')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Terminals Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Counter Terminals</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('admin.counter-terminals.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Add New Terminal
            </a>
        </div>
    </div>
    <!--end breadcrumb-->
    
    <h6 class="mb-0 text-uppercase">Counter Terminals</h6>
    <hr>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="terminals-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Terminal Name</th>
                            <th>City</th>
                            <th>Contact Info</th>
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
            $('#terminals-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.counter-terminals.data') }}",
                columns: [{
                    data: 'id',
                    name: 'id',
                },
                {
                    data: 'formatted_name',
                    name: 'name',
                },
                {
                    data: 'city_name',
                    name: 'city.name',
                },
                {
                    data: 'contact_info',
                    name: 'phone',
                    orderable: false,
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

    // Delete terminal function
    function deleteTerminal(terminalId) {
        if (confirm('Are you sure you want to delete this terminal?')) {
            $.ajax({
                url: "{{ route('admin.counter-terminals.destroy', ':id') }}".replace(':id', terminalId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#terminals-table').DataTable().ajax.reload();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error deleting terminal');
                }
            });
        }
    }
</script>
@endsection
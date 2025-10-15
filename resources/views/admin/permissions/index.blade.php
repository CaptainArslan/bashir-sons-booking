@extends('admin.layouts.app')

@section('title', 'Permissions')
@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Access Control</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @can('create permissions')
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add New Permission
                </a>
            @endcan
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="permissions-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Permission</th>
                            <th>Roles Count</th>
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
            $('#permissions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.permissions.data') }}",
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
                        data: 'roles_count',
                        name: 'roles_count',
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

        // Delete permission function
        function deletePermission(permissionId) {
            if (confirm('Are you sure you want to delete this permission?')) {
                $.ajax({
                    url: "{{ route('admin.permissions.destroy', ':id') }}".replace(':id', permissionId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#permissions-table').DataTable().ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        toastr.error(response.message || 'An error occurred while deleting the permission.');
                    }
                });
            }
        }
    </script>
@endsection

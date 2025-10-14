@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">User Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Add New User
            </a>
        </div>
    </div>
    <!--end breadcrumb-->
    
    <h6 class="mb-0 text-uppercase">Users Management</h6>
    <hr>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="users-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Information</th>
                            <th>Contact Info</th>
                            <th>Personal Info</th>
                            <th>Address Info</th>
                            <th>Roles</th>
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
            // Initialize the users table
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.users.data') }}",
                columns: [{
                    data: 'id',
                    name: 'id',
                },
                {
                    data: 'user_info',
                    name: 'name',
                    orderable: false,
                },
                {
                    data: 'contact_info',
                    name: 'profile.phone',
                    orderable: false,
                },
                {
                    data: 'personal_info',
                    name: 'profile.gender',
                    orderable: false,
                },
                {
                    data: 'address_info',
                    name: 'profile.address',
                    orderable: false,
                },
                {
                    data: 'roles_info',
                    name: 'roles.name',
                    orderable: false,
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
                }],
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            });
        });

        // Delete user function
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                $.ajax({
                    url: "{{ route('admin.users.destroy', ':id') }}".replace(':id', userId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Reload the users table
                            $('#users-table').DataTable().ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error deleting user');
                    }
                });
            }
        }
    </script>
@endsection

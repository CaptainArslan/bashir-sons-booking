@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        /* Compact Users Index Styling */
        .users-header {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .users-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .users-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .add-user-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-user-btn:hover {
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
    <div class="users-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-user me-2"></i>Users Management</h4>
                <p>Manage system users and their access permissions</p>
            </div>
            <div>
                @can('create users')
                    <a href="{{ route('admin.users.create') }}" class="add-user-btn">
                        <i class="bx bx-plus me-1"></i>Add New User
                    </a>
                @endcan
            </div>
        </div>
    </div>
    <!-- Compact Table -->
    <div class="table-container">
        <div class="p-3">
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
                            <th>Terminal</th>
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
                    data: 'terminal_info',
                    name: 'terminal.name',
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
                }],
                // responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            });
        });

        // Delete user function with SweetAlert
        function deleteUser(userId) {
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
                        url: "{{ url('admin/users') }}/" + userId,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message || 'User has been deleted.', 'success')
                                    .then(() => {
                                        $('#users-table').DataTable().ajax.reload();
                                    });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to delete user.', 'error');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while deleting the user.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }

        // Ban user function with SweetAlert
        function banUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This user will be banned and logged out immediately!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, ban user!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Banning...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('admin/users') }}/" + userId + "/ban",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Banned!', response.message || 'User has been banned.', 'success')
                                    .then(() => {
                                        $('#users-table').DataTable().ajax.reload();
                                    });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to ban user.', 'error');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while banning the user.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to ban users.';
                            }
                            Swal.fire('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }

        // Activate user function with SweetAlert
        function activateUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This user will be activated and can login again!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, activate user!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Activating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('admin/users') }}/" + userId + "/activate",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Activated!', response.message || 'User has been activated.', 'success')
                                    .then(() => {
                                        $('#users-table').DataTable().ajax.reload();
                                    });
                            } else {
                                Swal.fire('Error!', response.message || 'Failed to activate user.', 'error');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while activating the user.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to activate users.';
                            }
                            Swal.fire('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endsection

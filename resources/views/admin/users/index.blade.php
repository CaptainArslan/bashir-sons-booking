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
                <a href="{{ route('admin.users.create') }}" class="add-user-btn">
                    <i class="bx bx-plus me-1"></i>Add New User
                </a>
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
                    data: 'terminal_info',
                    name: 'terminal.name',
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
                // responsive: true,
                autoWidth: false,
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

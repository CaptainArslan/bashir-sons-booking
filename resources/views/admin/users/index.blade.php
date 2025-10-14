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
    
    <!-- Filter Tabs -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs" id="userTypeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        <i class="bx bx-group me-1"></i>All Users
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" role="tab">
                        <i class="bx bx-user me-1"></i>Customers
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button" role="tab">
                        <i class="bx bx-briefcase me-1"></i>Employees
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab">
                        <i class="bx bx-shield me-1"></i>Admins
                    </button>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="tab-content" id="userTypeTabsContent">
        <!-- All Users Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="users-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact Info</th>
                                    <th>Profile Info</th>
                                    <th>User Type</th>
                                    <th>Roles</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customers Tab -->
        <div class="tab-pane fade" id="customers" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="customers-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact Info</th>
                                    <th>Profile Info</th>
                                    <th>User Type</th>
                                    <th>Roles</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employees Tab -->
        <div class="tab-pane fade" id="employees" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="employees-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact Info</th>
                                    <th>Profile Info</th>
                                    <th>User Type</th>
                                    <th>Roles</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admins Tab -->
        <div class="tab-pane fade" id="admins" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="admins-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact Info</th>
                                    <th>Profile Info</th>
                                    <th>User Type</th>
                                    <th>Roles</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.layouts.datatables')
    <script>
        $(document).ready(function() {
            // Initialize only the first table (All Users)
            initializeDataTable('users-table', 'all');

            // Handle tab switching
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const target = $(e.target).attr("data-bs-target");
                const tableId = target.replace('#', '') + '-table';
                
                // Initialize table if not already initialized
                if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                    const userType = target.replace('#', '');
                    initializeDataTable(tableId, userType);
                } else {
                    // Reload the table when tab is shown
                    $('#' + tableId).DataTable().ajax.reload();
                }
            });
        });

        function initializeDataTable(tableId, userType) {
            const url = userType === 'all' 
                ? "{{ route('admin.users.data') }}"
                : "{{ route('admin.users.data') }}?type=" + userType;

            $('#' + tableId).DataTable({
                processing: true,
                serverSide: true,
                ajax: url,
                columns: [{
                    data: 'id',
                    name: 'id',
                },
                {
                    data: 'formatted_name',
                    name: 'name',
                },
                {
                    data: 'email',
                    name: 'email',
                },
                {
                    data: 'contact_info',
                    name: 'userProfile.phone',
                    orderable: false,
                },
                {
                    data: 'profile_info',
                    name: 'userProfile.gender',
                    orderable: false,
                },
                {
                    data: 'user_type',
                    name: 'roles.name',
                    orderable: false,
                },
                {
                    data: 'roles_list',
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
        }

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
                            // Reload all initialized tables
                            const tableIds = ['users-table', 'customers-table', 'employees-table', 'admins-table'];
                            tableIds.forEach(function(tableId) {
                                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                                    $('#' + tableId).DataTable().ajax.reload();
                                }
                            });
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

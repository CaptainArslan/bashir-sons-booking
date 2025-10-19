@extends('admin.layouts.app')

@section('title', 'Roles')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        #roles-table {
            table-layout: auto;
            width: 100% !important;
        }
        
        #roles-table td {
            white-space: normal !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 200px;
        }
        
        #roles-table th {
            white-space: normal !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        #roles-table td:nth-child(1) {
            max-width: 80px;
            text-align: center;
        }
        
        #roles-table td:nth-child(2) {
            max-width: 200px;
            font-weight: 600;
        }
        
        #roles-table td:nth-child(3) {
            max-width: 120px;
            text-align: center;
        }
        
        #roles-table td:nth-child(4) {
            max-width: 300px;
        }
        
        #roles-table td:nth-child(5) {
            max-width: 150px;
        }
        
        #roles-table td:nth-child(6) {
            max-width: 150px;
            text-align: center;
        }
        
        .permission-badge {
            font-size: 0.75rem;
            margin: 2px;
            display: inline-block;
        }
        
        .actions-column {
            white-space: nowrap;
        }
        
        .actions-column .btn {
            margin: 1px;
        }
    </style>
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Roles Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Role
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="roles-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Role Name</th>
                            <th>Permissions Count</th>
                            <th>Permissions</th>
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
            $('#roles-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.roles.data') }}",
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        width: '8%'
                    },
                    {
                        data: 'formatted_name',
                        name: 'name',
                        width: '20%'
                    },
                    {
                        data: 'permissions_count',
                        name: 'permissions_count',
                        searchable: false,
                        orderable: true,
                        width: '12%'
                    },
                    {
                        data: 'permissions_list',
                        name: 'permissions_list',
                        searchable: false,
                        orderable: false,
                        width: '35%'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '15%'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        width: '10%',
                        className: 'actions-column'
                    }
                ],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: "No roles found",
                    zeroRecords: "No matching roles found"
                }
            });
        });

        // Delete role function
        function deleteRole(roleId) {
            if (confirm('Are you sure you want to delete this role?')) {
                $.ajax({
                    url: "{{ route('admin.roles.destroy', ':id') }}".replace(':id', roleId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#roles-table').DataTable().ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred while deleting the role.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr.error(message);
                    }
                });
            }
        }
    </script>

@endsection

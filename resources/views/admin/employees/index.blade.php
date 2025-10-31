@extends('admin.layouts.app')

@section('title', 'Employees Management')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        /* Employee Index Styling */
        .employees-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
        }

        .employees-header h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .employees-header p {
            margin: 0.15rem 0 0 0;
            opacity: 0.9;
            font-size: 0.8rem;
        }

        .add-employee-btn {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 20px;
            padding: 0.4rem 0.85rem;
            color: white;
            font-weight: 500;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }

        .add-employee-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 123, 255, 0.3);
            color: white;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .stats-card h6 {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .stats-card h4 {
            margin: 0.2rem 0 0 0;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .row.mb-3 {
            margin-bottom: 0.75rem !important;
        }

        .table-container .p-3 {
            padding: 0.75rem !important;
        }

        #employees-table {
            font-size: 0.85rem;
        }

        #employees-table th {
            padding: 0.5rem;
            font-size: 0.8rem;
        }

        #employees-table td {
            padding: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <!-- Header -->
    <div class="employees-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-user-check me-2"></i>Employees Management</h4>
                <p>Manage employees and their terminal assignments</p>
            </div>
            <div>
                <a href="{{ route('admin.employees.create') }}" class="add-employee-btn">
                    <i class="bx bx-plus me-1"></i>Add New Employee
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="stats-card">
                <h6><i class="bx bx-user me-1"></i>Total Employees</h6>
                <h4 id="totalEmployees">-</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h6><i class="bx bx-check-circle me-1"></i>Active Employees</h6>
                <h4 id="activeEmployees">-</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h6><i class="bx bx-x-circle me-1"></i>No Terminal</h6>
                <h4 id="inactiveEmployees">-</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h6><i class="bx bx-calendar me-1"></i>This Month</h6>
                <h4 id="newEmployees">-</h4>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="employees-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee Information</th>
                            <th>Contact Info</th>
                            <th>Personal Info</th>
                            <th>Terminal Assignment</th>
                            <th>Routes</th>
                            <th>Address Info</th>
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
        var table = null;
        $(document).ready(function() {
            // Initialize the employees table
            table = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.employees.data') }}",
                columns: [{
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'employee_info',
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
                        data: 'terminal_info',
                        name: 'terminal.name',
                        orderable: false,
                    },
                    {
                        data: 'routes_info',
                        name: 'routes',
                        orderable: false,
                    },
                    {
                        data: 'address_info',
                        name: 'profile.address',
                        orderable: false,
                    },
                    {
                        data: 'status_info',
                        name: 'terminal_id',
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
                    }
                ],
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                drawCallback: function(settings) {
                    updateStatistics();
                }
            });

            // Update statistics
            function updateStatistics() {
                $.ajax({
                    url: "{{ route('admin.employees.stats') }}",
                    type: 'GET',
                    success: function(response) {
                        $('#totalEmployees').text(response.total);
                        $('#activeEmployees').text(response.active);
                        $('#inactiveEmployees').text(response.inactive);
                        $('#newEmployees').text(response.new_this_month);
                    },
                    error: function() {
                        console.log('Error loading statistics');
                    }
                });
            }

            // Initial statistics load
            updateStatistics();

        });
        
        // Delete employee function with SweetAlert
        function deleteEmployee(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.employees.destroy', ':id') }}".replace(':id',
                            userId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message || 'Employee has been deleted.',
                                    'success'
                                );
                                // Reload the employees table
                                table.ajax.reload();
                                // Update statistics
                                updateStatistics();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response?.message || 'Failed to delete employee.',
                                    'error'
                                );
                            }
                        },
                        error: function(error) {
                            Swal.fire(
                                'Error!',
                                error?.responseJSON?.message ||
                                'An error occurred while deleting the employee.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection

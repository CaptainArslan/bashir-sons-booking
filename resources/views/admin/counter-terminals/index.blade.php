@extends('admin.layouts.app')

@section('title', 'Counter Terminals')

@section('styles')
    <style>
        /* Compact Counter Terminals Index Styling */
        .terminals-header {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .terminals-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .terminals-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .add-terminal-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .add-terminal-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #ced4da;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            border: none;
            color: white;
        }

        .terminal-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .terminal-name {
            font-weight: 600;
            color: #495057;
            font-size: 0.95rem;
        }

        .terminal-location {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .contact-phone {
            font-weight: 500;
            color: #495057;
            font-size: 0.9rem;
        }

        .contact-email {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.active {
            background-color: #28a745;
        }

        .status-dot.inactive {
            background-color: #6c757d;
        }
    </style>
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
    </div>
    <!--end breadcrumb-->

    <!-- Compact Header -->
    <div class="terminals-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-building me-2"></i>Counter Terminals Management</h4>
                <p>Manage transportation terminals and their information</p>
            </div>
            @can('create terminals')
                <a href="{{ route('admin.counter-terminals.create') }}" class="add-terminal-btn">
                    <i class="bx bx-plus me-1"></i>Add New Terminal
                </a>
            @endcan
        </div>
    </div>


    <!-- Data Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-dark">
                    <i class="bx bx-list-ul me-2"></i>All Terminals
                </h6>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="terminals-table" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Terminal Information</th>
                            <th>Location</th>
                            <th>Contact Details</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this terminal? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Warning:</strong> Deleting this terminal will also remove all associated fares and
                        schedules.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete Terminal</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- @include('admin.layouts.datatables') --}}
    <script>
        var table;
        
        $(document).ready(function() {
            table = $('#terminals-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.counter-terminals.data') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'formatted_name',
                        name: 'name',
                        render: function(data, type, row) {
                            return '<div class="terminal-info">' +
                                '<div class="terminal-name">' + data + '</div>' +
                                '<div class="terminal-location">ID: ' + row.id + '</div>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'city_name',
                        name: 'city.name',
                        render: function(data, type, row) {
                            return '<div class="terminal-info">' +
                                '<div class="terminal-name">' + data + '</div>' +
                                '<div class="terminal-location">' + (row.address || 'No address') +
                                '</div>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'contact_info',
                        name: 'phone',
                        orderable: false,
                        render: function(data, type, row) {
                            return '<div class="contact-info">' +
                                '<div class="contact-phone">' + (row.phone || 'No phone') +
                                '</div>' +
                                '<div class="contact-email">' + (row.email || 'No email') +
                                '</div>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        render: function(data, type, row) {
                            const statusClass = row.status === 'active' ? 'active' : 'inactive';
                            return '<div class="status-indicator">' +
                                '<span class="status-dot ' + statusClass + '"></span>' +
                                '<span>' + data + '</span>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row) {
                            return '<div class="terminal-info">' +
                                '<div class="terminal-name">' + data + '</div>' +
                                '<div class="terminal-location">' + (row.updated_at || '') +
                                '</div>' +
                                '</div>';
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                    }
                ],
                order: [
                    [4, 'desc']
                ],
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: "No terminals found",
                    zeroRecords: "No matching terminals found"
                }
            });

            window.refreshTable = function() {
                table.ajax.reload();
            };

        });

        function deleteTerminal(terminalId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.counter-terminals.destroy', ':id') }}".replace(':id',
                            terminalId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'Terminal has been deleted.',
                                    'success'
                                );
                                table.ajax.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            Swal.fire(
                                'Error!',
                                response.message ||
                                'An error occurred while deleting the terminal.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection

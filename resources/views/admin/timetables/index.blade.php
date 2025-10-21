@extends('admin.layouts.app')

@section('title', 'Timetables')
@section('styles')
    <style>
        /* Compact Timetables Index Styling */
        .timetables-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .timetables-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .timetables-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .add-timetable-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-timetable-btn:hover {
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
    </style>
@endsection

@section('content')
    <!-- Compact Header -->
    <div class="timetables-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-time me-2"></i>Timetables Management</h4>
                <p>Manage bus timetables and schedules for all routes</p>
            </div>
            <div>
                @can('create timetables')
                    <a href="{{ route('admin.timetables.create') }}" class="add-timetable-btn">
                        <i class="bx bx-plus me-1"></i>Generate Timetables
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Compact Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="timetables-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Route</th>
                            <th>Start Terminal</th>
                            <th>End Terminal</th>
                            <th>Start Time</th>
                            <th>Total Stops</th>
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
            $('#timetables-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.timetables.data') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'route_name',
                        name: 'route_name',
                    },
                    {
                        data: 'start_terminal',
                        name: 'start_terminal',
                    },
                    {
                        data: 'end_terminal',
                        name: 'end_terminal',
                    },
                    {
                        data: 'start_departure_time',
                        name: 'start_departure_time',
                    },
                    {
                        data: 'total_stops',
                        name: 'total_stops',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'status',
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

        // Delete timetable function
        function deleteTimetable(timetableId) {
            if (confirm('Are you sure you want to delete this timetable?')) {
                $.ajax({
                    url: "{{ route('admin.timetables.destroy', ':id') }}".replace(':id', timetableId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#timetables-table').DataTable().ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        toastr.error(response.message || 'An error occurred while deleting the timetable.');
                    }
                });
            }
        }
    </script>
@endsection

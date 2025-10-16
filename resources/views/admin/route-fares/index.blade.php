@extends('admin.layouts.app')

@section('title', 'Route Fares Management')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        /* Compact Route Fares Index Styling */
        .fares-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .fares-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .fares-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .refresh-btn {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .refresh-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(108, 117, 125, 0.3);
            color: white;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .fare-info-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 6px;
            padding: 0.5rem;
            border-left: 3px solid #28a745;
        }
        
        .fare-amount {
            font-weight: 600;
            color: #28a745;
        }
        
        .fare-discount {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <!-- Compact Header -->
    <div class="fares-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-money me-2"></i>Route Fares Management</h4>
                <p>View and manage all route fare information</p>
            </div>
            <div>
                <button type="button" class="refresh-btn" onclick="location.reload()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Compact Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="route-fares-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Route</th>
                            <th>Route Path</th>
                            <th>Fare Information</th>
                            <th>Status</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('admin/assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#route-fares-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.route-fares.data') }}",
                    type: 'GET'
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'route_info',
                        name: 'route.name',
                    },
                    {
                        data: 'route_path',
                        name: 'route_path',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'fare_info',
                        name: 'fare_info',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    },

                ],
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: 'No route fares found.',
                    zeroRecords: 'No matching route fares found.',
                }
            });
        });
    </script>
@endsection

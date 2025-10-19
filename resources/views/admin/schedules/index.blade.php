@extends('admin.layouts.app')

@section('title', 'Schedules')

@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        #schedules-table {
            table-layout: auto;
            width: 100% !important;
        }
        
        #schedules-table td {
            white-space: normal !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 200px;
        }
        
        #schedules-table th {
            white-space: normal !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        #schedules-table td:nth-child(1) {
            max-width: 80px;
            text-align: center;
        }
        
        #schedules-table td:nth-child(2) {
            max-width: 150px;
            font-weight: 600;
        }
        
        #schedules-table td:nth-child(3) {
            max-width: 200px;
        }
        
        #schedules-table td:nth-child(4) {
            max-width: 120px;
            text-align: center;
        }
        
        #schedules-table td:nth-child(5) {
            max-width: 120px;
            text-align: center;
        }
        
        #schedules-table td:nth-child(6) {
            max-width: 120px;
            text-align: center;
        }
        
        #schedules-table td:nth-child(7) {
            max-width: 200px;
        }
        
        #schedules-table td:nth-child(8) {
            max-width: 100px;
            text-align: center;
        }
        
        #schedules-table td:nth-child(9) {
            max-width: 120px;
            text-align: center;
        }
        
        #schedules-table td:nth-child(10) {
            max-width: 150px;
            text-align: center;
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
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Schedules</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add New Schedule
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="schedules-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Trip Code</th>
                            <th>Route</th>
                            <th>Departure Time</th>
                            <th>Arrival Time</th>
                            <th>Frequency</th>
                            <th>Operating Days</th>
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
            $('#schedules-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.schedules.getData') }}",
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
                        data: 'formatted_code',
                        name: 'code',
                        width: '15%'
                    },
                    {
                        data: 'route_info',
                        name: 'route_info',
                        searchable: false,
                        orderable: false,
                        width: '20%'
                    },
                    {
                        data: 'formatted_frequency',
                        name: 'frequency',
                        searchable: false,
                        orderable: true,
                        width: '12%'
                    },
                    {
                        data: 'operating_days_list',
                        name: 'operating_days_list',
                        searchable: false,
                        orderable: false,
                        width: '20%'
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active',
                        searchable: false,
                        orderable: true,
                        width: '10%'
                    },
                    {
                        data: 'formatted_created_at',
                        name: 'created_at',
                        width: '12%'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        width: '15%',
                        className: 'actions-column'
                    }
                ],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    emptyTable: "No schedules found",
                    zeroRecords: "No matching schedules found"
                }
            });
        });
    </script>
@endsection

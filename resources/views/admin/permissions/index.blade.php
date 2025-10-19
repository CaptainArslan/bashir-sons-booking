@extends('admin.layouts.app')

@section('title', 'Permissions')
@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        #permissions-table {
            table-layout: auto;
            width: 100% !important;
        }
        
        #permissions-table td {
            white-space: normal !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 200px;
        }
        
        #permissions-table th {
            white-space: normal !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        #permissions-table td:nth-child(2) {
            max-width: 300px;
        }
        
        #permissions-table td:nth-child(3) {
            max-width: 150px;
            text-align: center;
        }
        
        #permissions-table td:nth-child(4) {
            max-width: 180px;
        }
    </style>
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Access Control</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="permissions-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Permission</th>
                            <th>Roles Count</th>
                            <th>Created Date</th>
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
            $('#permissions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.permissions.data') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                        width: '10%'
                    },
                    {
                        data: 'formatted_name',
                        name: 'name',
                        width: '50%'
                    },
                    {
                        data: 'roles_count',
                        name: 'roles_count',
                        orderable: false,
                        searchable: false,
                        width: '15%'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '25%'
                    }
                ],
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                }
            });
        });
    </script>
@endsection

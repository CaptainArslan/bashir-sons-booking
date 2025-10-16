@extends('admin.layouts.app')

@section('title', 'Cities')
@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        /* Compact Cities Index Styling */
        .cities-header {
            background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%);
            color: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .cities-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .cities-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .add-city-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-city-btn:hover {
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
    <div class="cities-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="bx bx-building me-2"></i>Cities Management</h4>
                <p>Manage cities and their information</p>
            </div>
            <div>
                <a href="{{ route('admin.cities.create') }}" class="add-city-btn">
                    <i class="bx bx-plus me-1"></i>Add New City
                </a>
            </div>
        </div>
    </div>

    <!-- Compact Table -->
    <div class="table-container">
        <div class="p-3">
            <div class="table-responsive">
                <table id="cities-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>City Name</th>
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
    @include('admin.layouts.datatables')
    <script>
        $(document).ready(function() {
            $('#cities-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.cities.data') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'formatted_name',
                        name: 'name',
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
                    }
                ],
            });
        });

        // Delete city function
        function deleteCity(cityId) {
            if (confirm('Are you sure you want to delete this city?')) {
                // You can implement the delete functionality here
                // For now, just show an alert
                alert('Delete functionality for city ID: ' + cityId + ' would be implemented here');
            }
        }
    </script>
@endsection

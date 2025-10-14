@extends('admin.layouts.app')

@section('title', 'Cities')
@section('styles')
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Cities Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Cities</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->
    <h6 class="mb-0 text-uppercase">Cities</h6>
    <hr>
    <div class="card">
        <div class="card-body">
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

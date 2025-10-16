@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1">Welcome to Transport Management System</h4>
                            <p class="mb-0 opacity-75">Manage your bus routes, terminals, and operations efficiently</p>
                        </div>
                        <div class="text-end">
                            <i class="bx bx-bus fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total Routes</p>
                            <h4 class="my-1 text-info">{{ $stats['total_routes'] }}</h4>
                            <p class="mb-0 font-13">{{ $stats['active_routes'] }} active routes</p>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                            <i class='bx bx-map'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total Buses</p>
                            <h4 class="my-1 text-success">{{ $stats['total_buses'] }}</h4>
                            <p class="mb-0 font-13">{{ $stats['active_buses'] }} active buses</p>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                            <i class='bx bx-bus'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total Terminals</p>
                            <h4 class="my-1 text-warning">{{ $stats['total_terminals'] }}</h4>
                            <p class="mb-0 font-13">{{ $stats['active_terminals'] }} active terminals</p>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                            <i class='bx bx-building'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total Enquiries</p>
                            <h4 class="my-1 text-danger">{{ $stats['total_enquiries'] }}</h4>
                            <p class="mb-0 font-13">{{ $stats['pending_enquiries'] }} pending</p>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                            <i class='bx bx-message-dots'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--end row-->

    <div class="row">
        <div class="col-12 col-lg-8 d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">Routes Overview</h6>
                        </div>
                        <div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i
                                    class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.routes.index') }}">View All Routes</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.routes.create') }}">Add New Route</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
                        <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                style="color: #14abef"></i>Active Routes</span>
                        <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                style="color: #ffc107"></i>Inactive Routes</span>
                    </div>
                    <div class="chart-container-1">
                        <canvas id="routesChart"></canvas>
                    </div>
                </div>
                <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
                    <div class="col">
                        <div class="p-3">
                            <h5 class="mb-0">{{ $stats['total_routes'] }}</h5>
                            <small class="mb-0">Total Routes <span> <i class="bx bx-up-arrow-alt align-middle"></i>
                                    {{ $stats['active_routes'] }} active</span></small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3">
                            <h5 class="mb-0">{{ $stats['total_stops'] }}</h5>
                            <small class="mb-0">Total Stops <span> <i class="bx bx-up-arrow-alt align-middle"></i>
                                    Across all routes</span></small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3">
                            <h5 class="mb-0">{{ $stats['total_fares'] }}</h5>
                            <small class="mb-0">Total Fares <span> <i class="bx bx-up-arrow-alt align-middle"></i>
                                    Configured</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">System Status</h6>
                        </div>
                        <div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i
                                    class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.buses.index') }}">View Buses</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.counter-terminals.index') }}">View Terminals</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <li
                        class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                        Active Buses <span class="badge bg-success rounded-pill">{{ $stats['active_buses'] }}</span>
                    </li>
                    <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                        Active Terminals <span class="badge bg-primary rounded-pill">{{ $stats['active_terminals'] }}</span>
                    </li>
                    <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                        Total Users <span class="badge bg-info rounded-pill">{{ $stats['total_users'] }}</span>
                    </li>
                    <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                        Pending Enquiries <span class="badge bg-warning text-dark rounded-pill">{{ $stats['pending_enquiries'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div><!--end row-->

    <div class="card radius-10">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <div>
                    <h6 class="mb-0">Recent Routes</h6>
                </div>
                <div class="dropdown ms-auto">
                    <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i
                            class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.routes.index') }}">View All Routes</a>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('admin.routes.create') }}">Add New Route</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Route Name</th>
                            <th>Code</th>
                            <th>Direction</th>
                            <th>Status</th>
                            <th>Stops</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentData['recent_routes'] as $route)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white me-2">
                                            <i class='bx bx-map'></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $route->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $route->code }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $route->direction === 'forward' ? 'success' : 'warning' }}">
                                        {{ ucfirst($route->direction) }}
                                    </span>
                                </td>
                                <td>
                                    @if($route->status instanceof \App\Enums\RouteStatusEnum)
                                        {!! \App\Enums\RouteStatusEnum::getStatusBadge($route->status->value) !!}
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($route->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $route->routeStops->count() }} stops</span>
                                </td>
                                <td>{{ $route->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.routes.edit', $route->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.routes.stops', $route->id) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bx bx-map"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bx bx-map me-2"></i>No routes found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12 col-lg-7 col-xl-8 d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">Recent Enquiries</h6>
                        </div>
                        <div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                data-bs-toggle="dropdown"><i
                                    class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.enquiries.index') }}">View All Enquiries</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentData['recent_enquiries'] as $enquiry)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="widgets-icons-2 rounded-circle bg-gradient-ibiza text-white me-2">
                                                    <i class='bx bx-user'></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $enquiry->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $enquiry->email }}</td>
                                        <td>{{ Str::limit($enquiry->subject, 30) }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'in_progress' => 'info', 
                                                    'resolved' => 'success',
                                                    'closed' => 'secondary',
                                                    'rejected' => 'danger'
                                                ];
                                                $color = $statusColors[$enquiry->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ ucfirst(str_replace('_', ' ', $enquiry->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $enquiry->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bx bx-message-dots me-2"></i>No enquiries found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5 col-xl-4 d-flex">
            <div class="card w-100 radius-10">
                <div class="card-body">
                    <div class="card radius-10 border shadow-none">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Routes</p>
                                    <h4 class="my-1">{{ $stats['total_routes'] }}</h4>
                                    <p class="mb-0 font-13">{{ $stats['active_routes'] }} active</p>
                                </div>
                                <div class="widgets-icons-2 bg-gradient-cosmic text-white ms-auto"><i
                                        class='bx bx-map'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card radius-10 border shadow-none">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Buses</p>
                                    <h4 class="my-1">{{ $stats['total_buses'] }}</h4>
                                    <p class="mb-0 font-13">{{ $stats['active_buses'] }} active</p>
                                </div>
                                <div class="widgets-icons-2 bg-gradient-ibiza text-white ms-auto"><i
                                        class='bx bx-bus'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card radius-10 mb-0 border shadow-none">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total Users</p>
                                    <h4 class="my-1">{{ $stats['total_users'] }}</h4>
                                    <p class="mb-0 font-13">System users</p>
                                </div>
                                <div class="widgets-icons-2 bg-gradient-kyoto text-dark ms-auto"><i
                                        class='bx bx-group'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--end row-->

    <div class="row row-cols-1 row-cols-lg-3">
        <div class="col d-flex">
            <div class="card radius-10 w-100">
                <div class="card-body">
                    <p class="font-weight-bold mb-1 text-secondary">System Overview</p>
                    <div class="d-flex align-items-center mb-4">
                        <div>
                            <h4 class="mb-0">{{ $stats['total_routes'] + $stats['total_buses'] + $stats['total_terminals'] }}</h4>
                            <p class="mb-0 text-muted">Total Entities</p>
                        </div>
                        <div class="">
                            <p class="mb-0 align-self-center font-weight-bold text-success ms-2">
                                <i class="bx bx-trending-up mr-2"></i>Active
                            </p>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h6 class="mb-0 text-primary">{{ $stats['active_routes'] }}</h6>
                                <small class="text-muted">Active Routes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h6 class="mb-0 text-success">{{ $stats['active_buses'] }}</h6>
                                <small class="text-muted">Active Buses</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">Routes Summary</h6>
                        </div>
                        <div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                data-bs-toggle="dropdown"><i
                                    class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.routes.index') }}">View All Routes</a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.routes.create') }}">Add New Route</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-1 mt-3">
                        <canvas id="routesSummaryChart"></canvas>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <li
                        class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                        Active Routes <span class="badge bg-gradient-quepal rounded-pill">{{ $stats['active_routes'] }}</span>
                    </li>
                    <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                        Total Stops <span class="badge bg-gradient-ibiza rounded-pill">{{ $stats['total_stops'] }}</span>
                    </li>
                    <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                        Total Fares <span class="badge bg-gradient-deepblue rounded-pill">{{ $stats['total_fares'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">Enquiries Status</h6>
                        </div>
                        <div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                data-bs-toggle="dropdown"><i
                                    class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.enquiries.index') }}">View All Enquiries</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container-0">
                        <canvas id="enquiriesChart"></canvas>
                    </div>
                </div>
                <div class="row row-group border-top g-0">
                    <div class="col">
                        <div class="p-3 text-center">
                            <h4 class="mb-0 text-warning">{{ $stats['pending_enquiries'] }}</h4>
                            <p class="mb-0">Pending</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 text-center">
                            <h4 class="mb-0 text-success">{{ $stats['total_enquiries'] - $stats['pending_enquiries'] }}</h4>
                            <p class="mb-0">Resolved</p>
                        </div>
                    </div>
                </div><!--end row-->
            </div>
        </div>
    </div><!--end row-->

@endsection

@section('scripts')
<script src="{{ asset('admin/assets/plugins/chartjs/js/Chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Routes Chart
    const routesCtx = document.getElementById('routesChart').getContext('2d');
    new Chart(routesCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Active Routes',
                data: [{{ $chartData['monthly_routes']->pluck('count')->join(',') }}],
                borderColor: '#14abef',
                backgroundColor: 'rgba(20, 171, 239, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active Buses', 'Inactive Buses'],
            datasets: [{
                data: [{{ $chartData['buses_by_status']['active'] ?? 0 }}, {{ $chartData['buses_by_status']['inactive'] ?? 0 }}],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Routes Summary Chart
    const routesSummaryCtx = document.getElementById('routesSummaryChart').getContext('2d');
    new Chart(routesSummaryCtx, {
        type: 'bar',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                label: 'Routes',
                data: [{{ $chartData['routes_by_status']['active'] ?? 0 }}, {{ $chartData['routes_by_status']['inactive'] ?? 0 }}],
                backgroundColor: ['#28a745', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Enquiries Chart
    const enquiriesCtx = document.getElementById('enquiriesChart').getContext('2d');
    new Chart(enquiriesCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'Resolved'],
            datasets: [{
                data: [{{ $chartData['enquiries_by_status']['pending'] ?? 0 }}, {{ $chartData['enquiries_by_status']['resolved'] ?? 0 }}],
                backgroundColor: ['#ffc107', '#28a745'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endsection

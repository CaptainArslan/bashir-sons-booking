@extends('admin.layouts.app')

@section('title', 'Route Timetable Details')

@section('styles')
<style>
    .timetable-card {
        border-left: 4px solid #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header-info h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .info-card {
        border-left: 3px solid #0dcaf0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .stats-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
    }
    
    .section-divider {
        border-top: 1px solid #e9ecef;
        margin: 1rem 0;
        padding-top: 1rem;
    }
    
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .stops-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        border-left: 4px solid #0dcaf0;
    }
    
    .stop-item {
        transition: all 0.2s ease;
        border-left: 3px solid #0d6efd !important;
        background: #ffffff;
        border-radius: 6px;
    }
    
    .stop-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
        transform: translateX(2px);
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
    }
    
    .table th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .table td {
        font-size: 0.875rem;
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.route-timetables.index') }}">Route Timetables</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Timetable Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card timetable-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-time me-2"></i>Timetable Details: {{ $routeTimetable->trip_code }}</h5>
                </div>

                <div class="card-body">
                    <!-- Timetable Information -->
                    <div class="section-title">
                        <i class="bx bx-time me-1"></i>Timetable Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card info-card">
                                <div class="card-body" style="padding: 0.75rem;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Trip Code:</strong> 
                                                <span class="badge bg-primary">{{ $routeTimetable->trip_code }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Status:</strong> 
                                                <span class="badge bg-{{ $routeTimetable->is_active ? 'success' : 'danger' }} stats-badge">
                                                    {{ $routeTimetable->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Departure:</strong> 
                                                <span class="badge bg-info">{{ $routeTimetable->departure_time }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Arrival:</strong> 
                                                @if($routeTimetable->arrival_time)
                                                    <span class="badge bg-success">{{ $routeTimetable->arrival_time }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Not set</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Frequency:</strong> 
                                                <span class="badge bg-{{ $routeTimetable->frequency->getFrequencyTypeColor($routeTimetable->frequency->value) }} stats-badge">
                                                    {{ $routeTimetable->frequency->getName() }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Created:</strong> 
                                                {{ $routeTimetable->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    @if($routeTimetable->frequency->value === 'custom' && $routeTimetable->operating_days)
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Operating Days:</strong>
                                                    @foreach($routeTimetable->operating_days as $day)
                                                        <span class="badge bg-secondary me-1">{{ ucfirst($day) }}</span>
                                                    @endforeach
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card info-card">
                                <div class="card-body" style="padding: 0.75rem;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Route:</strong> 
                                                <span class="badge bg-primary">{{ $routeTimetable->route->name }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Code:</strong> 
                                                <span class="badge bg-info">{{ $routeTimetable->route->code }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Stops:</strong> 
                                                <span class="badge bg-success">{{ $routeTimetable->route->totalStops ?? 'N/A' }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Distance:</strong> 
                                                <span class="badge bg-warning">{{ $routeTimetable->route->totalDistance ?? 'N/A' }} km</span>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Travel Time:</strong> 
                                                <span class="badge bg-secondary">{{ $routeTimetable->route->totalTravelTime ?? 'N/A' }} min</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>First Terminal:</strong> 
                                                {{ $routeTimetable->route->firstTerminal?->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Last Terminal:</strong> 
                                                {{ $routeTimetable->route->lastTerminal?->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stop Times Section -->
                    <div class="section-divider"></div>
                    <div class="section-title">
                        <i class="bx bx-map me-1"></i>Stop Times
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="stops-section">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-map text-primary me-2" style="font-size: 1.2rem;"></i>
                                        <h6 class="mb-0 fw-bold text-primary">Route Stop Times</h6>
                                    </div>
                                    @if($routeTimetable->stops->isNotEmpty())
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.route-stop-times.edit', $routeTimetable) }}" class="btn btn-warning btn-sm">
                                                <i class="bx bx-edit me-1"></i>Edit Stop Times
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if($routeTimetable->stops->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sequence</th>
                                                    <th>Terminal</th>
                                                    <th>Arrival Time</th>
                                                    <th>Departure Time</th>
                                                    <th>Online Booking</th>
                                                    <th>Distance</th>
                                                    <th>Travel Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($routeTimetable->stops->sortBy('sequence') as $stopTime)
                                                    <tr class="stop-item">
                                                        <td>
                                                            <span class="badge bg-primary">{{ $stopTime->sequence }}</span>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $stopTime->routeStop->terminal->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $stopTime->routeStop->terminal->city->name }}</small>
                                                        </td>
                                                        <td>
                                                            @if($stopTime->arrival_time)
                                                                <span class="badge bg-info">{{ $stopTime->arrival_time }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($stopTime->departure_time)
                                                                <span class="badge bg-success">{{ $stopTime->departure_time }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($stopTime->allow_online_booking)
                                                                <span class="badge bg-success">Allowed</span>
                                                            @else
                                                                <span class="badge bg-danger">Not Allowed</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($stopTime->routeStop->distance_from_previous)
                                                                {{ $stopTime->routeStop->distance_from_previous }} km
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($stopTime->routeStop->approx_travel_time)
                                                                {{ $stopTime->routeStop->approx_travel_time }} min
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bx bx-time me-2" style="font-size: 3rem;"></i>
                                            <h5 class="mt-3">No Stop Times Configured</h5>
                                        </div>
                                        <p class="text-muted mb-0 mt-2">Add stop times to define when the bus arrives and departs from each terminal.</p>
                                        <div class="mt-3">
                                            {{-- <a href="{{ route('admin.route-stop-times.create', $routeTimetable) }}" class="btn btn-primary me-2">
                                                <i class="bx bx-plus me-1"></i>Add Stop Times
                                            </a> --}}
                                            <a href="{{ route('admin.route-stop-times.generate', $routeTimetable) }}" class="btn btn-info">
                                                <i class="bx bx-magic-wand me-1"></i>Generate Stop Times
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('admin.route-timetables.index') }}" class="btn btn-light px-4">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.route-timetables.edit', $routeTimetable) }}" class="btn btn-warning px-4">
                                <i class="bx bx-edit me-1"></i>Edit Timetable
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

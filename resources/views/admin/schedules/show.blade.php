@extends('admin.layouts.app')

@section('title', 'Schedule Details')

@section('styles')
<style>
    .schedule-card {
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Schedule Details</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary">
                <i class="bx bx-edit"></i> Edit Schedule
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card schedule-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-show me-2"></i>Schedule Details: {{ $schedule->trip_code }}</h5>
                </div>

                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="section-title">
                        <i class="bx bx-time me-1"></i>Schedule Information
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card info-card">
                                <div class="card-body" style="padding: 0.75rem;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Trip Code:</strong> 
                                                <span class="badge bg-primary">{{ $schedule->trip_code }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Status:</strong> 
                                                @if($schedule->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Departure:</strong> 
                                                <span class="badge bg-info">{{ $schedule->departure_time }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Arrival:</strong> 
                                                @if($schedule->arrival_time)
                                                    <span class="badge bg-success">{{ $schedule->arrival_time }}</span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Frequency:</strong> 
                                                <span class="badge bg-warning">{{ $schedule->frequency->getName() }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Created:</strong> 
                                                <span class="badge bg-secondary">{{ $schedule->created_at->format('M d, Y') }}</span>
                                            </p>
                                        </div>
                                    </div>
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
                                                <span class="badge bg-primary">{{ $schedule->route->name }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Code:</strong> 
                                                <span class="badge bg-info">{{ $schedule->route->code }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Stops:</strong> 
                                                <span class="badge bg-success">{{ $schedule->route->totalStops ?? 'N/A' }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Distance:</strong> 
                                                <span class="badge bg-warning">{{ $schedule->route->totalDistance ?? 'N/A' }} km</span>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1" style="font-size: 0.85rem;">
                                                <strong>Travel Time:</strong> 
                                                <span class="badge bg-secondary">{{ $schedule->route->totalTravelTime ?? 'N/A' }} min</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Days -->
                    @if($schedule->frequency->value === 'custom' && $schedule->operating_days)
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-calendar me-1"></i>Operating Days
                        </div>
                        
                        <div class="stops-section">
                            <div class="row">
                                @foreach($schedule->operating_days as $day)
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <div class="stop-item p-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-calendar-check text-primary me-2"></i>
                                                <span class="fw-bold">{{ ucfirst($day) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Route Stops -->
                    @if($schedule->route->routeStops && $schedule->route->routeStops->count() > 0)
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-map me-1"></i>Route Stops
                        </div>
                        
                        <div class="stops-section">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sequence</th>
                                            <th>Terminal</th>
                                            <th>City</th>
                                            <th>Distance</th>
                                            <th>Travel Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedule->route->routeStops as $stop)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">{{ $stop->sequence }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $stop->terminal->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $stop->terminal->code }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $stop->terminal->city->name }}</span>
                                                </td>
                                                <td>
                                                    @if($stop->distance_from_previous)
                                                        <span class="badge bg-warning">{{ $stop->distance_from_previous }} km</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($stop->travel_time_from_previous)
                                                        <span class="badge bg-secondary">{{ $stop->travel_time_from_previous }} min</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Schedule Stops -->
                    @if($schedule->stops && $schedule->stops->count() > 0)
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-time-five me-1"></i>Schedule Stop Times
                        </div>
                        
                        <div class="stops-section">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Stop</th>
                                            <th>Terminal</th>
                                            <th>Arrival Time</th>
                                            <th>Departure Time</th>
                                            <th>Booking</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedule->stopsOrdered as $stop)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">{{ $stop->sequence }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $stop->routeStop->terminal->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $stop->routeStop->terminal->code }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($stop->arrival_time)
                                                        <span class="badge bg-success">{{ $stop->arrival_time }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($stop->departure_time)
                                                        <span class="badge bg-info">{{ $stop->departure_time }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($stop->allow_online_booking)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('admin.schedules.index') }}" class="btn btn-light px-4">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('admin.schedules.toggle-status', $schedule) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-{{ $schedule->is_active ? 'warning' : 'success' }} px-4">
                                    <i class="bx bx-{{ $schedule->is_active ? 'pause' : 'play' }} me-1"></i>
                                    {{ $schedule->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary px-4">
                                <i class="bx bx-edit me-1"></i>Edit Schedule
                            </a>
                            <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this schedule?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="bx bx-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

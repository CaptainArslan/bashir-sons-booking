@extends('admin.layouts.app')

@section('title', 'Route Timetable Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Timetable Details: {{ $routeTimetable->trip_code }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.route-timetables.edit', $routeTimetable) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.route-timetables.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Timetable Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h4 class="card-title">Timetable Information</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Trip Code:</strong></td>
                                            <td>{{ $routeTimetable->trip_code }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Route:</strong></td>
                                            <td>{{ $routeTimetable->route->name }} ({{ $routeTimetable->route->code }})</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Departure Time:</strong></td>
                                            <td><span class="badge badge-info">{{ $routeTimetable->departure_time }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Arrival Time:</strong></td>
                                            <td>
                                                @if($routeTimetable->arrival_time)
                                                    <span class="badge badge-success">{{ $routeTimetable->arrival_time }}</span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Frequency:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $routeTimetable->frequency->getFrequencyTypeColor($routeTimetable->frequency->value) }}">
                                                    {{ $routeTimetable->frequency->getName() }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($routeTimetable->frequency->value === 'custom' && $routeTimetable->operating_days)
                                            <tr>
                                                <td><strong>Operating Days:</strong></td>
                                                <td>
                                                    @foreach($routeTimetable->operating_days as $day)
                                                        <span class="badge badge-secondary">{{ ucfirst($day) }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($routeTimetable->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h4 class="card-title">Route Information</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Total Stops:</strong></td>
                                            <td>{{ $routeTimetable->route->totalStops }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Distance:</strong></td>
                                            <td>{{ $routeTimetable->route->totalDistance }} km</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Travel Time:</strong></td>
                                            <td>{{ $routeTimetable->route->totalTravelTime }} minutes</td>
                                        </tr>
                                        <tr>
                                            <td><strong>First Terminal:</strong></td>
                                            <td>{{ $routeTimetable->route->firstTerminal?->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Terminal:</strong></td>
                                            <td>{{ $routeTimetable->route->lastTerminal?->name }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stop Times Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="card-title">Stop Times</h4>
                                    @if($routeTimetable->stops->isNotEmpty())
                                        <div class="card-tools">
                                            <a href="{{ route('admin.route-stop-times.edit', $routeTimetable) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit Stop Times
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="card-body">
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
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-primary">{{ $stopTime->sequence }}</span>
                                                            </td>
                                                            <td>
                                                                <strong>{{ $stopTime->routeStop->terminal->name }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ $stopTime->routeStop->terminal->city->name }}</small>
                                                            </td>
                                                            <td>
                                                                @if($stopTime->arrival_time)
                                                                    <span class="badge badge-info">{{ $stopTime->arrival_time }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($stopTime->departure_time)
                                                                    <span class="badge badge-success">{{ $stopTime->departure_time }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($stopTime->allow_online_booking)
                                                                    <span class="badge badge-success">Allowed</span>
                                                                @else
                                                                    <span class="badge badge-danger">Not Allowed</span>
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
                                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                            <h5>No Stop Times Configured</h5>
                                            <p class="text-muted">Add stop times to define when the bus arrives and departs from each terminal.</p>
                                            <div class="mt-3">
                                                <a href="{{ route('admin.route-stop-times.create', $routeTimetable) }}" class="btn btn-primary me-2">
                                                    <i class="fas fa-plus"></i> Add Stop Times
                                                </a>
                                                <a href="{{ route('admin.route-stop-times.generate', $routeTimetable) }}" class="btn btn-info">
                                                    <i class="fas fa-magic"></i> Generate Stop Times
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

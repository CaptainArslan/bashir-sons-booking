@extends('admin.layouts.app')

@section('title', 'Route Timetables')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Route Timetables</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.route-timetables.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Timetable
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="route_id" class="form-control">
                                    <option value="">All Routes</option>
                                    @foreach($routes as $route)
                                        <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Timetables Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Trip Code</th>
                                    <th>Route</th>
                                    <th>Departure Time</th>
                                    <th>Arrival Time</th>
                                    <th>Frequency</th>
                                    <th>Operating Days</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timetables as $timetable)
                                    <tr>
                                        <td>
                                            <strong>{{ $timetable->trip_code }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $timetable->route->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $timetable->route->code }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $timetable->departure_time }}</span>
                                        </td>
                                        <td>
                                            @if($timetable->arrival_time)
                                                <span class="badge badge-success">{{ $timetable->arrival_time }}</span>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $timetable->frequency->getFrequencyTypeColor($timetable->frequency->value) }}">
                                                {{ $timetable->frequency->getName() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($timetable->frequency->value === 'custom' && $timetable->operating_days)
                                                @foreach($timetable->operating_days as $day)
                                                    <span class="badge badge-secondary">{{ ucfirst($day) }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($timetable->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.route-timetables.show', $timetable) }}" 
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.route-timetables.edit', $timetable) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.route-timetables.toggle-status', $timetable) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-{{ $timetable->is_active ? 'secondary' : 'success' }} btn-sm" 
                                                            title="{{ $timetable->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $timetable->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.route-timetables.destroy', $timetable) }}" 
                                                      class="d-inline" onsubmit="return confirm('Are you sure you want to delete this timetable?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No timetables found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $timetables->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

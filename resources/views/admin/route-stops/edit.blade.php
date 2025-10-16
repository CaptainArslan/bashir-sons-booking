@extends('admin.layouts.app')

@section('title', 'Edit Route Stop')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.route-stops.index') }}">Route Stops</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Route Stop</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.route-stops.update', $routeStop->id) }}" method="POST" class="row g-3">
                @method('PUT')
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit Route Stop</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="route_id" class="form-label">Route <span class="text-danger">*</span></label>
                                <select class="form-select @error('route_id') is-invalid @enderror" id="route_id" name="route_id" required>
                                    <option value="">Select Route</option>
                                    @foreach ($routes as $route)
                                        <option value="{{ $route->id }}" {{ old('route_id', $routeStop->route_id) == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('route_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="terminal_id" class="form-label">Terminal <span class="text-danger">*</span></label>
                                <select class="form-select @error('terminal_id') is-invalid @enderror" id="terminal_id" name="terminal_id" required>
                                    <option value="">Select Terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}" {{ old('terminal_id', $routeStop->terminal_id) == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->name }} - {{ $terminal->city->name }} ({{ $terminal->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('terminal_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- <div class="col-md-6">
                                <label for="sequence" class="form-label">Sequence <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('sequence') is-invalid @enderror" id="sequence"
                                    name="sequence" placeholder="Enter sequence number" value="{{ old('sequence', $routeStop->sequence) }}" min="1" readonly>
                                <div class="form-text">Sequence is automatically managed and cannot be edited</div>
                                @error('sequence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            
                            <div class="col-md-6">
                                <label for="distance_from_previous" class="form-label">Distance from Previous (km)</label>
                                <input type="number" class="form-control @error('distance_from_previous') is-invalid @enderror" id="distance_from_previous"
                                    name="distance_from_previous" placeholder="Enter distance in kilometers" 
                                    value="{{ old('distance_from_previous', $routeStop->distance_from_previous) }}" 
                                    step="0.1" min="0">
                                @error('distance_from_previous')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="approx_travel_time" class="form-label">Approximate Travel Time (minutes)</label>
                                <input type="number" class="form-control @error('approx_travel_time') is-invalid @enderror" id="approx_travel_time"
                                    name="approx_travel_time" placeholder="Enter travel time in minutes" 
                                    value="{{ old('approx_travel_time', $routeStop->approx_travel_time) }}" 
                                    min="0">
                                @error('approx_travel_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_pickup_allowed" name="is_pickup_allowed" value="1" 
                                        {{ old('is_pickup_allowed', $routeStop->is_pickup_allowed) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_pickup_allowed">
                                        Pickup Allowed
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_dropoff_allowed" name="is_dropoff_allowed" value="1" 
                                        {{ old('is_dropoff_allowed', $routeStop->is_dropoff_allowed) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_dropoff_allowed">
                                        Dropoff Allowed
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.route-stops.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Update Route Stop
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
@endsection

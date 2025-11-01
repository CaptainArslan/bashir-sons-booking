@extends('admin.layouts.app')

@section('title', 'Edit Bus Assignment')

@section('content')
<div class="container-fluid p-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-edit"></i> Edit Bus Assignment
            </h5>
        </div>
        <div class="card-body">
            <form id="assignmentForm" method="POST" action="{{ route('admin.bus-assignments.update', $busAssignment) }}">
                @csrf
                @method('PUT')

                <!-- Trip & Segment Info (Read-only) -->
                <div class="alert alert-info">
                    <strong>Trip:</strong> {{ $busAssignment->trip->route->name ?? 'N/A' }}<br>
                    <strong>Segment:</strong> {{ $busAssignment->segment_label }}<br>
                    <small class="text-muted">Segment cannot be changed. Delete and create a new assignment to change segment.</small>
                </div>

                <!-- Bus Selection -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Bus <span class="text-danger">*</span></label>
                    <select class="form-select" name="bus_id" required>
                        <option value="">-- Select Bus --</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ $busAssignment->bus_id == $bus->id ? 'selected' : '' }}>
                                {{ $bus->name }} - {{ $bus->registration_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('bus_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <!-- Driver Information -->
                <h6 class="fw-bold mb-3"><i class="fas fa-user-tie"></i> Driver Information</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Driver Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="driver_name" value="{{ old('driver_name', $busAssignment->driver_name) }}" required maxlength="255">
                        @error('driver_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Driver Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="driver_phone" value="{{ old('driver_phone', $busAssignment->driver_phone) }}" required maxlength="20">
                        @error('driver_phone')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Driver CNIC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="driver_cnic" value="{{ old('driver_cnic', $busAssignment->driver_cnic) }}" required maxlength="50">
                        @error('driver_cnic')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Driver License <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="driver_license" value="{{ old('driver_license', $busAssignment->driver_license) }}" required maxlength="100">
                        @error('driver_license')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Driver Address</label>
                    <textarea class="form-control" name="driver_address" rows="2" maxlength="500">{{ old('driver_address', $busAssignment->driver_address) }}</textarea>
                    @error('driver_address')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <!-- Host/Attendant Information -->
                <h6 class="fw-bold mb-3"><i class="fas fa-user-friends"></i> Host/Trip Attendant Information</h6>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Host Name</label>
                        <input type="text" class="form-control" name="host_name" value="{{ old('host_name', $busAssignment->host_name) }}" maxlength="255">
                        @error('host_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Host Phone</label>
                        <input type="tel" class="form-control" name="host_phone" value="{{ old('host_phone', $busAssignment->host_phone) }}" maxlength="20">
                        @error('host_phone')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3" maxlength="1000">{{ old('notes', $busAssignment->notes) }}</textarea>
                    @error('notes')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.bus-assignments.index', ['trip_id' => $busAssignment->trip_id]) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


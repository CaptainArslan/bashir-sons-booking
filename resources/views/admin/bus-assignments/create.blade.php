@extends('admin.layouts.app')

@section('title', 'Create Bus Assignment')

@section('content')
<div class="container-fluid p-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-plus-circle"></i> Assign Bus to Terminal Segment
            </h5>
        </div>
        <div class="card-body">
            <form id="assignmentForm" method="POST" action="{{ route('admin.bus-assignments.store') }}">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">

                <!-- Trip Info -->
                <div class="alert alert-info">
                    <strong>Trip:</strong> {{ $trip->route->name ?? 'N/A' }}<br>
                    <strong>Date:</strong> {{ $trip->departure_date->format('M d, Y') }}<br>
                    <strong>Departure:</strong> {{ $trip->departure_datetime->format('H:i') }}
                </div>

                <!-- Segment Selection -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">From Terminal <span class="text-danger">*</span></label>
                        <select class="form-select" name="from_trip_stop_id" id="fromTripStop" required>
                            <option value="">-- Select From Terminal --</option>
                            @foreach($trip->stops->sortBy('sequence') as $stop)
                                <option value="{{ $stop->id }}" data-sequence="{{ $stop->sequence }}">
                                    {{ $stop->terminal->name }} ({{ $stop->terminal->code }}) - Sequence {{ $stop->sequence }}
                                </option>
                            @endforeach
                        </select>
                        @error('from_trip_stop_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">To Terminal <span class="text-danger">*</span></label>
                        <select class="form-select" name="to_trip_stop_id" id="toTripStop" required>
                            <option value="">-- Select To Terminal --</option>
                            @foreach($trip->stops->sortBy('sequence') as $stop)
                                <option value="{{ $stop->id }}" data-sequence="{{ $stop->sequence }}">
                                    {{ $stop->terminal->name }} ({{ $stop->terminal->code }}) - Sequence {{ $stop->sequence }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_trip_stop_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Bus Selection -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Bus <span class="text-danger">*</span></label>
                    <select class="form-select" name="bus_id" required>
                        <option value="">-- Select Bus --</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}">
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
                        <input type="text" class="form-control" name="driver_name" required maxlength="255">
                        @error('driver_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Driver Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="driver_phone" required maxlength="20" placeholder="03001234567">
                        @error('driver_phone')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Driver CNIC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="driver_cnic" required maxlength="50" placeholder="12345-6789012-3">
                        @error('driver_cnic')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Driver License <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="driver_license" required maxlength="100">
                        @error('driver_license')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Driver Address</label>
                    <textarea class="form-control" name="driver_address" rows="2" maxlength="500"></textarea>
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
                        <input type="text" class="form-control" name="host_name" maxlength="255" placeholder="Optional">
                        @error('host_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Host Phone</label>
                        <input type="tel" class="form-control" name="host_phone" maxlength="20" placeholder="Optional">
                        @error('host_phone')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3" maxlength="1000" placeholder="Optional notes about this assignment..."></textarea>
                    @error('notes')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.bus-assignments.index', ['trip_id' => $trip->id]) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Validate that "To" comes after "From" in sequence
    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
        const fromSelect = document.getElementById('fromTripStop');
        const toSelect = document.getElementById('toTripStop');

        if (fromSelect.value && toSelect.value) {
            const fromSequence = parseInt(fromSelect.options[fromSelect.selectedIndex].dataset.sequence);
            const toSequence = parseInt(toSelect.options[toSelect.selectedIndex].dataset.sequence);

            if (fromSequence >= toSequence) {
                e.preventDefault();
                alert('Destination terminal must come after origin terminal in the trip sequence.');
                return false;
            }
        }
    });

    // Update "To" options based on "From" selection
    document.getElementById('fromTripStop').addEventListener('change', function() {
        const fromSequence = parseInt(this.options[this.selectedIndex]?.dataset.sequence || 0);
        const toSelect = document.getElementById('toTripStop');

        Array.from(toSelect.options).forEach(option => {
            if (option.value) {
                const toSequence = parseInt(option.dataset.sequence || 0);
                option.disabled = toSequence <= fromSequence;
            }
        });
    });
</script>
@endsection


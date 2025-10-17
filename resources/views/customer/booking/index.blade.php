@extends('layouts.frontend')

@section('title', 'Book Your Trip')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center">Book Your Trip</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('customer.booking.search') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_id">Select Route</label>
                                    <select name="route_id" id="route_id" class="form-control" required>
                                        <option value="">Choose a route...</option>
                                        @foreach($routes as $route)
                                            <option value="{{ $route->id }}">
                                                {{ $route->name }} ({{ $route->code }})
                                                - {{ $route->firstTerminal?->name }} to {{ $route->lastTerminal?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="travel_date">Travel Date</label>
                                    <input type="date" name="travel_date" id="travel_date" 
                                           class="form-control" 
                                           min="{{ date('Y-m-d') }}" 
                                           value="{{ date('Y-m-d') }}" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from_stop_id">From</label>
                                    <select name="from_stop_id" id="from_stop_id" class="form-control" required>
                                        <option value="">Select departure point...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="to_stop_id">To</label>
                                    <select name="to_stop_id" id="to_stop_id" class="form-control" required>
                                        <option value="">Select destination...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i> Search Available Trips
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const routeSelect = document.getElementById('route_id');
    const fromSelect = document.getElementById('from_stop_id');
    const toSelect = document.getElementById('to_stop_id');

    routeSelect.addEventListener('change', function() {
        const routeId = this.value;
        
        // Clear existing options
        fromSelect.innerHTML = '<option value="">Select departure point...</option>';
        toSelect.innerHTML = '<option value="">Select destination...</option>';

        if (routeId) {
            // Fetch route stops
            fetch(`/api/routes/${routeId}/stops`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(stop => {
                        const option1 = new Option(`${stop.terminal.name} (${stop.terminal.city.name})`, stop.id);
                        const option2 = new Option(`${stop.terminal.name} (${stop.terminal.city.name})`, stop.id);
                        fromSelect.appendChild(option1);
                        toSelect.appendChild(option2);
                    });
                })
                .catch(error => {
                    console.error('Error fetching route stops:', error);
                });
        }
    });
});
</script>
@endsection

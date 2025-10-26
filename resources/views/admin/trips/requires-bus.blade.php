@extends('admin.layouts.app')

@section('title', 'Trips Requiring Bus Assignment')

@section('content')
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="ti ti-alert-circle"></i> Trips Requiring Bus Assignment</h4>
        </div>
        <div class="card-body">
            @if ($trips->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Trip ID</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Bookings</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trips as $trip)
                                <tr>
                                    <td><strong>#{{ $trip->id }}</strong></td>
                                    <td>{{ $trip->route->code }} - {{ $trip->route->name }}</td>
                                    <td>{{ $trip->departure_datetime->format('M d, Y h:i A') }}</td>
                                    <td>{{ $trip->bookings()->count() }} bookings</td>
                                    <td>
                                        <a href="{{ route('admin.trips.show', $trip->id) }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-bus"></i> Assign Bus
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ti ti-check-circle" style="font-size: 4rem; color: #10b981;"></i>
                    <h5 class="mt-3">All trips have buses assigned!</h5>
                </div>
            @endif
        </div>
    </div>
@endsection


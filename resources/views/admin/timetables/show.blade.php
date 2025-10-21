@extends('admin.layouts.app')

@section('title', 'Timetable Details')
@section('styles')
    <style>
        .timetable-details {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .details-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
        }
        
        .details-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .details-content {
            padding: 2rem;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #6c757d;
        }
        
        .stops-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .stops-table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
        }
        
        .stops-table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }
        
        .stops-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        .stops-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .time-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .sequence-badge {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }
        
        .btn-back {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .btn-edit {
            background: #28a745;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-edit:hover {
            background: #218838;
            color: white;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="timetable-details">
                <!-- Header -->
                <div class="details-header">
                    <h4><i class="fas fa-clock me-2"></i>Timetable Details</h4>
                </div>
                
                <!-- Content -->
                <div class="details-content">
                    <!-- Back Button -->
                    <a href="{{ route('admin.timetables.index') }}" class="btn-back">
                        <i class="fas fa-arrow-left me-1"></i>Back to Timetables
                    </a>
                    
                    <!-- Edit Button -->
                    @can('edit timetables')
                    <a href="{{ route('admin.timetables.edit', $timetable->id) }}" class="btn-edit">
                        <i class="fas fa-edit me-1"></i>Edit Timetable
                    </a>
                    @endcan
                    
                    <!-- Timetable Information -->
                    <div class="info-card">
                        <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Timetable Information</h5>
                        <div class="info-row">
                            <span class="info-label">Timetable Name:</span>
                            <span class="info-value">{{ $timetable->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Route:</span>
                            <span class="info-value">{{ $timetable->route->name }} ({{ $timetable->route->code }})</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Start Departure Time:</span>
                            <span class="info-value">
                                <span class="time-badge">{{ $timetable->start_departure_time }}</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">End Arrival Time:</span>
                            <span class="info-value">
                                @if($timetable->end_arrival_time)
                                    <span class="time-badge">{{ $timetable->end_arrival_time }}</span>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                @if($timetable->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Stops:</span>
                            <span class="info-value">{{ $timetableStops->count() }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Created:</span>
                            <span class="info-value">{{ $timetable->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                    
                    <!-- Stops Schedule -->
                    <div class="stops-table">
                        <h5 class="p-3 mb-0"><i class="fas fa-map-marker-alt me-2"></i>Stop Schedule</h5>
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="40%">Terminal</th>
                                    <th width="25%">Arrival Time</th>
                                    <th width="25%">Departure Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timetableStops as $stop)
                                <tr>
                                    <td>
                                        <span class="sequence-badge">{{ $stop->sequence }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $stop->terminal->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $stop->terminal->city->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($stop->arrival_time)
                                            <span class="time-badge">{{ $stop->arrival_time }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($stop->departure_time)
                                            <span class="time-badge">{{ $stop->departure_time }}</span>
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
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="info-card">
                <h5 class="mb-3"><i class="fas fa-route me-2"></i>Route Summary</h5>
                <div class="info-row">
                    <span class="info-label">Route Code:</span>
                    <span class="info-value">{{ $timetable->route->code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Direction:</span>
                    <span class="info-value">{{ ucfirst($timetable->route->direction ?? 'N/A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Distance:</span>
                    <span class="info-value">{{ $timetable->route->total_distance ?? 0 }} km</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Travel Time:</span>
                    <span class="info-value">{{ $timetable->route->total_travel_time ?? 0 }} min</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @if($timetable->route->status->value === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($timetable->route->status->value) }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

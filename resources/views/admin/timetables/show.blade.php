@extends('admin.layouts.app')

@section('title', 'Timetable Details')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .page-header h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-header .meta-info {
        margin-top: 0.75rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        font-size: 0.9rem;
        opacity: 0.95;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .btn-action {
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-back {
        background: #6c757d;
        color: white;
    }

    .btn-back:hover {
        background: #5a6268;
        color: white;
    }

    .btn-edit {
        background: #28a745;
        color: white;
    }

    .btn-edit:hover {
        background: #218838;
        color: white;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .info-card-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e9ecef;
    }

    .info-card-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #495057;
    }

    .info-card-header i {
        color: #667eea;
        font-size: 1.2rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid #667eea;
    }

    .info-item-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .info-item-value {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
    }

    .stops-timeline {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
    }

    .stops-timeline-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .stops-timeline-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #495057;
    }

    .stops-timeline-header i {
        color: #667eea;
        font-size: 1.2rem;
    }

    .stop-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
        position: relative;
    }

    .stop-item:last-child {
        margin-bottom: 0;
    }

    .stop-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .stop-item.start {
        border-left-color: #28a745;
        background: linear-gradient(90deg, #d4edda 0%, #f8f9fa 100%);
    }

    .stop-item.end {
        border-left-color: #dc3545;
        background: linear-gradient(90deg, #f8d7da 0%, #f8f9fa 100%);
    }

    .stop-sequence {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .stop-item.start .stop-sequence {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .stop-item.end .stop-sequence {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .stop-info {
        flex: 1;
    }

    .stop-name {
        font-size: 1rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 0.25rem;
    }

    .stop-city {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .stop-times {
        display: flex;
        gap: 1.5rem;
        align-items: center;
    }

    .time-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 80px;
    }

    .time-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .time-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #667eea;
        font-family: 'Courier New', monospace;
        padding: 0.4rem 0.75rem;
        background: #e3f2fd;
        border-radius: 6px;
        min-width: 70px;
        text-align: center;
    }

    .time-value.empty {
        color: #adb5bd;
        background: #f8f9fa;
    }

    .route-summary-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-left: 4px solid #0dcaf0;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
    }

    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .stop-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .stop-times {
            width: 100%;
            justify-content: space-between;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h2>
                    <i class="fas fa-clock"></i>
                    {{ $timetable->name ?? 'Timetable Details' }}
                </h2>
                <div class="meta-info">
                    <span>
                        <i class="fas fa-calendar-alt"></i>
                        Created: {{ $timetable->created_at->format('M d, Y') }}
                    </span>
                    <span>
                        <i class="fas fa-clock"></i>
                        {{ $timetableStops->count() }} Stops
                    </span>
                    <span>
                        <span class="status-badge {{ $timetable->is_active ? 'active' : 'inactive' }}">
                            {{ $timetable->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </span>
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('admin.timetables.index') }}" class="btn-action btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Back to Timetables
                </a>
                @can('edit timetables')
                <a href="{{ route('admin.timetables.edit', $timetable->id) }}" class="btn-action btn-edit">
                    <i class="fas fa-edit"></i>
                    Edit Timetable
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Timetable Information Card -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-info-circle"></i>
                    <h5>Timetable Information</h5>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-item-label">Timetable Name</span>
                        <span class="info-item-value">{{ $timetable->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Route</span>
                        <span class="info-item-value">
                            {{ $timetable->route->name ?? 'N/A' }}
                            <small class="text-muted">({{ $timetable->route->code ?? 'N/A' }})</small>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Start Departure</span>
                        <span class="info-item-value">
                            <span class="time-value">{{ $timetable->start_departure_time ?? 'N/A' }}</span>
                        </span>
                    </div>
                    @if($timetable->end_arrival_time)
                    <div class="info-item">
                        <span class="info-item-label">End Arrival</span>
                        <span class="info-item-value">
                            <span class="time-value">{{ $timetable->end_arrival_time }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Total Duration</span>
                        <span class="info-item-value">
                            @php
                                $startTime = \Carbon\Carbon::parse($timetable->start_departure_time);
                                $endTime = \Carbon\Carbon::parse($timetable->end_arrival_time);
                                $duration = $startTime->diffInMinutes($endTime);
                                $hours = floor($duration / 60);
                                $minutes = $duration % 60;
                            @endphp
                            @if($hours > 0)
                                {{ $hours }}h {{ $minutes }}m
                            @else
                                {{ $minutes }}m
                            @endif
                        </span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="info-item-label">Status</span>
                        <span class="info-item-value">
                            <span class="status-badge {{ $timetable->is_active ? 'active' : 'inactive' }}">
                                {{ $timetable->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stops Timeline -->
            <div class="stops-timeline">
                <div class="stops-timeline-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <h5>Stop Schedule ({{ $timetableStops->count() }} Stops)</h5>
                </div>
                
                @foreach($timetableStops as $index => $stop)
                    @php
                        $isFirst = $index === 0;
                        $isLast = $index === $timetableStops->count() - 1;
                        $stopClass = $isFirst ? 'start' : ($isLast ? 'end' : '');
                    @endphp
                    <div class="stop-item {{ $stopClass }}">
                        <div class="stop-sequence">{{ $stop->sequence }}</div>
                        <div class="stop-info">
                            <div class="stop-name">{{ $stop->terminal->name }}</div>
                            <div class="stop-city">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $stop->terminal->city->name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="stop-times">
                            @if(!$isFirst)
                            <div class="time-block">
                                <span class="time-label">Arrival</span>
                                <span class="time-value {{ !$stop->arrival_time ? 'empty' : '' }}">
                                    {{ $stop->arrival_time ? \Carbon\Carbon::parse($stop->arrival_time)->format('H:i') : '--:--' }}
                                </span>
                            </div>
                            @endif
                            @if(!$isLast)
                            <div class="time-block">
                                <span class="time-label">Departure</span>
                                <span class="time-value {{ !$stop->departure_time ? 'empty' : '' }}">
                                    {{ $stop->departure_time ? \Carbon\Carbon::parse($stop->departure_time)->format('H:i') : '--:--' }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Route Summary Card -->
            <div class="info-card route-summary-card">
                <div class="info-card-header">
                    <i class="fas fa-route"></i>
                    <h5>Route Summary</h5>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-item-label">Route Code</span>
                        <span class="info-item-value">{{ $timetable->route->code ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Direction</span>
                        <span class="info-item-value">{{ ucfirst($timetable->route->direction ?? 'N/A') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Total Stops</span>
                        <span class="info-item-value">{{ $timetable->route->routeStops->count() }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Route Status</span>
                        <span class="info-item-value">
                            @if($timetable->route->status->value === 'active')
                                <span class="status-badge active">Active</span>
                            @else
                                <span class="status-badge inactive">{{ ucfirst($timetable->route->status->value) }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-chart-bar"></i>
                    <h5>Quick Statistics</h5>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-item-label">Total Stops</span>
                        <span class="info-item-value">{{ $timetableStops->count() }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Starting Point</span>
                        <span class="info-item-value">
                            {{ $timetableStops->first()->terminal->name ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Final Destination</span>
                        <span class="info-item-value">
                            {{ $timetableStops->last()->terminal->name ?? 'N/A' }}
                        </span>
                    </div>
                    @if($timetable->end_arrival_time)
                    <div class="info-item">
                        <span class="info-item-label">Trip Duration</span>
                        <span class="info-item-value">
                            @php
                                $startTime = \Carbon\Carbon::parse($timetable->start_departure_time);
                                $endTime = \Carbon\Carbon::parse($timetable->end_arrival_time);
                                $duration = $startTime->diffInMinutes($endTime);
                                $hours = floor($duration / 60);
                                $minutes = $duration % 60;
                            @endphp
                            @if($hours > 0)
                                {{ $hours }}h {{ $minutes }}m
                            @else
                                {{ $minutes }}m
                            @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Timetable Details')

@section('styles')
<style>
    .page-header {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .page-header h4 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #495057;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.9rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
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

    .stops-timeline {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 1.5rem;
    }

    .stops-timeline-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .stops-timeline-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
    }

    .stop-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 4px;
        border-left: 4px solid #007bff;
    }

    .stop-item:last-child {
        margin-bottom: 0;
    }

    .stop-item.start {
        border-left-color: #28a745;
    }

    .stop-item.end {
        border-left-color: #dc3545;
    }

    .stop-sequence {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #007bff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .stop-item.start .stop-sequence {
        background: #28a745;
    }

    .stop-item.end .stop-sequence {
        background: #dc3545;
    }

    .stop-info {
        flex: 1;
    }

    .stop-name {
        font-size: 1rem;
        font-weight: 600;
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
        font-size: 0.75rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .time-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #007bff;
        padding: 0.35rem 0.75rem;
        background: #e7f3ff;
        border-radius: 4px;
        min-width: 70px;
        text-align: center;
    }

    .time-value.empty {
        color: #adb5bd;
        background: #f8f9fa;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.8rem;
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4>
                    <i class="bx bx-time me-2"></i>{{ $timetable->name ?? 'Timetable Details' }}
                </h4>
                <div class="mt-2">
                    <span class="me-3">
                        <strong>Route:</strong> {{ $timetable->route->name ?? 'N/A' }} ({{ $timetable->route->code ?? 'N/A' }})
                    </span>
                    <span class="me-3">
                        <strong>Status:</strong>
                        <span class="status-badge {{ $timetable->is_active ? 'active' : 'inactive' }}">
                            {{ $timetable->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </span>
                </div>
            </div>
            <div class="action-buttons">
                <a href="{{ route('admin.timetables.index') }}" class="btn-action btn-back">
                    <i class="bx bx-arrow-back"></i>Back
                </a>
                @can('edit timetables')
                <a href="{{ route('admin.timetables.edit', $timetable->id) }}" class="btn-action btn-edit">
                    <i class="bx bx-edit"></i>Edit
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Stops Timeline -->
    <div class="stops-timeline">
        <div class="stops-timeline-header">
            <h5><i class="bx bx-map me-2"></i>Stop Schedule</h5>
            <span class="text-muted">{{ $timetableStops->count() }} Stops</span>
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
                    <div class="stop-city">{{ $stop->terminal->city->name ?? 'N/A' }}</div>
                </div>
                <div class="stop-times">
                    <div class="time-block">
                        <span class="time-label">
                            Arrival
                            @if($isFirst)
                                <small class="text-muted">(Optional)</small>
                            @endif
                        </span>
                        <span class="time-value {{ !$stop->arrival_time ? 'empty' : '' }}">
                            {{ $stop->arrival_time ? \Carbon\Carbon::parse($stop->arrival_time)->format('H:i') : '--:--' }}
                        </span>
                    </div>
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
@endsection

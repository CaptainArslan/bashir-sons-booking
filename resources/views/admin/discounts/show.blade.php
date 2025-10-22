@extends('admin.layouts.app')

@section('title', 'Discount Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Discount Details</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.discounts.index') }}">Discounts</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">{{ $discount->title }}</h4>
                            <div class="d-flex gap-2">
                                @can('edit discounts')
                                    <a href="{{ route('admin.discounts.edit', $discount) }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-edit me-1"></i>
                                        Edit
                                    </a>
                                @endcan
                                <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i>
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Discount Title</label>
                                            <div class="info-value">{{ $discount->title }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Route</label>
                                            <div class="info-value">
                                                @if($discount->route)
                                                    {{ $discount->route->name }} ({{ $discount->route->code }})
                                                @else
                                                    <span class="text-muted">No route assigned</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Discount Type</label>
                                            <div class="info-value">
                                                <span class="badge bg-{{ $discount->discount_type === 'fixed' ? 'success' : 'warning' }}">
                                                    {{ $discount->discount_type === 'fixed' ? 'Fixed' : 'Percentage' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Discount Value</label>
                                            <div class="info-value">
                                                <span class="fw-bold text-primary">{{ $discount->formatted_value }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Start Date</label>
                                            <div class="info-value">
                                                <i class="bx bx-calendar me-1"></i>
                                                {{ $discount->starts_at->format('M d, Y') }}
                                                @if($discount->start_time)
                                                    <small class="text-muted">({{ $discount->start_time->format('H:i') }})</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">End Date</label>
                                            <div class="info-value">
                                                <i class="bx bx-calendar me-1"></i>
                                                {{ $discount->ends_at->format('M d, Y') }}
                                                @if($discount->end_time)
                                                    <small class="text-muted">({{ $discount->end_time->format('H:i') }})</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Status</label>
                                            <div class="info-value">
                                                @if($discount->isExpired())
                                                    <span class="badge bg-danger">Expired</span>
                                                @elseif($discount->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Created By</label>
                                            <div class="info-value">
                                                @if($discount->creator)
                                                    <i class="bx bx-user me-1"></i>
                                                    {{ $discount->creator->name }}
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="info-item mb-3">
                                            <label class="info-label">Active Platforms</label>
                                            <div class="info-value">
                                                @if(count($discount->active_platforms) > 0)
                                                    @foreach($discount->active_platforms as $platform)
                                                        <span class="badge bg-primary me-1">{{ $platform }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No platforms selected</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Discount Information
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">Validity Period</small>
                                            <div class="fw-bold">
                                                {{ $discount->starts_at->format('M d') }} - {{ $discount->ends_at->format('M d, Y') }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">Time Range</small>
                                            <div class="fw-bold">
                                                @if($discount->start_time && $discount->end_time)
                                                    {{ $discount->start_time->format('H:i') }} - {{ $discount->end_time->format('H:i') }}
                                                @else
                                                    <span class="text-muted">All day</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">Created</small>
                                            <div class="fw-bold">
                                                {{ $discount->created_at->format('M d, Y H:i') }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">Last Updated</small>
                                            <div class="fw-bold">
                                                {{ $discount->updated_at->format('M d, Y H:i') }}
                                            </div>
                                        </div>

                                        @if($discount->isValid())
                                            <div class="alert alert-success alert-sm mb-0">
                                                <i class="bx bx-check-circle me-1"></i>
                                                This discount is currently valid and active.
                                            </div>
                                        @elseif($discount->isExpired())
                                            <div class="alert alert-danger alert-sm mb-0">
                                                <i class="bx bx-x-circle me-1"></i>
                                                This discount has expired.
                                            </div>
                                        @else
                                            <div class="alert alert-warning alert-sm mb-0">
                                                <i class="bx bx-pause-circle me-1"></i>
                                                This discount is inactive.
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
</div>

@endsection

@push('styles')
<style>
.info-item {
    padding: 0.5rem 0;
}

.info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    font-size: 1rem;
    color: #212529;
    font-weight: 500;
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endpush

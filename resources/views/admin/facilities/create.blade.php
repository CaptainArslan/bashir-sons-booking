@extends('admin.layouts.app')

@section('title', 'Create Facility')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.facilities.index') }}">Facilities</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Facility</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.facilities.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Create Facility</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Facility Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter Facility Name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3" placeholder="Enter facility description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="icon" class="form-label">Icon Class <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon"
                                    name="icon" placeholder="e.g., bx bx-wifi, bx bx-air-conditioning" value="{{ old('icon') }}" required>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Use Boxicons classes (e.g., bx bx-wifi, bx bx-air-conditioning, bx bx-tv, bx bx-music)
                                </div>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.facilities.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Facility
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

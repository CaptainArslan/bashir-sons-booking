@extends('admin.layouts.app')

@section('title', 'Create Bus')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.buses.index') }}">Buses</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Bus</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.buses.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Create Bus</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Bus Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter Bus Name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('registration_number') is-invalid @enderror" id="registration_number"
                                    name="registration_number" placeholder="Enter Registration Number (e.g., ABC-123)" value="{{ old('registration_number') }}" 
                                    style="text-transform: uppercase;" required>
                                <div class="form-text">Enter in format: ABC-123 (will be converted to uppercase)</div>
                                @error('registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('model') is-invalid @enderror" id="model"
                                    name="model" placeholder="Enter Bus Model" value="{{ old('model') }}" required>
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('color') is-invalid @enderror" id="color"
                                    name="color" placeholder="Enter Bus Color" value="{{ old('color') }}" required>
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bus_type_id" class="form-label">Bus Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('bus_type_id') is-invalid @enderror" id="bus_type_id" name="bus_type_id" required>
                                    <option value="">Select Bus Type</option>
                                    @foreach ($busTypes as $busType)
                                        <option value="{{ $busType->id }}" {{ old('bus_type_id') == $busType->id ? 'selected' : '' }}>{{ $busType->name }}</option>
                                    @endforeach
                                </select>
                                @error('bus_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bus_layout_id" class="form-label">Bus Layout <span class="text-danger">*</span></label>
                                <select class="form-select @error('bus_layout_id') is-invalid @enderror" id="bus_layout_id" name="bus_layout_id" required>
                                    <option value="">Select Bus Layout</option>
                                    @foreach ($busLayouts as $busLayout)
                                        <option value="{{ $busLayout->id }}" {{ old('bus_layout_id') == $busLayout->id ? 'selected' : '' }}>{{ $busLayout->name }} ({{ $busLayout->total_seats }} seats)</option>
                                    @endforeach
                                </select>
                                @error('bus_layout_id')
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
                                    name="description" rows="3" placeholder="Enter bus description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Facilities</label>
                                <div class="row">
                                    @foreach ($facilities as $facility)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="facilities[]" value="{{ $facility->id }}" id="facility_{{ $facility->id }}"
                                                    {{ in_array($facility->id, old('facilities', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="facility_{{ $facility->id }}">
                                                    <i class="{{ $facility->icon }} me-2"></i>{{ $facility->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('facilities')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.buses.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Bus
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

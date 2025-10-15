@extends('admin.layouts.app')

@section('title', 'Edit Banner')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Content Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.banners.index') }}">Banners</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Banner</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @method('PUT')
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit Banner</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Banner Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                                    name="title" placeholder="Enter Banner Title" value="{{ old('title', $banner->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label">Banner Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Banner Type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @if (old('type', $banner->type->value) == $type) selected @endif>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @if (old('status', $banner->status->value) == $status) selected @endif>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="path" class="form-label">Banner Image</label>
                                <input type="file" class="form-control @error('path') is-invalid @enderror" id="path"
                                    name="path" accept="image/*">
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Supported formats: JPEG, PNG, JPG, GIF. Maximum size: 2MB. Leave empty to keep current image.
                                </div>
                                @if($banner->path)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $banner->path) }}" alt="{{ $banner->title }}" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                @endif
                                @error('path')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.banners.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Update Banner
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

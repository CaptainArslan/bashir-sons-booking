@extends('admin.layouts.app')

@section('title', 'Create Permission')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Access Control</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Permission</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.permissions.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Create Permission</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Enter permission name (e.g., view_users, create_posts)" value="{{ old('name') }}" required>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Use lowercase letters and underscores only (e.g., view_users, create_posts, delete_comments)
                                </div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Permission
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

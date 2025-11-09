@extends('admin.layouts.app')

@section('title', 'Invoice Settings')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Settings</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice Settings</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @can('edit general settings')
                <a href="{{ route('admin.invoice-settings.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add New Template
                </a>
            @endcan
        </div>
    </div>
    <!--end breadcrumb-->

    @include('admin.layouts.alerts')

    <div class="row">
        @forelse($invoiceSettings as $setting)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 {{ $setting->is_default ? 'border-primary' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $setting->template_name }}</h5>
                        @if($setting->is_default)
                            <span class="badge bg-primary">Default</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Size:</strong> 
                            <span class="badge bg-info">{{ $setting->size }}</span>
                        </div>
                        @if($setting->invoice_name)
                            <div class="mb-2">
                                <strong>Invoice Name:</strong> {{ $setting->invoice_name }}
                            </div>
                        @endif
                        @if($setting->prefix)
                            <div class="mb-2">
                                <strong>Prefix:</strong> {{ $setting->prefix }}
                            </div>
                        @endif
                        <div class="mb-2">
                            <strong>Status:</strong> 
                            <span class="badge {{ $setting->status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $setting->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        @if($setting->primary_color)
                            <div class="mb-2">
                                <strong>Primary Color:</strong> 
                                <span class="badge" style="background-color: {{ $setting->primary_color }}; color: white;">
                                    {{ $setting->primary_color }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('admin.invoice-settings.show', $setting) }}" class="btn btn-sm btn-info">
                                <i class="bx bx-show"></i> View
                            </a>
                            @can('edit general settings')
                                <a href="{{ route('admin.invoice-settings.edit', $setting) }}" class="btn btn-sm btn-warning">
                                    <i class="bx bx-edit"></i> Edit
                                </a>
                                @if(!$setting->is_default)
                                    <form action="{{ route('admin.invoice-settings.set-default', $setting) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Set this as default template for {{ $setting->size }}?')">
                                            <i class="bx bx-check"></i> Set Default
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.invoice-settings.destroy', $setting) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bx bx-trash"></i> Delete
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-invoice text-muted" style="font-size: 64px;"></i>
                        <h5 class="mt-3">No Invoice Settings Found</h5>
                        <p class="text-muted">Create your first invoice template to get started.</p>
                        @can('edit general settings')
                            <a href="{{ route('admin.invoice-settings.create') }}" class="btn btn-primary mt-3">
                                <i class="bx bx-plus"></i> Create First Template
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection


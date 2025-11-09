@extends('admin.layouts.app')

@section('title', 'View Invoice Setting')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 fw-bold">
                                <i class="bx bx-show me-2"></i>
                                Invoice Setting Details
                            </h4>
                            <p class="text-muted mb-0 mt-1">View invoice template configuration</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.invoice-settings.index') }}">Invoice Settings</a></li>
                                <li class="breadcrumb-item active">View</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $invoiceSetting->template_name }}</h5>
                            <div>
                                @if($invoiceSetting->is_default)
                                    <span class="badge bg-primary me-2">Default</span>
                                @endif
                                <span class="badge {{ $invoiceSetting->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $invoiceSetting->status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Size:</strong> 
                                    <span class="badge bg-info">{{ $invoiceSetting->size }}</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Invoice Name:</strong> {{ $invoiceSetting->invoice_name ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>File Type:</strong> {{ $invoiceSetting->file_type ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Date Format:</strong> {{ $invoiceSetting->invoice_date_format }}
                                </div>
                                @if($invoiceSetting->primary_color)
                                <div class="col-md-4 mb-3">
                                    <strong>Primary Color:</strong> 
                                    <span class="badge" style="background-color: {{ $invoiceSetting->primary_color }}; color: white;">
                                        {{ $invoiceSetting->primary_color }}
                                    </span>
                                </div>
                                @endif
                                @if($invoiceSetting->secondary_color)
                                <div class="col-md-4 mb-3">
                                    <strong>Secondary Color:</strong> 
                                    <span class="badge" style="background-color: {{ $invoiceSetting->secondary_color }}; color: white;">
                                        {{ $invoiceSetting->secondary_color }}
                                    </span>
                                </div>
                                @endif
                                @if($invoiceSetting->text_color)
                                <div class="col-md-4 mb-3">
                                    <strong>Text Color:</strong> 
                                    <span class="badge" style="background-color: {{ $invoiceSetting->text_color }}; color: white;">
                                        {{ $invoiceSetting->text_color }}
                                    </span>
                                </div>
                                @endif
                                @if($invoiceSetting->prefix)
                                <div class="col-md-6 mb-3">
                                    <strong>Prefix:</strong> {{ $invoiceSetting->prefix }}
                                </div>
                                @endif
                                @if($invoiceSetting->number_of_digit)
                                <div class="col-md-6 mb-3">
                                    <strong>Number of Digits:</strong> {{ $invoiceSetting->number_of_digit }}
                                </div>
                                @endif
                                @if($invoiceSetting->numbering_type)
                                <div class="col-md-6 mb-3">
                                    <strong>Numbering Type:</strong> {{ ucfirst($invoiceSetting->numbering_type) }}
                                </div>
                                @endif
                                @if($invoiceSetting->start_number)
                                <div class="col-md-6 mb-3">
                                    <strong>Start Number:</strong> {{ $invoiceSetting->start_number }}
                                </div>
                                @endif
                                @if($invoiceSetting->header_title || $invoiceSetting->header_text)
                                <div class="col-12 mb-3">
                                    <strong>Header:</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        @if($invoiceSetting->header_title)
                                            <h6>{{ $invoiceSetting->header_title }}</h6>
                                        @endif
                                        @if($invoiceSetting->header_text)
                                            <p class="mb-0">{{ $invoiceSetting->header_text }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if($invoiceSetting->footer_title || $invoiceSetting->footer_text)
                                <div class="col-12 mb-3">
                                    <strong>Footer:</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        @if($invoiceSetting->footer_title)
                                            <h6>{{ $invoiceSetting->footer_title }}</h6>
                                        @endif
                                        @if($invoiceSetting->footer_text)
                                            <p class="mb-0">{{ $invoiceSetting->footer_text }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if($invoiceSetting->company_logo)
                                <div class="col-md-6 mb-3">
                                    <strong>Company Logo:</strong>
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($invoiceSetting->company_logo) }}" alt="Company Logo" style="max-height: 100px;">
                                    </div>
                                </div>
                                @endif
                                @if($invoiceSetting->invoice_logo)
                                <div class="col-md-6 mb-3">
                                    <strong>Invoice Logo:</strong>
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($invoiceSetting->invoice_logo) }}" alt="Invoice Logo" style="max-height: 100px;">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.invoice-settings.edit', $invoiceSetting) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-2"></i>Edit
                            </a>
                            <a href="{{ route('admin.invoice-settings.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


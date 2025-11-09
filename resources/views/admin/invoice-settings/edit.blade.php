@extends('admin.layouts.app')

@section('title', 'Edit Invoice Setting')

@section('content')
    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid;
        }
        .form-section.basic {
            border-left-color: #0d6efd;
        }
        .form-section.design {
            border-left-color: #ffc107;
        }
        .form-section.numbering {
            border-left-color: #198754;
        }
        .form-section.content {
            border-left-color: #dc3545;
        }
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .color-preview {
            width: 40px;
            height: 40px;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .preview-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            background: #f8f9fa;
            margin-top: 0.5rem;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .preview-container img {
            max-width: 100%;
            max-height: 120px;
            border-radius: 4px;
        }
        .current-image {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            background: #f8f9fa;
            margin-top: 0.5rem;
        }
        .current-image img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 4px;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 fw-bold">
                                <i class="bx bx-edit me-2"></i>
                                Edit Invoice Setting
                            </h4>
                            <p class="text-muted mb-0 mt-1">Update invoice template settings</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.invoice-settings.index') }}">Invoice Settings</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.layouts.alerts')

            <form action="{{ route('admin.invoice-settings.update', $invoiceSetting) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="form-section basic">
                    <h5 class="mb-3"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Template Name</label>
                            <input type="text" class="form-control @error('template_name') is-invalid @enderror" 
                                   name="template_name" value="{{ old('template_name', $invoiceSetting->template_name) }}" required>
                            @error('template_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Size</label>
                            <select class="form-select @error('size') is-invalid @enderror" name="size" required>
                                <option value="">Select Size</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size }}" {{ old('size', $invoiceSetting->size) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            @error('size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice Name</label>
                            <input type="text" class="form-control" name="invoice_name" value="{{ old('invoice_name', $invoiceSetting->invoice_name) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">File Type</label>
                            <select class="form-select" name="file_type">
                                <option value="">Select File Type</option>
                                @foreach($fileTypes as $type)
                                    <option value="{{ $type }}" {{ old('file_type', $invoiceSetting->file_type) == $type ? 'selected' : '' }}>{{ strtoupper($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" value="1" 
                                       {{ old('status', $invoiceSetting->status) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Set as Default</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" 
                                       {{ old('is_default', $invoiceSetting->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label">Default Template for this Size</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Design Settings -->
                <div class="form-section design">
                    <h5 class="mb-3"><i class="bx bx-palette me-2"></i>Design Settings</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Primary Color</label>
                            <div class="color-picker-wrapper">
                                <input type="color" class="form-control form-control-color" name="primary_color" 
                                       value="{{ old('primary_color', $invoiceSetting->primary_color ?? '#0d6efd') }}" id="primaryColor">
                                <input type="text" class="form-control" id="primaryColorText" 
                                       value="{{ old('primary_color', $invoiceSetting->primary_color ?? '#0d6efd') }}" placeholder="#000000">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Secondary Color</label>
                            <div class="color-picker-wrapper">
                                <input type="color" class="form-control form-control-color" name="secondary_color" 
                                       value="{{ old('secondary_color', $invoiceSetting->secondary_color ?? '#6c757d') }}" id="secondaryColor">
                                <input type="text" class="form-control" id="secondaryColorText" 
                                       value="{{ old('secondary_color', $invoiceSetting->secondary_color ?? '#6c757d') }}" placeholder="#000000">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Text Color</label>
                            <div class="color-picker-wrapper">
                                <input type="color" class="form-control form-control-color" name="text_color" 
                                       value="{{ old('text_color', $invoiceSetting->text_color ?? '#212529') }}" id="textColor">
                                <input type="text" class="form-control" id="textColorText" 
                                       value="{{ old('text_color', $invoiceSetting->text_color ?? '#212529') }}" placeholder="#000000">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Logo</label>
                            @if($invoiceSetting->company_logo)
                                <div class="current-image mb-2">
                                    <img src="{{ Storage::url($invoiceSetting->company_logo) }}" alt="Current Company Logo">
                                    <p class="text-muted mt-2 mb-0">Current Logo</p>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="company_logo" accept="image/*" onchange="previewImage(this, 'companyLogoPreview')">
                            <div class="preview-container" id="companyLogoPreview">
                                @if(!$invoiceSetting->company_logo)
                                    <span class="text-muted">No image selected</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Logo Width</label>
                            <input type="text" class="form-control" name="logo_width" 
                                   value="{{ old('logo_width', $invoiceSetting->logo_width) }}" placeholder="e.g., 100px">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Logo Height</label>
                            <input type="text" class="form-control" name="logo_height" 
                                   value="{{ old('logo_height', $invoiceSetting->logo_height) }}" placeholder="e.g., 50px">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice Logo</label>
                            @if($invoiceSetting->invoice_logo)
                                <div class="current-image mb-2">
                                    <img src="{{ Storage::url($invoiceSetting->invoice_logo) }}" alt="Current Invoice Logo">
                                    <p class="text-muted mt-2 mb-0">Current Logo</p>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="invoice_logo" accept="image/*" onchange="previewImage(this, 'invoiceLogoPreview')">
                            <div class="preview-container" id="invoiceLogoPreview">
                                @if(!$invoiceSetting->invoice_logo)
                                    <span class="text-muted">No image selected</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Numbering -->
                <div class="form-section numbering">
                    <h5 class="mb-3"><i class="bx bx-hash me-2"></i>Invoice Numbering</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Prefix</label>
                            <input type="text" class="form-control" name="prefix" 
                                   value="{{ old('prefix', $invoiceSetting->prefix) }}" placeholder="e.g., INV">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Number of Digits</label>
                            <input type="text" class="form-control" name="number_of_digit" 
                                   value="{{ old('number_of_digit', $invoiceSetting->number_of_digit) }}" placeholder="e.g., 6">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Numbering Type</label>
                            <select class="form-select" name="numbering_type">
                                <option value="">Select Type</option>
                                @foreach($numberingTypes as $type)
                                    <option value="{{ $type }}" {{ old('numbering_type', $invoiceSetting->numbering_type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Number</label>
                            <input type="number" class="form-control" name="start_number" 
                                   value="{{ old('start_number', $invoiceSetting->start_number ?? 1) }}" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Invoice Number</label>
                            <input type="number" class="form-control" name="last_invoice_number" 
                                   value="{{ old('last_invoice_number', $invoiceSetting->last_invoice_number ?? 0) }}" min="0">
                        </div>
                    </div>
                </div>

                <!-- Header & Footer -->
                <div class="form-section content">
                    <h5 class="mb-3"><i class="bx bx-text me-2"></i>Header & Footer Content</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Header Title</label>
                            <input type="text" class="form-control" name="header_title" 
                                   value="{{ old('header_title', $invoiceSetting->header_title) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Footer Title</label>
                            <input type="text" class="form-control" name="footer_title" 
                                   value="{{ old('footer_title', $invoiceSetting->footer_title) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Header Text</label>
                            <textarea class="form-control" name="header_text" rows="4">{{ old('header_text', $invoiceSetting->header_text) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Footer Text</label>
                            <textarea class="form-control" name="footer_text" rows="4">{{ old('footer_text', $invoiceSetting->footer_text) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Invoice Date Format</label>
                            <input type="text" class="form-control" name="invoice_date_format" 
                                   value="{{ old('invoice_date_format', $invoiceSetting->invoice_date_format) }}" required>
                            <small class="text-muted">PHP date format (e.g., Y-M-d h:m:s, d/m/Y, etc.)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-2"></i>Update Invoice Setting
                        </button>
                        <a href="{{ route('admin.invoice-settings.index') }}" class="btn btn-secondary">
                            <i class="bx bx-x me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Color picker synchronization
        document.getElementById('primaryColor').addEventListener('input', function(e) {
            document.getElementById('primaryColorText').value = e.target.value;
        });
        document.getElementById('primaryColorText').addEventListener('input', function(e) {
            if(/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                document.getElementById('primaryColor').value = e.target.value;
            }
        });

        document.getElementById('secondaryColor').addEventListener('input', function(e) {
            document.getElementById('secondaryColorText').value = e.target.value;
        });
        document.getElementById('secondaryColorText').addEventListener('input', function(e) {
            if(/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                document.getElementById('secondaryColor').value = e.target.value;
            }
        });

        document.getElementById('textColor').addEventListener('input', function(e) {
            document.getElementById('textColorText').value = e.target.value;
        });
        document.getElementById('textColorText').addEventListener('input', function(e) {
            if(/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                document.getElementById('textColor').value = e.target.value;
            }
        });

        // Image preview
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '<span class="text-muted">No image selected</span>';
            }
        }
    </script>
@endsection


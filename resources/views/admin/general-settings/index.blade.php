@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('styles')
@endsection

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Content Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">General Settings</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @if($settings)
                <a href="{{ route('admin.general-settings.edit', $settings->id) }}" class="btn btn-primary">
                    <i class="bx bx-edit"></i> Edit Settings
                </a>
            @else
                <a href="{{ route('admin.general-settings.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Create Settings
                </a>
            @endif
        </div>
    </div>
    <!--end breadcrumb-->

    @if($settings)
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Company Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Company Name:</strong></td>
                                        <td>{{ $settings->company_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $settings->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $settings->phone }}</td>
                                    </tr>
                                    @if($settings->alternate_phone)
                                    <tr>
                                        <td><strong>Alternate Phone:</strong></td>
                                        <td>{{ $settings->alternate_phone }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td>{{ $settings->address }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>City:</strong></td>
                                        <td>{{ $settings->city }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>State:</strong></td>
                                        <td>{{ $settings->state }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Country:</strong></td>
                                        <td>{{ $settings->country }}</td>
                                    </tr>
                                    @if($settings->postal_code)
                                    <tr>
                                        <td><strong>Postal Code:</strong></td>
                                        <td>{{ $settings->postal_code }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Contact & Social</h5>
                                <table class="table table-borderless">
                                    @if($settings->website_url)
                                    <tr>
                                        <td><strong>Website:</strong></td>
                                        <td><a href="{{ $settings->website_url }}" target="_blank">{{ $settings->website_url }}</a></td>
                                    </tr>
                                    @endif
                                    @if($settings->tagline)
                                    <tr>
                                        <td><strong>Tagline:</strong></td>
                                        <td>{{ $settings->tagline }}</td>
                                    </tr>
                                    @endif
                                    @if($settings->support_email)
                                    <tr>
                                        <td><strong>Support Email:</strong></td>
                                        <td>{{ $settings->support_email }}</td>
                                    </tr>
                                    @endif
                                    @if($settings->support_phone)
                                    <tr>
                                        <td><strong>Support Phone:</strong></td>
                                        <td>{{ $settings->support_phone }}</td>
                                    </tr>
                                    @endif
                                    @if($settings->business_hours)
                                    <tr>
                                        <td><strong>Business Hours:</strong></td>
                                        <td>{{ $settings->business_hours }}</td>
                                    </tr>
                                    @endif
                                </table>
                                
                                <h5 class="mb-3 mt-4">Social Media</h5>
                                <table class="table table-borderless">
                                    @if($settings->facebook_url)
                                    <tr>
                                        <td><strong>Facebook:</strong></td>
                                        <td><a href="{{ $settings->facebook_url }}" target="_blank">{{ $settings->facebook_url }}</a></td>
                                    </tr>
                                    @endif
                                    @if($settings->instagram_url)
                                    <tr>
                                        <td><strong>Instagram:</strong></td>
                                        <td><a href="{{ $settings->instagram_url }}" target="_blank">{{ $settings->instagram_url }}</a></td>
                                    </tr>
                                    @endif
                                    @if($settings->twitter_url)
                                    <tr>
                                        <td><strong>Twitter:</strong></td>
                                        <td><a href="{{ $settings->twitter_url }}" target="_blank">{{ $settings->twitter_url }}</a></td>
                                    </tr>
                                    @endif
                                    @if($settings->linkedin_url)
                                    <tr>
                                        <td><strong>LinkedIn:</strong></td>
                                        <td><a href="{{ $settings->linkedin_url }}" target="_blank">{{ $settings->linkedin_url }}</a></td>
                                    </tr>
                                    @endif
                                    @if($settings->youtube_url)
                                    <tr>
                                        <td><strong>YouTube:</strong></td>
                                        <td><a href="{{ $settings->youtube_url }}" target="_blank">{{ $settings->youtube_url }}</a></td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        
                        @if($settings->logo || $settings->favicon)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Media</h5>
                                <div class="row">
                                    @if($settings->logo)
                                    <div class="col-md-6">
                                        <h6>Logo</h6>
                                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="Company Logo" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                    @endif
                                    @if($settings->favicon)
                                    <div class="col-md-6">
                                        <h6>Favicon</h6>
                                        <img src="{{ asset('storage/' . $settings->favicon) }}" alt="Favicon" class="img-thumbnail" style="max-width: 100px;">
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-cog display-1 text-muted mb-3"></i>
                        <h4>No Settings Found</h4>
                        <p class="text-muted">General settings have not been configured yet.</p>
                        <a href="{{ route('admin.general-settings.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Create Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
@endsection

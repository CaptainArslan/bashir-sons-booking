@extends('admin.layouts.app')

@section('title', 'Create Booking')

@section('styles')
    <style>
        .booking-card {
            border-left: 4px solid #0d6efd;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header-booking {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px 8px 0 0;
        }

        .card-header-booking h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-left: 4px solid #2196f3;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .info-box p {
            margin: 0;
            font-size: 0.85rem;
            color: #1976d2;
        }

        .search-icon {
            font-size: 1.5rem;
            color: #667eea;
        }
    </style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Booking Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Booking</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card booking-card">
                <div class="card-header-booking">
                    <h5><i class="bx bx-search-alt me-2"></i>Search Available Seats</h5>
                </div>

                <form action="{{ route('admin.bookings.search') }}" method="POST">
                    @csrf

                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Select route, departure date and
                                terminals to check available seats.</p>
                        </div>

                        <!-- Route Selection -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="route_id" class="form-label">
                                    Select Route <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('route_id') is-invalid @enderror" id="route_id"
                                    name="route_id" required>
                                    <option value="">Select a route</option>
                                    @foreach ($routes as $route)
                                        <option value="{{ $route->id }}" {{ old('route_id') == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('route_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date and Time -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="departure_date" class="form-label">
                                    Departure Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('departure_date') is-invalid @enderror"
                                    id="departure_date" name="departure_date" value="{{ old('departure_date', date('Y-m-d')) }}"
                                    min="{{ date('Y-m-d') }}" required>
                                @error('departure_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="departure_time" class="form-label">
                                    Departure Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control @error('departure_time') is-invalid @enderror"
                                    id="departure_time" name="departure_time" value="{{ old('departure_time', '08:00') }}"
                                    required>
                                @error('departure_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Terminal Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="from_terminal_id" class="form-label">
                                    From Terminal <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('from_terminal_id') is-invalid @enderror"
                                    id="from_terminal_id" name="from_terminal_id" required>
                                    <option value="">Select starting terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}"
                                            {{ old('from_terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->name }} - {{ $terminal->city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_terminal_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="to_terminal_id" class="form-label">
                                    To Terminal <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2 @error('to_terminal_id') is-invalid @enderror"
                                    id="to_terminal_id" name="to_terminal_id" required>
                                    <option value="">Select destination terminal</option>
                                    @foreach ($terminals as $terminal)
                                        <option value="{{ $terminal->id }}"
                                            {{ old('to_terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->name }} - {{ $terminal->city->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_terminal_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-light px-4">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-search me-1"></i>Search Seats
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: 'resolve'
            });
        });
    </script>
@endsection


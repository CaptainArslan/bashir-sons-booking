@extends('admin.layouts.app')

@section('title', 'Create Route')

@section('styles')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Transport Management</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Route</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-10 mx-auto">
            <form action="{{ route('admin.routes.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Create Route</h5>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="name" class="form-label">Route Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" placeholder="Enter Route Name" value="{{ old('name') }}"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="direction" class="form-label">Direction <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('direction') is-invalid @enderror" id="direction"
                                    name="direction" required>
                                    <option value="">Select Direction</option>
                                    <option value="forward" {{ old('direction') == 'forward' ? 'selected' : '' }}>Forward
                                    </option>
                                    <option value="return" {{ old('direction') == 'return' ? 'selected' : '' }}>Return
                                    </option>
                                </select>
                                @error('direction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6">
                                <label for="code" class="form-label">Route Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" placeholder="Route code will be auto-generated"
                                    value="{{ old('code') }}" style="text-transform: uppercase;" required readonly>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Code will be auto-generated based on route name and direction
                                </div>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-6">
                                <label for="is_return_of" class="form-label">Return Route Of</label>
                                <select class="form-select @error('is_return_of') is-invalid @enderror" id="is_return_of"
                                    name="is_return_of">
                                    <option value="">Select Return Route (Optional)</option>
                                    @foreach ($routes as $route)
                                        <option value="{{ $route->id }}"
                                            {{ old('is_return_of') == $route->id ? 'selected' : '' }}>
                                            {{ $route->name }} ({{ $route->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select if this route is a return route of another route</div>
                                @error('is_return_of')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="base_currency" class="form-label">Base Currency <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('base_currency') is-invalid @enderror" id="base_currency"
                                    name="base_currency" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency }}"
                                            {{ old('base_currency') == $currency ? 'selected' : '' }}>
                                            {{ $currency }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('base_currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.routes.index') }}" class="btn btn-light px-4">
                                        <i class="bx bx-arrow-back me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>Create Route
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const directionSelect = document.getElementById('direction');
            const codeInput = document.getElementById('code');

            // Auto-generate code when name or direction changes
            function generateRouteCode() {
                const name = nameInput.value.trim();
                const direction = directionSelect.value;

                console.log('Generating code for:', name, direction);

                if (name && direction) {
                    const code = generateCodeFromName(name, direction);
                    console.log('Generated code:', code);
                    codeInput.value = code;
                } else {
                    console.log('Missing name or direction, clearing code');
                    codeInput.value = '';
                }
            }

            // Generate code based on route name and direction
            function generateCodeFromName(name, direction) {
                // Extract city names from route name
                const cities = extractCitiesFromName(name);

                if (cities.length >= 2) {
                    const fromCity = cities[0].substring(0, 3).toUpperCase();
                    const toCity = cities[1].substring(0, 3).toUpperCase();
                    const directionCode = direction === 'forward' ? '001' : '002';
                    return `${fromCity}-${toCity}-${directionCode}`;
                } else if (cities.length === 1) {
                    const city = cities[0].substring(0, 3).toUpperCase();
                    const directionCode = direction === 'forward' ? '001' : '002';
                    return `${city}-ROUTE-${directionCode}`;
                } else {
                    const directionCode = direction === 'forward' ? '001' : '002';
                    return `ROUTE-${directionCode}`;
                }
            }

            // Extract city names from route name
            function extractCitiesFromName(name) {
                const commonPatterns = [
                    /(\w+)\s+to\s+(\w+)/i,
                    /(\w+)\s+-\s+(\w+)/i,
                    /(\w+)\s+→\s+(\w+)/i,
                    /(\w+)\s+and\s+(\w+)/i
                ];

                for (const pattern of commonPatterns) {
                    const match = name.match(pattern);
                    if (match) {
                        return [match[1], match[2]];
                    }
                }

                // If no pattern matches, try to extract words that look like city names
                const words = name.split(/\s+/);
                const cityWords = words.filter(word =>
                    word.length > 2 &&
                    /^[A-Za-z]+$/.test(word) &&
                    !['express', 'route', 'service', 'bus', 'line'].includes(word.toLowerCase())
                );

                return cityWords.slice(0, 2);
            }

            // Event listeners
            nameInput.addEventListener('input', generateRouteCode);
            directionSelect.addEventListener('change', generateRouteCode);

            // Initialize code generation if there are existing values
            if (nameInput.value || directionSelect.value) {
                generateRouteCode();
            }
        });
    </script>
@endsection

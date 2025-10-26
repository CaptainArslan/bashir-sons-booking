@extends('admin.layouts.app')

@section('title', 'Add Expense')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Expense</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.expenses.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Trip <span class="text-danger">*</span></label>
                            <select name="trip_id" class="form-select @error('trip_id') is-invalid @enderror" required>
                                <option value="">Select Trip</option>
                                @foreach ($trips as $t)
                                    <option value="{{ $t->id }}"
                                        {{ (old('trip_id', $trip?->id) == $t->id) ? 'selected' : '' }}>
                                        Trip #{{ $t->id }} - {{ $t->route->code }} ({{ $t->departure_datetime->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('trip_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Expense Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" {{ old('type') == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}" step="0.01" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Incurred Date <span class="text-danger">*</span></label>
                            <input type="date" name="incurred_date"
                                class="form-control @error('incurred_date') is-invalid @enderror"
                                value="{{ old('incurred_date', now()->format('Y-m-d')) }}" required>
                            @error('incurred_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Receipt Number</label>
                            <input type="text" name="receipt_number"
                                class="form-control @error('receipt_number') is-invalid @enderror"
                                value="{{ old('receipt_number') }}">
                            @error('receipt_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
@endsection


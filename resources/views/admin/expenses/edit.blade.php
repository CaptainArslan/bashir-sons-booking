@extends('admin.layouts.app')

@section('title', 'Edit Expense')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Expense #{{ $expense->id }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Trip</label>
                            <input type="text" class="form-control" value="Trip #{{ $expense->trip_id }} - {{ $expense->trip->route->code }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Expense Type</label>
                            <select name="type" class="form-select" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" {{ $expense->type === $type ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" value="{{ $expense->amount }}" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Incurred Date</label>
                            <input type="date" name="incurred_date" class="form-control" value="{{ $expense->incurred_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Receipt Number</label>
                            <input type="text" name="receipt_number" class="form-control" value="{{ $expense->receipt_number }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $expense->description }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Expense</button>
                </div>
            </form>
        </div>
    </div>
@endsection


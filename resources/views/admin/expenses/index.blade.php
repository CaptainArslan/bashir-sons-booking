@extends('admin.layouts.app')

@section('title', 'Expense Management')

@section('content')
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">💰 Expense Management</h4>
                <div>
                    <a href="{{ route('admin.expenses.reports') }}" class="btn btn-light btn-sm me-2">
                        <i class="ti ti-chart-bar"></i> Reports
                    </a>
                    <a href="{{ route('admin.expenses.create') }}" class="btn btn-light btn-sm">
                        <i class="ti ti-plus"></i> Add Expense
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h3 class="text-danger mb-1">₨{{ number_format($stats['total_expenses'], 0) }}</h3>
                        <p class="mb-0 text-muted">Total Expenses</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h3 class="text-primary mb-1">{{ $stats['total_count'] }}</h3>
                        <p class="mb-0 text-muted">Total Records</p>
                    </div>
                </div>
                @foreach (\App\Enums\ExpenseTypeEnum::cases() as $type)
                    @if (isset($stats['by_type'][$type->value]) && $stats['by_type'][$type->value] > 0)
                        <div class="col-md-2">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-{{ $type->color() }} mb-1">
                                    ₨{{ number_format($stats['by_type'][$type->value], 0) }}</h6>
                                <p class="mb-0 text-muted small">{{ $type->label() }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="route_id" class="form-select">
                        <option value="">All Routes</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                {{ $route->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Trip</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Receipt</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenses as $expense)
                            <tr>
                                <td><strong>#{{ $expense->id }}</strong></td>
                                <td>
                                    <a href="{{ route('admin.trips.show', $expense->trip_id) }}">Trip #{{ $expense->trip_id }}</a><br>
                                    <small class="text-muted">{{ $expense->trip->route->code }}</small>
                                </td>
                                <td><span class="badge bg-{{ $expense->type->color() }}">{{ $expense->type->label() }}</span></td>
                                <td>{{ $expense->description }}</td>
                                <td><strong>₨{{ number_format($expense->amount, 0) }}</strong></td>
                                <td>{{ $expense->incurred_date->format('M d, Y') }}</td>
                                <td>{{ $expense->receipt_number ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this expense?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $expenses->links() }}
        </div>
    </div>
@endsection


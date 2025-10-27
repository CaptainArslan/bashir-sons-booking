<?php

namespace App\Services;

use App\Enums\ExpenseTypeEnum;
use App\Models\Expense;
use App\Models\Trip;
use Illuminate\Support\Collection;

class ExpenseService
{
    /**
     * Add expense to trip
     */
    public function addExpense(array $data): Expense
    {
        $trip = Trip::findOrFail($data['trip_id']);

        if (! $trip->canAddExpenses()) {
            throw new \Exception(
                'Cannot add expenses to trip. Trip must have a bus assigned and not be in pending or cancelled status.'
            );
        }

        return Expense::create([
            'trip_id' => $data['trip_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'PKR',
            'description' => $data['description'] ?? null,
            'incurred_by' => $data['incurred_by'] ?? auth()->id(),
            'incurred_date' => $data['incurred_date'] ?? now(),
            'receipt_number' => $data['receipt_number'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Update expense
     */
    public function updateExpense(int|string $expenseId, array $data): Expense
    {
        $expense = Expense::findOrFail($expenseId);

        // Check if trip is still editable
        if ($expense->trip->status->value === 'completed') {
            throw new \Exception('Cannot edit expenses for completed trips.');
        }

        $expense->update($data);

        return $expense->fresh();
    }

    /**
     * Delete expense
     */
    public function deleteExpense(int|string $expenseId): bool
    {
        $expense = Expense::findOrFail($expenseId);

        // Check if trip is still editable
        if ($expense->trip->status->value === 'completed') {
            throw new \Exception('Cannot delete expenses for completed trips.');
        }

        return $expense->delete();
    }

    /**
     * Get trip expenses summary
     */
    public function getTripExpensesSummary(int $tripId): array
    {
        $trip = Trip::with('expenses')->findOrFail($tripId);

        $expenses = $trip->expenses;

        $summary = [
            'trip_id' => $tripId,
            'total_expenses' => $expenses->sum('amount'),
            'currency' => 'PKR',
            'by_type' => [],
            'expenses_count' => $expenses->count(),
        ];

        foreach (ExpenseTypeEnum::cases() as $type) {
            $typeExpenses = $expenses->where('type', $type);
            $summary['by_type'][$type->value] = [
                'type' => $type->value,
                'label' => $type->label(),
                'total' => $typeExpenses->sum('amount'),
                'count' => $typeExpenses->count(),
            ];
        }

        return $summary;
    }

    /**
     * Get expenses for date range
     */
    public function getExpensesForDateRange(
        string $startDate,
        string $endDate,
        ?int $routeId = null
    ): Collection {
        $query = Expense::with(['trip.route', 'incurredByUser'])
            ->forDateRange($startDate, $endDate);

        if ($routeId) {
            $query->whereHas('trip', function ($q) use ($routeId) {
                $q->where('route_id', $routeId);
            });
        }

        return $query->orderBy('incurred_date', 'desc')->get();
    }

    /**
     * Get expense statistics
     */
    public function getExpenseStatistics(
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $routeId = null
    ): array {
        $query = Expense::query();

        if ($startDate && $endDate) {
            $query->forDateRange($startDate, $endDate);
        }

        if ($routeId) {
            $query->whereHas('trip', function ($q) use ($routeId) {
                $q->where('route_id', $routeId);
            });
        }

        $expenses = $query->get();

        $statistics = [
            'total_expenses' => $expenses->sum('amount'),
            'average_expense' => $expenses->avg('amount'),
            'expense_count' => $expenses->count(),
            'by_type' => [],
        ];

        foreach (ExpenseTypeEnum::cases() as $type) {
            $typeExpenses = $expenses->where('type', $type);
            $statistics['by_type'][$type->value] = [
                'type' => $type->value,
                'label' => $type->label(),
                'total' => $typeExpenses->sum('amount'),
                'count' => $typeExpenses->count(),
                'average' => $typeExpenses->avg('amount') ?? 0,
            ];
        }

        return $statistics;
    }

    /**
     * Get expenses by user
     */
    public function getExpensesByUser(int $userId): Collection
    {
        return Expense::with(['trip.route', 'incurredByUser'])
            ->byUser($userId)
            ->orderBy('incurred_date', 'desc')
            ->get();
    }

    /**
     * Validate expense data
     */
    public function validateExpense(array $data): bool
    {
        $trip = Trip::findOrFail($data['trip_id']);

        if (! $trip->canAddExpenses()) {
            throw new \Exception('Cannot add expenses to this trip.');
        }

        $type = ExpenseTypeEnum::from($data['type']);

        if ($type->requiresReceipt() && empty($data['receipt_number'])) {
            throw new \Exception("Receipt number is required for {$type->label()} expenses.");
        }

        if ($data['amount'] <= 0) {
            throw new \Exception('Expense amount must be greater than zero.');
        }

        return true;
    }

    /**
     * Bulk import expenses
     */
    public function bulkImportExpenses(array $expenses): array
    {
        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($expenses as $index => $expenseData) {
            try {
                $this->validateExpense($expenseData);
                $this->addExpense($expenseData);
                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'data' => $expenseData,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}

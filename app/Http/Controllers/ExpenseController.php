<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    /**
     * Add a new expense
     */
    public function store(CreateExpenseRequest $request): JsonResponse
    {
        try {
            $expense = $this->expenseService->addExpense($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Expense added successfully',
                'data' => $expense,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update an expense
     */
    public function update(int $id, UpdateExpenseRequest $request): JsonResponse
    {
        try {
            $expense = $this->expenseService->updateExpense($id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Expense updated successfully',
                'data' => $expense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an expense
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->expenseService->deleteExpense($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Expense deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get trip expenses summary
     */
    public function tripSummary(int $tripId): JsonResponse
    {
        try {
            $summary = $this->expenseService->getTripExpensesSummary($tripId);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip expenses summary retrieved successfully',
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get expenses for date range
     */
    public function dateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'route_id' => 'nullable|integer|exists:routes,id',
        ]);

        try {
            $expenses = $this->expenseService->getExpensesForDateRange(
                $request->start_date,
                $request->end_date,
                $request->route_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Expenses retrieved successfully',
                'data' => $expenses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get expense statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'route_id' => 'nullable|integer|exists:routes,id',
        ]);

        try {
            $statistics = $this->expenseService->getExpenseStatistics(
                $request->start_date,
                $request->end_date,
                $request->route_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Expense statistics retrieved successfully',
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user expenses
     */
    public function userExpenses(int $userId): JsonResponse
    {
        try {
            $expenses = $this->expenseService->getExpensesByUser($userId);

            return response()->json([
                'status' => 'success',
                'message' => 'User expenses retrieved successfully',
                'data' => $expenses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

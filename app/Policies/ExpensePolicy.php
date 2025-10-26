<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Determine if the user can view any expenses.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Admin', 'Super Admin', 'Employee']);
    }

    /**
     * Determine if the user can view the expense.
     */
    public function view(User $user, Expense $expense): bool
    {
        // User can view expenses they incurred
        if ($expense->incurred_by === $user->id) {
            return true;
        }

        // Admin and Super Admin can view all expenses
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can create expenses.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Admin', 'Super Admin', 'Employee']);
    }

    /**
     * Determine if the user can update the expense.
     */
    public function update(User $user, Expense $expense): bool
    {
        // Cannot update expenses for completed trips
        if ($expense->trip->status->value === 'completed') {
            return false;
        }

        // User can update expenses they incurred
        if ($expense->incurred_by === $user->id) {
            return true;
        }

        // Admin and Super Admin can update all expenses
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can delete the expense.
     */
    public function delete(User $user, Expense $expense): bool
    {
        // Cannot delete expenses for completed trips
        if ($expense->trip->status->value === 'completed') {
            return false;
        }

        // Admin and Super Admin can delete expenses
        return $user->hasRole(['Admin', 'Super Admin']);
    }

    /**
     * Determine if the user can view expense statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->hasRole(['Admin', 'Super Admin']);
    }
}

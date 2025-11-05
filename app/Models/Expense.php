<?php

namespace App\Models;

use App\Enums\ExpenseTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'user_id',
        'expense_type',
        'amount',
        'from_terminal_id',
        'to_terminal_id',
        'description',
        'expense_date',
    ];

    protected function casts(): array
    {
        return [
            'expense_type' => ExpenseTypeEnum::class,
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    // =============================
    // Relationships
    // =============================
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'from_terminal_id');
    }

    public function toTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'to_terminal_id');
    }

    // =============================
    // Accessors
    // =============================
    public function getFormattedAmountAttribute(): string
    {
        return 'PKR '.number_format($this->amount, 2);
    }
}

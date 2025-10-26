<?php

namespace App\Models;

use App\Enums\ExpenseTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'type',
        'amount',
        'currency',
        'description',
        'incurred_by',
        'incurred_date',
        'receipt_number',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExpenseTypeEnum::class,
            'amount' => 'decimal:2',
            'incurred_date' => 'date',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function incurredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incurred_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForTrip($query, int $tripId)
    {
        return $query->where('trip_id', $tripId);
    }

    public function scopeByType($query, ExpenseTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('incurred_by', $userId);
    }

    public function scopeForDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('incurred_date', [$startDate, $endDate]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function requiresReceipt(): bool
    {
        return $this->type->requiresReceipt();
    }

    public function hasReceipt(): bool
    {
        return ! empty($this->receipt_number);
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2).' '.$this->currency;
    }
}

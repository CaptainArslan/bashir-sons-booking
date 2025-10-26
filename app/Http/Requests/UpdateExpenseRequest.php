<?php

namespace App\Http\Requests;

use App\Enums\ExpenseTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::enum(ExpenseTypeEnum::class)],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'currency' => ['sometimes', 'string', 'max:3'],
            'description' => ['sometimes', 'string', 'max:1000'],
            'incurred_by' => ['sometimes', 'integer', 'exists:users,id'],
            'incurred_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'receipt_number' => ['sometimes', 'string', 'max:100'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'Expense amount must be greater than zero.',
            'incurred_date.before_or_equal' => 'Incurred date cannot be in the future.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'incurred_by' => 'person incurring expense',
            'incurred_date' => 'date incurred',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\ExpenseTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateExpenseRequest extends FormRequest
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
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'type' => ['required', 'string', Rule::enum(ExpenseTypeEnum::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'incurred_by' => ['nullable', 'integer', 'exists:users,id'],
            'incurred_date' => ['nullable', 'date', 'before_or_equal:today'],
            'receipt_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(function () {
                    $type = ExpenseTypeEnum::tryFrom($this->input('type'));

                    return $type && $type->requiresReceipt();
                }),
            ],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'trip_id.required' => 'Trip selection is required.',
            'trip_id.exists' => 'The selected trip does not exist.',
            'type.required' => 'Expense type is required.',
            'amount.required' => 'Expense amount is required.',
            'amount.min' => 'Expense amount must be greater than zero.',
            'incurred_date.before_or_equal' => 'Incurred date cannot be in the future.',
            'receipt_number.required_if' => 'Receipt number is required for this expense type.',
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

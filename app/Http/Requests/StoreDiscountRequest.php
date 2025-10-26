<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create discounts');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'route_id' => ['required', 'exists:routes,id'],
            'discount_type' => ['required', 'string', Rule::in(['fixed', 'percentage'])],
            'value' => ['required', 'numeric', 'min:0'],
            'is_android' => ['boolean'],
            'is_ios' => ['boolean'],
            'is_web' => ['boolean'],
            'is_counter' => ['boolean'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The discount title is required.',
            'title.max' => 'The discount title may not be greater than 255 characters.',
            'route_id.required' => 'Please select a route.',
            'route_id.exists' => 'The selected route is invalid.',
            'discount_type.required' => 'Please select a discount type.',
            'discount_type.in' => 'The selected discount type is invalid.',
            'value.required' => 'The discount value is required.',
            'value.numeric' => 'The discount value must be a number.',
            'value.min' => 'The discount value must be at least 0.',
            'starts_at.required' => 'The start date is required.',
            'starts_at.date' => 'The start date must be a valid date.',
            'ends_at.required' => 'The end date is required.',
            'ends_at.date' => 'The end date must be a valid date.',
            'ends_at.after' => 'The end date must be after the start date.',
            'start_time.date_format' => 'The start time must be in HH:MM format.',
            'end_time.date_format' => 'The end time must be in HH:MM format.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_android' => $this->boolean('is_android'),
            'is_ios' => $this->boolean('is_ios'),
            'is_web' => $this->boolean('is_web'),
            'is_counter' => $this->boolean('is_counter'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

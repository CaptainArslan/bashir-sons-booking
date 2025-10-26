<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchTripsRequest extends FormRequest
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
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'from_stop_id' => ['required', 'integer', 'exists:route_stops,id'],
            'to_stop_id' => ['required', 'integer', 'exists:route_stops,id', 'different:from_stop_id'],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'route_id.required' => 'Route selection is required.',
            'route_id.exists' => 'The selected route does not exist.',
            'date.required' => 'Travel date is required.',
            'date.after_or_equal' => 'Travel date must be today or a future date.',
            'from_stop_id.required' => 'Boarding point is required.',
            'from_stop_id.exists' => 'The selected boarding point is invalid.',
            'to_stop_id.required' => 'Destination point is required.',
            'to_stop_id.exists' => 'The selected destination point is invalid.',
            'to_stop_id.different' => 'Destination must be different from boarding point.',
            'passengers.min' => 'At least one passenger is required.',
            'passengers.max' => 'Maximum 50 passengers allowed per search.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'from_stop_id' => 'boarding point',
            'to_stop_id' => 'destination point',
        ];
    }
}

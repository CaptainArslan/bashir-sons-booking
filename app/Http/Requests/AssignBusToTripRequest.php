<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignBusToTripRequest extends FormRequest
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
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bus_id.required' => 'Bus selection is required.',
            'bus_id.exists' => 'The selected bus does not exist.',
        ];
    }
}

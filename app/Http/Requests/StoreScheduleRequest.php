<?php

namespace App\Http\Requests;

use App\Enums\FrequencyTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create schedules');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'route_id' => 'required|exists:routes,id',
            'trip_code' => 'required|string|max:255|unique:schedules,trip_code',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'nullable|date_format:H:i|after:departure_time',
            'frequency' => ['required', Rule::enum(FrequencyTypeEnum::class)],
            'operating_days' => 'nullable|array',
            'operating_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'route_id.required' => 'Please select a route.',
            'route_id.exists' => 'The selected route does not exist.',
            'trip_code.required' => 'Trip code is required.',
            'trip_code.unique' => 'This trip code is already in use.',
            'departure_time.required' => 'Departure time is required.',
            'departure_time.date_format' => 'Departure time must be in HH:MM format.',
            'arrival_time.date_format' => 'Arrival time must be in HH:MM format.',
            'arrival_time.after' => 'Arrival time must be after departure time.',
            'frequency.required' => 'Frequency is required.',
            'operating_days.array' => 'Operating days must be an array.',
            'operating_days.*.in' => 'Invalid operating day selected.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure operating_days is only set when frequency is custom
        if ($this->frequency !== FrequencyTypeEnum::CUSTOM->value) {
            $this->merge(['operating_days' => null]);
        }
    }
}

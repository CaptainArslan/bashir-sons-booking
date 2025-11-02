<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimetableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit timetables');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'stops' => 'required|array|min:1',
            'stops.*.id' => 'required|exists:timetable_stops,id',
            'stops.*.arrival_time' => 'nullable|date_format:H:i',
            'stops.*.departure_time' => 'nullable|date_format:H:i',
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
            'name.string' => 'Timetable name must be a valid text.',
            'name.max' => 'Timetable name cannot exceed 255 characters.',
            'is_active.boolean' => 'Active status must be true or false.',
            'stops.required' => 'At least one stop is required.',
            'stops.array' => 'Stops must be provided as an array.',
            'stops.min' => 'At least one stop is required.',
            'stops.*.id.required' => 'Stop ID is required.',
            'stops.*.id.exists' => 'Stop does not exist.',
            'stops.*.arrival_time.date_format' => 'Please enter a valid arrival time format (HH:MM).',
            'stops.*.departure_time.date_format' => 'Please enter a valid departure time format (HH:MM).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'timetable name',
            'start_departure_time' => 'start departure time',
            'end_arrival_time' => 'end arrival time',
            'is_active' => 'active status',
            'stops' => 'stops',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation logic
            $stops = $this->input('stops', []);

            if (count($stops) > 0) {
                // First stop: must have departure time
                $firstStop = $stops[0];
                if (empty($firstStop['departure_time'])) {
                    $validator->errors()->add('stops.0.departure_time', 'First stop must have a departure time.');
                }

                // Last stop: should have arrival time (if provided)
                $lastIndex = count($stops) - 1;
                $lastStop = $stops[$lastIndex];
                // Note: We don't force last stop to have arrival time as it might be optional
            }
        });
    }
}

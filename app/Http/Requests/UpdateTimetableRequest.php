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
            'start_departure_time' => 'required|date_format:H:i:s',
            'end_arrival_time' => 'nullable|date_format:H:i:s',
            'is_active' => 'boolean',
            'stops' => 'required|array|min:1',
            'stops.*.id' => 'required|exists:timetable_stops,id',
            'stops.*.arrival_time' => 'nullable|date_format:H:i:s',
            'stops.*.departure_time' => 'nullable|date_format:H:i:s',
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
            'start_departure_time.required' => 'Start departure time is required.',
            'start_departure_time.date_format' => 'Please enter a valid time format (HH:MM:SS).',
            'end_arrival_time.date_format' => 'Please enter a valid time format (HH:MM:SS).',
            'is_active.boolean' => 'Active status must be true or false.',
            'stops.required' => 'At least one stop is required.',
            'stops.array' => 'Stops must be provided as an array.',
            'stops.min' => 'At least one stop is required.',
            'stops.*.id.required' => 'Stop ID is required.',
            'stops.*.id.exists' => 'Stop does not exist.',
            'stops.*.arrival_time.date_format' => 'Please enter a valid arrival time format (HH:MM:SS).',
            'stops.*.departure_time.date_format' => 'Please enter a valid departure time format (HH:MM:SS).',
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
                // First stop: must have departure time, should NOT have arrival time
                $firstStop = $stops[0];
                if (empty($firstStop['departure_time'])) {
                    $validator->errors()->add('stops.0.departure_time', 'First stop must have a departure time.');
                }
                // First stop arrival_time should be empty (it's the starting point)
                if (! empty($firstStop['arrival_time'])) {
                    $validator->errors()->add('stops.0.arrival_time', 'First stop should not have an arrival time.');
                }

                // Last stop: must have arrival time, should NOT have departure time
                $lastIndex = count($stops) - 1;
                $lastStop = $stops[$lastIndex];
                if (empty($lastStop['arrival_time'])) {
                    $validator->errors()->add('stops.'.$lastIndex.'.arrival_time', 'Last stop must have an arrival time.');
                }
                // Last stop departure_time should be empty (it's the destination)
                if (! empty($lastStop['departure_time'])) {
                    $validator->errors()->add('stops.'.$lastIndex.'.departure_time', 'Last stop should not have a departure time.');
                }

                // Check if middle stops have at least one time
                for ($i = 1; $i < $lastIndex; $i++) {
                    $stop = $stops[$i];
                    if (empty($stop['arrival_time']) && empty($stop['departure_time'])) {
                        $validator->errors()->add('stops.'.$i.'.arrival_time', 'Middle stops must have either arrival or departure time.');
                    }
                }
            }
        });
    }
}

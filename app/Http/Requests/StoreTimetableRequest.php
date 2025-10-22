<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimetableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create timetables');
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
            'departure_count' => 'required|integer|min:1|max:10',
            'timetables' => 'required|array|min:1',
            'timetables.*.stops' => 'required|array|min:1',
            'timetables.*.stops.*.stop_id' => 'required|exists:terminals,id',
            'timetables.*.stops.*.sequence' => 'required|integer|min:1',
            'timetables.*.stops.*.arrival_time' => 'nullable|date_format:H:i',
            'timetables.*.stops.*.departure_time' => 'nullable|date_format:H:i',
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
            'departure_count.required' => 'Please enter the number of departures.',
            'departure_count.integer' => 'Number of departures must be a whole number.',
            'departure_count.min' => 'Number of departures must be at least 1.',
            'departure_count.max' => 'Number of departures cannot exceed 50.',
            'start_time.required' => 'Please enter the start time.',
            'start_time.date_format' => 'Please enter a valid time format (HH:MM).',
            'time_interval.required' => 'Please select a time interval.',
            'time_interval.integer' => 'Time interval must be a whole number.',
            'time_interval.min' => 'Time interval must be at least 15 minutes.',
            'time_interval.max' => 'Time interval cannot exceed 8 hours.',
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
            'route_id' => 'route',
            'departure_count' => 'number of departures',
            'start_time' => 'start time',
            'time_interval' => 'time interval',
        ];
    }
}

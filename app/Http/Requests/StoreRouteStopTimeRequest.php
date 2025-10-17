<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreRouteStopTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create route stop times');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stop_times' => 'required|array|min:1',
            'stop_times.*.route_stop_id' => 'required|exists:route_stops,id',
            'stop_times.*.sequence' => 'required|integer|min:1',
            'stop_times.*.arrival_time' => 'nullable|date_format:H:i',
            'stop_times.*.departure_time' => 'nullable|date_format:H:i',
            'stop_times.*.allow_online_booking' => 'boolean',
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
            'stop_times.required' => 'At least one stop time is required.',
            'stop_times.min' => 'At least one stop time is required.',
            'stop_times.*.route_stop_id.required' => 'Route stop is required.',
            'stop_times.*.route_stop_id.exists' => 'The selected route stop does not exist.',
            'stop_times.*.sequence.required' => 'Sequence is required.',
            'stop_times.*.sequence.integer' => 'Sequence must be a number.',
            'stop_times.*.sequence.min' => 'Sequence must be at least 1.',
            'stop_times.*.arrival_time.date_format' => 'Arrival time must be in HH:MM format.',
            'stop_times.*.departure_time.date_format' => 'Departure time must be in HH:MM format.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $stopTimes = $this->input('stop_times', []);

            if (empty($stopTimes)) {
                return;
            }

            // Validate that all route_stop_ids belong to the same route as the timetable
            $routeTimetable = $this->route('routeTimetable');
            if ($routeTimetable) {
                $validRouteStopIds = $routeTimetable->route->routeStops->pluck('id');
                $providedRouteStopIds = collect($stopTimes)->pluck('route_stop_id');

                if (!$providedRouteStopIds->every(fn($id) => $validRouteStopIds->contains($id))) {
                    $validator->errors()->add('stop_times', 'Invalid route stops provided.');
                }
            }

            // Validate sequence order
            $sequences = collect($stopTimes)->pluck('sequence')->sort();
            $expectedSequences = range(1, count($stopTimes));

            if ($sequences->values()->toArray() !== $expectedSequences) {
                $validator->errors()->add('stop_times', 'Stop sequences must be consecutive starting from 1.');
            }

            // Validate time sequence
            $this->validateTimeSequence($validator, $stopTimes);
        });
    }

    /**
     * Validate that times are in proper sequence.
     */
    private function validateTimeSequence($validator, array $stopTimes)
    {
        $sortedStopTimes = collect($stopTimes)->sortBy('sequence')->values();

        for ($i = 0; $i < $sortedStopTimes->count() - 1; $i++) {
            $current = $sortedStopTimes[$i];
            $next = $sortedStopTimes[$i + 1];

            // Skip if times are not set or don't exist
            if (empty($current['departure_time']) || empty($next['arrival_time'])) {
                continue;
            }

            try {
                $currentTime = Carbon::parse($current['departure_time']);
                $nextTime = Carbon::parse($next['arrival_time']);

                if ($currentTime->gte($nextTime)) {
                    $validator->errors()->add('stop_times', 'Stop times must be in chronological order.');
                    break;
                }
            } catch (\Exception $e) {
                // Skip invalid time formats
                continue;
            }
        }
    }
}

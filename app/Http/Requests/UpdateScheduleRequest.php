<?php

namespace App\Http\Requests;

use App\Enums\FrequencyTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit schedules');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $scheduleId = $this->route('schedule')->id ?? null;

        return [
            'route_id' => 'required|exists:routes,id',
            'code' => 'required|string|max:255|unique:schedules,code,' . $scheduleId,
            'frequency' => ['required', Rule::enum(FrequencyTypeEnum::class)],
            'operating_days' => 'required_if:frequency,' . FrequencyTypeEnum::CUSTOM->value . '|array',
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
            'code.required' => 'Code is required.',
            'code.unique' => 'This code is already in use.',
            'frequency.required' => 'Frequency is required.',
            'operating_days.required_if' => 'Operating days are required when frequency is custom.',
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

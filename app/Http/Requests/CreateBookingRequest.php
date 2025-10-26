<?php

namespace App\Http\Requests;

use App\Enums\BookingTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
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
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'timetable_id' => ['nullable', 'integer', 'exists:timetables,id'],
            'from_stop_id' => ['required', 'integer', 'exists:route_stops,id'],
            'to_stop_id' => ['required', 'integer', 'exists:route_stops,id', 'different:from_stop_id'],
            'type' => ['required', 'string', Rule::enum(BookingTypeEnum::class)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
            'seats' => ['required', 'array', 'min:1'],
            'seats.*.seat_number' => ['required', 'string', 'max:10'],
            'seats.*.seat_row' => ['required', 'string', 'max:5'],
            'seats.*.seat_column' => ['required', 'string', 'max:5'],
            'seats.*.passenger_name' => ['required', 'string', 'max:255'],
            'seats.*.passenger_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'seats.*.passenger_gender' => ['nullable', 'string', 'in:male,female,other'],
            'seats.*.passenger_cnic' => ['nullable', 'string', 'max:20'],
            'seats.*.passenger_phone' => ['nullable', 'string', 'max:20'],
            'seats.*.notes' => ['nullable', 'string', 'max:500'],
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
            'departure_date.required' => 'Departure date is required.',
            'departure_date.after_or_equal' => 'Departure date must be today or a future date.',
            'from_stop_id.required' => 'Boarding point is required.',
            'from_stop_id.exists' => 'The selected boarding point is invalid.',
            'to_stop_id.required' => 'Destination point is required.',
            'to_stop_id.exists' => 'The selected destination point is invalid.',
            'to_stop_id.different' => 'Destination must be different from boarding point.',
            'type.required' => 'Booking type is required.',
            'seats.required' => 'At least one seat must be selected.',
            'seats.min' => 'At least one seat must be selected.',
            'seats.*.seat_number.required' => 'Seat number is required for all seats.',
            'seats.*.passenger_name.required' => 'Passenger name is required for all seats.',
            'seats.*.passenger_age.min' => 'Passenger age must be 0 or greater.',
            'seats.*.passenger_age.max' => 'Passenger age must be 120 or less.',
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
            'contact_phone' => 'contact phone number',
            'contact_email' => 'contact email address',
            'contact_name' => 'contact name',
        ];
    }
}

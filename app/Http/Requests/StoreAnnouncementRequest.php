<?php

namespace App\Http\Requests;

use App\Enums\AnnouncementAudienceTypeEnum;
use App\Enums\AnnouncementDisplayTypeEnum;
use App\Enums\AnnouncementPriorityEnum;
use App\Enums\AnnouncementStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'string', Rule::in(AnnouncementStatusEnum::getStatuses())],
            'display_type' => ['required', 'string', Rule::in(AnnouncementDisplayTypeEnum::getDisplayTypes())],
            'priority' => ['required', 'string', Rule::in(AnnouncementPriorityEnum::getPriorities())],
            'audience_type' => ['required', 'string', Rule::in(AnnouncementAudienceTypeEnum::getAudienceTypes())],
            'audience_payload' => ['nullable', 'array'],
            'audience_users' => ['nullable', 'array'],
            'audience_users.*' => ['exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_pinned' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.required' => 'The description field is required.',
            'image.image' => 'The image must be a valid image file.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
            'link.url' => 'The link must be a valid URL.',
            'status.required' => 'The status field is required.',
            'display_type.required' => 'The display type field is required.',
            'priority.required' => 'The priority field is required.',
            'audience_type.required' => 'The audience type field is required.',
            'start_date.required' => 'The start date field is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.required' => 'The end date field is required.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after' => 'The end date must be after the start date.',
            'audience_users.*.exists' => 'One or more selected users do not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_pinned' => $this->boolean('is_pinned'),
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

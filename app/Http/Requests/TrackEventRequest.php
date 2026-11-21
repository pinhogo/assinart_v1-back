<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:pageview,click,funnel'],
            'event_name' => ['required_if:type,funnel', 'nullable', 'string', 'in:home_pageview,home_waitlist_vitrine_submitted,editor_session,editor_vitrine_cta_clicked'],
            'source' => ['nullable', 'string', 'max:50'],
            'metadata' => ['nullable', 'array', 'max:20'],
            'username' => ['nullable', 'string', 'max:50'],
            'link_id' => ['nullable', 'integer', 'min:1'],
            'visitor_id' => ['nullable', 'string', 'max:64'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'user_agent' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

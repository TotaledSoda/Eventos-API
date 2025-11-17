<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class EventUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'category'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_path'  => ['nullable', 'string', 'max:255'],
            'starts_at'   => ['nullable', 'date'],

            'venue_name'  => ['nullable', 'string', 'max:255'],
            'address'     => ['nullable', 'string', 'max:255'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
        ];
    }
}

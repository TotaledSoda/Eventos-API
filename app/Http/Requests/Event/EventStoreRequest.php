<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class EventStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la controla el middleware de permisos
        return true;
    }

    public function rules(): array
    {
        return [
            'organizer_id' => ['required', 'exists:organizers,id'],
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'image_path'   => ['nullable', 'string', 'max:255'],
            'starts_at'    => ['required', 'date'],

            'venue_name'   => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric'],
            'longitude'    => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'organizer_id.required' => 'El organizador es obligatorio.',
            'organizer_id.exists'   => 'El organizador seleccionado no existe.',
            'title.required'        => 'El título del evento es obligatorio.',
            'starts_at.required'    => 'La fecha y hora del evento son obligatorias.',
        ];
    }
}

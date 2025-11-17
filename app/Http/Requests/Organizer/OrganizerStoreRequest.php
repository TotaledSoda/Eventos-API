<?php

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class OrganizerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización se controla por middleware de permisos
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'     => ['required', 'exists:users,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'website'     => ['nullable', 'string', 'max:255'],
            'logo_path'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists'   => 'El usuario seleccionado no existe.',
            'name.required'    => 'El nombre del organizador es obligatorio.',
        ];
    }
}

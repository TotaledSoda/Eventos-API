<?php

namespace App\Http\Requests\TicketType;

use Illuminate\Foundation\Http\FormRequest;

class TicketTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'stock'       => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }
}

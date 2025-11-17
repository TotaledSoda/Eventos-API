<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // se controla por auth:sanctum
    }

    public function rules(): array
    {
        return [
            'items'                           => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id'          => ['required', 'integer', 'exists:ticket_types,id'],
            'items.*.quantity'                => ['required', 'integer', 'min:1'],
            'currency'                        => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                  => 'Debes enviar al menos un tipo de boleto.',
            'items.*.ticket_type_id.required' => 'Cada item debe tener un tipo de boleto.',
            'items.*.ticket_type_id.exists'   => 'Algún tipo de boleto no existe.',
            'items.*.quantity.required'       => 'Cada item debe tener cantidad.',
            'items.*.quantity.min'            => 'La cantidad mínima es 1.',
        ];
    }
}

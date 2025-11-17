<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketType\TicketTypeStoreRequest;
use App\Http\Requests\TicketType\TicketTypeUpdateRequest;
use App\Models\Event;
use App\Models\TicketType;

class TicketTypeController extends Controller
{
    /**
     * Listar tipos de boletos de un evento.
     * Permiso: events.edit (gestión del evento)
     */
    public function index(Event $event)
    {
        $ticketTypes = $event->ticketTypes()->orderBy('price')->get();

        return response()->json([
            'data' => $ticketTypes,
        ]);
    }

    /**
     * Crear tipo de boleto para un evento.
     * Permiso: events.edit
     */
    public function store(TicketTypeStoreRequest $request, Event $event)
    {
        $data = $request->validated();
        $data['event_id'] = $event->id;

        $ticketType = TicketType::create($data);

        return response()->json([
            'message' => 'Tipo de boleto creado correctamente.',
            'data'    => $ticketType,
        ], 201);
    }

    /**
     * Detalle de un tipo de boleto.
     * Permiso: events.edit
     */
    public function show(Event $event, TicketType $ticketType)
    {
        // Nos aseguramos que pertenezca al evento
        if ($ticketType->event_id !== $event->id) {
            return response()->json([
                'message' => 'Este tipo de boleto no pertenece a este evento.',
            ], 404);
        }

        return response()->json([
            'data' => $ticketType,
        ]);
    }

    /**
     * Actualizar tipo de boleto.
     * Permiso: events.edit
     */
    public function update(TicketTypeUpdateRequest $request, Event $event, TicketType $ticketType)
    {
        if ($ticketType->event_id !== $event->id) {
            return response()->json([
                'message' => 'Este tipo de boleto no pertenece a este evento.',
            ], 404);
        }

        $data = $request->validated();

        $ticketType->update($data);

        return response()->json([
            'message' => 'Tipo de boleto actualizado correctamente.',
            'data'    => $ticketType,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\EventStoreRequest;
use App\Http\Requests\Event\EventUpdateRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    /**
     * Listado de eventos (admin/organizador).
     * Permiso: events.view_all
     */
    public function index(Request $request)
    {
        $query = Event::with(['organizer', 'ticketTypes'])
            ->orderByDesc('starts_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->get('organizer_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('title', 'LIKE', "%{$search}%");
        }

        $events = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $events,
        ]);
    }

    /**
     * Crear evento.
     * Permiso: events.create
     */
    public function store(EventStoreRequest $request)
    {
        $data = $request->validated();

        $event = Event::create($data);

        return response()->json([
            'message' => 'Evento creado correctamente.',
            'data'    => $event->load('organizer'),
        ], 201);
    }

    /**
     * Detalle evento.
     * Permiso: events.view_all
     */
    public function show(Event $event)
    {
        return response()->json([
            'data' => $event->load(['organizer', 'ticketTypes']),
        ]);
    }

    /**
     * Actualizar evento.
     * Permiso: events.edit
     */
    public function update(EventUpdateRequest $request, Event $event)
    {
        $data = $request->validated();

        $event->update($data);

        return response()->json([
            'message' => 'Evento actualizado correctamente.',
            'data'    => $event->load(['organizer', 'ticketTypes']),
        ]);
    }

    /**
     * Publicar evento.
     * Permiso: events.publish
     */
    public function publish(Event $event)
    {
        if ($event->status === 'published') {
            return response()->json([
                'message' => 'El evento ya está publicado.',
                'data'    => $event,
            ]);
        }

        $event->update([
            'status'       => 'published',
            'published_at' => Carbon::now(),
            'canceled_at'  => null,
        ]);

        return response()->json([
            'message' => 'Evento publicado correctamente.',
            'data'    => $event,
        ]);
    }

    /**
     * Cancelar evento.
     * Permiso: events.cancel
     */
    public function cancel(Event $event)
    {
        if ($event->status === 'canceled') {
            return response()->json([
                'message' => 'El evento ya está cancelado.',
                'data'    => $event,
            ]);
        }

        $event->update([
            'status'      => 'canceled',
            'canceled_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Evento cancelado correctamente.',
            'data'    => $event,
        ]);
    }

    /**
     * Destacar evento.
     * Permiso: events.feature
     */
    public function feature(Event $event)
    {
        if ($event->is_featured) {
            return response()->json([
                'message' => 'El evento ya está destacado.',
                'data'    => $event,
            ]);
        }

        $event->update(['is_featured' => true]);

        return response()->json([
            'message' => 'Evento destacado correctamente.',
            'data'    => $event,
        ]);
    }

    /**
     * Quitar destacado.
     * Permiso: events.feature
     */
    public function unfeature(Event $event)
    {
        if (!$event->is_featured) {
            return response()->json([
                'message' => 'El evento no está destacado.',
                'data'    => $event,
            ]);
        }

        $event->update(['is_featured' => false]);

        return response()->json([
            'message' => 'Evento ya no está destacado.',
            'data'    => $event,
        ]);
    }
}

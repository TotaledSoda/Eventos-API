<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketScan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class TicketValidationController extends Controller
{
    /**
     * Validar un ticket vía código de QR.
     * Endpoint: POST /api/v1/tickets/validate
     * Body: { "code": "uuid|firma" }
     *
     * Requiere: auth:sanctum + permiso adecuado (ej: tickets.view)
     */
    public function validateCode(Request $request)
    {
        $user = $request->user();

        if (!$user->can('tickets.view')) {
            return response()->json([
                'message' => 'No tienes permiso para validar tickets.',
            ], 403);
        }

        $validated = $request->validate([
            'code'      => ['required', 'string'],
            'latitude'  => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $code = $validated['code'];

        // Formato esperado: uuid|firma
        $parts = explode('|', $code);

        if (count($parts) !== 2) {
            return $this->invalidResponse(null, 'Formato de código inválido.', $user, $validated);
        }

        [$uuid, $signature] = $parts;

        $expectedSignature = hash_hmac('sha256', $uuid, Config::get('app.key'));

        if (!hash_equals($expectedSignature, $signature)) {
            return $this->invalidResponse(null, 'Firma inválida.', $user, $validated);
        }

        /** @var Ticket|null $ticket */
        $ticket = Ticket::with(['event', 'ticketType', 'user'])->where('uuid', $uuid)->first();

        if (!$ticket) {
            return $this->invalidResponse(null, 'Ticket no encontrado.', $user, $validated);
        }

        // Lógica de validación de estado
        $result = 'valid';
        $message = 'Ticket válido.';

        if ($ticket->status === Ticket::STATUS_INVALIDATED) {
            $result  = 'invalidated';
            $message = 'Este ticket ha sido invalidado.';
        } elseif ($ticket->status === Ticket::STATUS_USED) {
            $result  = 'duplicate';
            $message = 'Este ticket ya fue usado.';
        }

        // Si es válido y está en estado "valid", lo marcamos como usado
        if ($result === 'valid') {
            $ticket->update([
                'status'        => Ticket::STATUS_USED,
                'checked_in_at' => Carbon::now(),
            ]);
        }

        // Registrar el scan
        TicketScan::create([
            'ticket_id'   => $ticket->id,
            'scanned_by'  => $user->id,
            'scanned_at'  => Carbon::now(),
            'result'      => $result,
            'device_info' => $request->userAgent(),
            'latitude'    => $validated['latitude'] ?? null,
            'longitude'   => $validated['longitude'] ?? null,
        ]);

        return response()->json([
            'status'  => $result,   // valid | duplicate | invalidated | invalid
            'message' => $message,
            'data'    => [
                'ticket' => [
                    'id'           => $ticket->id,
                    'uuid'         => $ticket->uuid,
                    'status'       => $ticket->status,
                    'checked_in_at'=> $ticket->checked_in_at,
                ],
                'event'  => $ticket->event ? [
                    'id'        => $ticket->event->id,
                    'title'     => $ticket->event->title,
                    'starts_at' => $ticket->event->starts_at,
                    'venue_name'=> $ticket->event->venue_name,
                ] : null,
                'ticket_type' => $ticket->ticketType ? [
                    'id'    => $ticket->ticketType->id,
                    'name'  => $ticket->ticketType->name,
                    'price' => $ticket->ticketType->price,
                ] : null,
                'user' => [
                    'id'    => $ticket->user->id,
                    'name'  => $ticket->user->name,
                    'email' => $ticket->user->email,
                ],
            ],
        ]);
    }

    protected function invalidResponse(?Ticket $ticket, string $message, $user, array $validated)
    {
        // Registramos intento inválido sin asociar ticket si no existe
        TicketScan::create([
            'ticket_id'   => $ticket?->id,
            'scanned_by'  => $user->id ?? null,
            'scanned_at'  => Carbon::now(),
            'result'      => 'invalid',
            'device_info' => request()->userAgent(),
            'latitude'    => $validated['latitude'] ?? null,
            'longitude'   => $validated['longitude'] ?? null,
        ]);

        return response()->json([
            'status'  => 'invalid',
            'message' => $message,
        ], 422);
    }
}
    
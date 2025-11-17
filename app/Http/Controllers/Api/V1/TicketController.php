<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TicketController extends Controller
{
    /**
     * Tickets del usuario autenticado (wallet).
     */
    public function myTickets(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::with(['event', 'ticketType'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Adjuntamos el payload del QR
        $tickets->each(function (Ticket $ticket) {
            $ticket->qr_code_payload = $ticket->getQrCodePayload();
        });

        return response()->json([
            'data' => $tickets,
        ]);
    }

    /**
     * Ver detalle de un ticket (para admin o dueño).
     */
    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($user->id !== $ticket->user_id && !$user->can('tickets.view')) {
            return response()->json([
                'message' => 'No tienes permiso para ver este ticket.',
            ], 403);
        }

        $ticket->load(['event', 'ticketType', 'order', 'scans']);

        $ticket->qr_code_payload = $ticket->getQrCodePayload();

        return response()->json([
            'data' => $ticket,
        ]);
    }

    /**
     * Forzar check-in manual (por admin / staff con permiso).
     * Permiso: tickets.force_checkin
     */
    public function forceCheckIn(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if (!$user->can('tickets.force_checkin')) {
            return response()->json([
                'message' => 'No tienes permiso para forzar check-in.',
            ], 403);
        }

        if ($ticket->status === Ticket::STATUS_USED) {
            return response()->json([
                'message' => 'El ticket ya estaba marcado como usado.',
                'data'    => $ticket,
            ], 422);
        }

        $ticket->update([
            'status'        => Ticket::STATUS_USED,
            'checked_in_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Check-in forzado correctamente.',
            'data'    => $ticket,
        ]);
    }
}

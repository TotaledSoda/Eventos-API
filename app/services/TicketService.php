<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Generar tickets para una orden pagada.
     * Un ticket por cada unidad en cada order_item.
     */
    public function generateTicketsForOrder(Order $order): void
    {
        // Solo generamos si la orden está pagada
        if ($order->status !== Order::STATUS_PAID) {
            return;
        }

        // Si ya hay tickets para esta orden, no repetir
        $existingCount = Ticket::where('order_id', $order->id)->count();
        if ($existingCount > 0) {
            return;
        }

        $order->loadMissing(['items.ticketType', 'user']);

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $ticketType = $item->ticketType;

                for ($i = 0; $i < $item->quantity; $i++) {
                    Ticket::create([
                        'order_id'       => $order->id,
                        'order_item_id'  => $item->id,
                        'user_id'        => $order->user_id,
                        'event_id'       => $ticketType->event_id,
                        'ticket_type_id' => $ticketType->id,
                        // uuid se genera automáticamente en el modelo
                        'status'         => Ticket::STATUS_VALID,
                    ]);
                }
            }
        });
    }
}

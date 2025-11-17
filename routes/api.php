<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\TicketTypeController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\TicketValidationController;

Route::prefix('v1')->group(function () {

    // 🟢 RUTAS PÚBLICAS (SIN AUTH)

    // Ping simple
    Route::get('ping', function () {
        return response()->json([
            'ok'   => true,
            'app'  => 'eventos',
            'env'  => config('app.env'),
            'time' => now()->toDateTimeString(),
        ]);
    });

    // Eventos públicos para "Descubrir" en la app
    Route::get('public/events', [EventController::class, 'publicIndex']);
    Route::get('public/events/{event}', [EventController::class, 'publicShow']);

    // 🔒 RUTAS PROTEGIDAS (auth:sanctum)

    Route::middleware(['auth:sanctum'])->group(function () {

        // 🎟️ Eventos (admin / organizador)
        Route::prefix('events')->group(function () {
            Route::get('/', [EventController::class, 'index'])
                ->middleware('permission:events.view_all');

            Route::post('/', [EventController::class, 'store'])
                ->middleware('permission:events.create');

            Route::get('{event}', [EventController::class, 'show'])
                ->middleware('permission:events.view_all');

            Route::put('{event}', [EventController::class, 'update'])
                ->middleware('permission:events.edit');
            Route::patch('{event}', [EventController::class, 'update'])
                ->middleware('permission:events.edit');

            Route::post('{event}/publish', [EventController::class, 'publish'])
                ->middleware('permission:events.publish');

            Route::post('{event}/cancel', [EventController::class, 'cancel'])
                ->middleware('permission:events.cancel');

            Route::post('{event}/feature', [EventController::class, 'feature'])
                ->middleware('permission:events.feature');

            Route::post('{event}/unfeature', [EventController::class, 'unfeature'])
                ->middleware('permission:events.feature');

            // 🎫 Tipos de boletos para un evento
            Route::get('{event}/ticket-types', [TicketTypeController::class, 'index'])
                ->middleware('permission:events.edit');

            Route::post('{event}/ticket-types', [TicketTypeController::class, 'store'])
                ->middleware('permission:events.edit');

            Route::get('{event}/ticket-types/{ticketType}', [TicketTypeController::class, 'show'])
                ->middleware('permission:events.edit');

            Route::put('{event}/ticket-types/{ticketType}', [TicketTypeController::class, 'update'])
                ->middleware('permission:events.edit');
            Route::patch('{event}/ticket-types/{ticketType}', [TicketTypeController::class, 'update'])
                ->middleware('permission:events.edit');
        });

        // 🎟️ Tickets (wallet y admin)
        Route::get('my/tickets', [TicketController::class, 'myTickets']);

        Route::get('tickets/{ticket}', [TicketController::class, 'show']);

        Route::post('tickets/{ticket}/force-checkin', [TicketController::class, 'forceCheckIn'])
            ->middleware('permission:tickets.force_checkin');

        // 📲 Validación vía QR (staff)
        Route::post('tickets/validate', [TicketValidationController::class, 'validateCode'])
            ->middleware('permission:tickets.view');

        // 💳 Órdenes
        Route::post('orders', [OrderController::class, 'store']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);

        Route::post('orders/{order}/refund', [OrderController::class, 'refund'])
            ->middleware('permission:orders.refund');

        Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->middleware('permission:orders.edit_status');
    });

    // 🌐 Webhook PayPal (sin auth)
    Route::post('paypal/webhook', [OrderController::class, 'webhook']);
});

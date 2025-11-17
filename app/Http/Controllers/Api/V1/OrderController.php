<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Services\TicketService;


class OrderController extends Controller
{
    protected PayPalService $payPal;
protected TicketService $ticketService;

public function __construct(PayPalService $payPal, TicketService $ticketService)
{
    $this->payPal         = $payPal;
    $this->ticketService  = $ticketService;
}

    /**
     * Crear una orden y generar orden en PayPal.
     *
     * Cliente: usuario autenticado
     */
    public function store(OrderStoreRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $currency = strtoupper($data['currency'] ?? 'MXN');

        return DB::transaction(function () use ($data, $user, $currency) {
            $itemsData = [];
            $total = 0;

            foreach ($data['items'] as $item) {
                $ticketType = TicketType::findOrFail($item['ticket_type_id']);

                $quantity   = $item['quantity'];
                $unitPrice  = $ticketType->price;
                $lineTotal  = $unitPrice * $quantity;

                // Aquí más adelante podrías validar stock, fechas de venta, etc.
                $itemsData[] = [
                    'ticket_type_id' => $ticketType->id,
                    'quantity'       => $quantity,
                    'unit_price'     => $unitPrice,
                    'total_price'    => $lineTotal,
                ];

                $total += $lineTotal;
            }

            // Creamos la orden local en pending
            $order = Order::create([
                'user_id'      => $user->id,
                'total_amount' => $total,
                'currency'     => $currency,
                'status'       => Order::STATUS_PENDING,
            ]);

            // Items
            foreach ($itemsData as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            // Crear orden en PayPal
            $returnUrl = config('app.url') . '/payments/paypal/return';  // ajustar luego a tu frontend
            $cancelUrl = config('app.url') . '/payments/paypal/cancel';

            $paypalOrder = $this->payPal->createOrder($total, $currency, $returnUrl, $cancelUrl);

            $order->update([
                'paypal_order_id' => $paypalOrder['id'] ?? null,
                'paypal_payload'  => $paypalOrder,
            ]);

            // Link de aprobación
            $approveLink = collect($paypalOrder['links'] ?? [])
                ->firstWhere('rel', 'approve')['href'] ?? null;

            return response()->json([
                'message' => 'Orden creada correctamente.',
                'data'    => [
                    'order'        => $order->load('items.ticketType'),
                    'paypal'       => [
                        'id'           => $paypalOrder['id'] ?? null,
                        'approve_link' => $approveLink,
                    ],
                ],
            ], 201);
        });
    }

    /**
     * Listar órdenes (admin).
     * Permiso: orders.view
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Si es admin/super-admin => todas las órdenes
        if ($user->can('orders.view')) {
            $query = Order::with(['user', 'items.ticketType'])
                ->orderByDesc('created_at');

            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            $orders = $query->paginate($request->get('per_page', 15));
        } else {
            // Usuario normal: solo sus órdenes
            $orders = Order::with(['items.ticketType'])
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate($request->get('per_page', 15));
        }

        return response()->json([
            'data' => $orders,
        ]);
    }

    /**
     * Ver detalle de una orden.
     */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        if ($user->id !== $order->user_id && !$user->can('orders.view')) {
            return response()->json([
                'message' => 'No tienes permiso para ver esta orden.',
            ], 403);
        }

        return response()->json([
            'data' => $order->load(['user', 'items.ticketType']),
        ]);
    }

    /**
     * Webhook de PayPal: aquí actualizamos el estado de la orden.
     *
     * IMPORTANTE:
     * - En producción deberías validar la firma del webhook.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('PayPal webhook recibido', $payload);

        $paypalOrderId = data_get($payload, 'resource.id')
            ?? data_get($payload, 'resource.supplementary_data.related_ids.order_id');

        if (!$paypalOrderId) {
            return response()->json(['message' => 'No se encontró paypal_order_id en el webhook'], 400);
        }

        $order = Order::where('paypal_order_id', $paypalOrderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada para el paypal_order_id dado'], 404);
        }

        // Podemos asumir que si llega PAYMENT.CAPTURE.COMPLETED está pagado
        $eventType = $payload['event_type'] ?? null;

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $captureId = data_get($payload, 'resource.id');

            $order->update([
                'status'            => Order::STATUS_PAID,
                'paid_at'           => Carbon::now(),
                'paypal_capture_id' => $captureId,
                'paypal_payload'    => $payload,
                'failure_code'      => null,
                'failure_message'   => null,
            ]);
            if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
    $captureId = data_get($payload, 'resource.id');

    $order->update([
        'status'            => Order::STATUS_PAID,
        'paid_at'           => Carbon::now(),
        'paypal_capture_id' => $captureId,
        'paypal_payload'    => $payload,
        'failure_code'      => null,
        'failure_message'   => null,
    ]);

    // Generar tickets para la orden pagada
    $this->ticketService->generateTicketsForOrder($order->fresh('items.ticketType'));
}


            // TODO: aquí, en el módulo de tickets, generaremos los tickets
        } elseif (in_array($eventType, ['PAYMENT.CAPTURE.DENIED', 'PAYMENT.CAPTURE.REFUNDED'])) {
            $order->update([
                'status'         => Order::STATUS_FAILED,
                'failure_code'   => $eventType,
                'failure_message'=> data_get($payload, 'summary'),
                'paypal_payload' => $payload,
            ]);
        }

        return response()->json(['message' => 'Webhook procesado.']);
    }

    /**
     * Cambiar estado a refunded (nivel admin).
     * Permiso: orders.refund
     *
     * Nota:
     * - Aquí solo marcamos internalmente como refunded.
     * - Para integrar un refund real en PayPal usarías la API de refunds.
     */
    public function refund(Request $request, Order $order)
    {
        $user = $request->user();

        if (!$user->can('orders.refund')) {
            return response()->json([
                'message' => 'No tienes permiso para reembolsar órdenes.',
            ], 403);
        }

        if ($order->status !== Order::STATUS_PAID) {
            return response()->json([
                'message' => 'Solo se pueden reembolsar órdenes pagadas.',
            ], 422);
        }

        // TODO: aquí podrías llamar a la API de PayPal para reembolsar de verdad

        $order->update([
            'status'       => Order::STATUS_REFUNDED,
            'refunded_at'  => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Orden marcada como reembolsada.',
            'data'    => $order,
        ]);
    }

    /**
     * Cambiar status manualmente (orders.edit_status)
     */
    public function updateStatus(Request $request, Order $order)
    {
        $user = $request->user();

        if (!$user->can('orders.edit_status')) {
            return response()->json([
                'message' => 'No tienes permiso para editar el estado de órdenes.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,refunded,failed'],
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Estado de la orden actualizado.',
            'data'    => $order,
        ]);
    }
}

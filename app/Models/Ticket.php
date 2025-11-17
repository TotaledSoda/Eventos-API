<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_VALID       = 'valid';
    public const STATUS_USED        = 'used';
    public const STATUS_INVALIDATED = 'invalidated';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'user_id',
        'event_id',
        'ticket_type_id',
        'uuid',
        'status',
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    // Relaciones
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function scans()
    {
        return $this->hasMany(TicketScan::class);
    }

    /**
     * Genera el UUID para un ticket nuevo si no lo tiene.
     */
    public static function booted()
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Payload que irá dentro del QR.
     * Formato: uuid|firma_hmac
     */
    public function getQrCodePayload(): string
    {
        $secret   = Config::get('app.key');
        $signature = hash_hmac('sha256', $this->uuid, $secret);

        return $this->uuid . '|' . $signature;
    }
}

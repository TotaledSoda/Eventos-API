<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_PAID     = 'paid';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_FAILED   = 'failed';

    protected $fillable = [
        'user_id',
        'total_amount',
        'currency',
        'status',
        'paypal_order_id',
        'paypal_capture_id',
        'paypal_payload',
        'paid_at',
        'refunded_at',
        'failure_code',
        'failure_message',
    ];

    protected $casts = [
        'total_amount'   => 'float',
        'paypal_payload' => 'array',
        'paid_at'        => 'datetime',
        'refunded_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

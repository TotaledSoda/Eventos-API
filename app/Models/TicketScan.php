<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'scanned_by',
        'scanned_at',
        'result',
        'device_info',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'latitude'   => 'float',
        'longitude'  => 'float',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'float',
        'stock'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

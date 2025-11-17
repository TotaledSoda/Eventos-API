<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'title',
        'slug',
        'category',
        'description',
        'image_path',
        'starts_at',
        'venue_name',
        'address',
        'latitude',
        'longitude',
        'status',
        'is_featured',
        'published_at',
        'canceled_at',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
        'canceled_at'  => 'datetime',
        'latitude'     => 'float',
        'longitude'    => 'float',
    ];

    // Relaciones
    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    // Generar slug si no viene
    protected static function booted()
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title . '-' . uniqid());
            }
        });
    }
}

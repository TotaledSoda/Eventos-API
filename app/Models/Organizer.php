<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    public function events()
{
    return $this->hasMany(Event::class);
}
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'phone',
        'website',
        'logo_path',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Usuario dueño del organizador.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

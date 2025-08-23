<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'font_id',
        'color_id',
        'last_location_latlon',
        'last_location_city',
        'last_location_updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Asigna valores por defecto al crear un nuevo usuario.
     */
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (is_null($user->font_id)) {
                $user->font_id = 1;
            }
            if (is_null($user->color_id)) {
                $user->color_id = 1;
            }
        });
    }
}

<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'font_id',
        'color_id',
        'last_location_latlon',
        'last_location_city',
        'last_location_updated_at',
        'role',
        'is_blocked',
        'free_daily_quota',
        'free_daily_used',
        'free_daily_date',
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
            'is_blocked' => 'boolean',
            'free_daily_quota' => 'integer',
            'free_daily_used' => 'integer',
            'free_daily_date' => 'date',
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

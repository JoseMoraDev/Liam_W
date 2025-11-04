<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocationPref extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ccaa_id',
        'cpro',
        'area_code',
        'municipio_id',
        'municipio_name',
    ];
}

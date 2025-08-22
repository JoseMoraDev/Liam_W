<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Endpoint;

class UbicacionEndpointUsuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint_id',
        'tipo_ubicacion',
        'valor_lat',
        'valor_lon',
        'valor_id_municipio',
        'nombre_amigable',
        'usos'
    ];

    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

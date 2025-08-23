<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Endpoint;

class UbicacionEndpointUsuario extends Model
{
    use HasFactory;

    // Forzar nombre de tabla
    protected $table = 'ubicaciones_endpoint_usuario';

    protected $fillable = [
        'user_id',
        'endpoint_id',
        'tipo_ubicacion',
        'valor_lat',
        'valor_lon',
        'bbox_norte',
        'bbox_sur',
        'bbox_este',
        'bbox_oeste',
        'valor_id_municipio',
        'valor_codigo_playa',
        'valor_codigo_montana',
        'valor_codigo_area',
        'valor_codigo_zona',
        'nombre_amigable',
        'usos',
        'predeterminada',
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

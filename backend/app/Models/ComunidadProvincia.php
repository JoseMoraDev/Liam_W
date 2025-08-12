<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComunidadProvincia extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'comunidades_provincias';

    // No uses timestamps si no tienes campos created_at y updated_at
    public $timestamps = false; // si tienes timestamps, o false si no

    // Campos que se pueden asignar masivamente
    protected $fillable = ['codauto', 'nomauto', 'cpro', 'nompro'];
}

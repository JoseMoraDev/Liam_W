<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codauto',
        'cpro',
        'cmun',
        'dc',
        'nombre',
    ];

    public $timestamps = false; // Como la tabla no tiene 'created_at' ni 'updated_at'
}

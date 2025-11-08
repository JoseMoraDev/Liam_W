<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EndpointHit extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint_id',
        'hits',
    ];
}

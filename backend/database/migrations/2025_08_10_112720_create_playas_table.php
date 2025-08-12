<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayasTable extends Migration
{
    public function up()
    {
        Schema::create('playas', function (Blueprint $table) {
            $table->string('id_playa')->primary();
            $table->string('nombre_playa')->nullable();
            $table->string('id_provincia')->nullable();
            $table->string('nombre_provincia')->nullable();
            $table->string('id_municipio')->nullable();
            $table->string('nombre_municipio')->nullable();

            // Guardamos DMS originales por si acaso
            $table->string('lat_dms')->nullable();
            $table->string('lon_dms')->nullable();

            // Coordenadas en decimal (suficiente precisión)
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();

            $table->timestamps();
        });

        // Índices para búsquedas por proximidad
        Schema::table('playas', function (Blueprint $table) {
            $table->index(['lat', 'lon']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('playas');
    }
}

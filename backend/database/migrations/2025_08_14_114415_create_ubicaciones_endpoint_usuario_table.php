<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ubicaciones_endpoint_usuario', function (Blueprint $table) {
            $table->id();

            // Relación con usuario y endpoint
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('endpoint_id')->constrained('endpoints')->onDelete('cascade');

            /**
             * Tipo de ubicación según lo que la API requiera.
             * Así no nos limitamos a coordenadas y municipio.
             */
            $table->enum('tipo_ubicacion', [
                'coordenadas',       // lat + lon
                'bbox',              // bounding box
                'municipio',         // código INE
                'codigo_playa',      // código oficial AEMET playa
                'codigo_montana',    // código AEMET montaña
                'codigo_area',       // área CAP
                'codigo_zona',       // para nivológica u otras zonas AEMET
                'sin_ubicacion'      // cuando la API no requiere nada
            ]);

            // Coordenadas simples
            $table->decimal('valor_lat', 10, 6)->nullable();
            $table->decimal('valor_lon', 10, 6)->nullable();

            // Bounding box (para TomTom Incidents y similares)
            $table->decimal('bbox_norte', 10, 6)->nullable();
            $table->decimal('bbox_sur', 10, 6)->nullable();
            $table->decimal('bbox_este', 10, 6)->nullable();
            $table->decimal('bbox_oeste', 10, 6)->nullable();

            // ID de municipio (INE)
            $table->string('valor_id_municipio')->nullable();

            // Códigos específicos para distintas APIs
            $table->string('valor_codigo_playa')->nullable();
            $table->string('valor_codigo_montana')->nullable();
            $table->string('valor_codigo_area')->nullable();
            $table->string('valor_codigo_zona')->nullable();

            // Nombre amigable (ej: "Elche, Alicante")
            $table->string('nombre_amigable')->nullable();

            // Cuántas veces se ha usado
            $table->unsignedInteger('usos')->default(0);

            // Si es la ubicación predeterminada
            $table->boolean('predeterminada')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_endpoint_usuario');
    }
};

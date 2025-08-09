<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenShortcodeToApiCallsDefinitionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('api_calls_definitions', function (Blueprint $table) {
            // Añadimos la columna token_shortcode justo después de la columna id
            $table->string('token_shortcode')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_calls_definitions', function (Blueprint $table) {
            // Eliminamos la columna token_shortcode para revertir la migración
            $table->dropColumn('token_shortcode');
        });
    }
}

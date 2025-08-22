<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComunidadesProvinciasTable extends Migration
{
    public function up()
    {
        Schema::create('comunidades_provincias', function (Blueprint $table) {
            $table->id();
            $table->string('codauto');
            $table->string('nomauto');
            $table->string('cpro');
            $table->string('nompro');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comunidades_provincias');
    }
}

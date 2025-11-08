<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('endpoint_hits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('endpoint_id')->unique();
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamps();
            $table->foreign('endpoint_id')->references('id')->on('endpoints')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('endpoint_hits');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_location_prefs', function (Blueprint $table) {
            $table->string('municipio_id')->nullable()->after('area_code');
            $table->string('municipio_name')->nullable()->after('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_location_prefs', function (Blueprint $table) {
            $table->dropColumn(['municipio_id', 'municipio_name']);
        });
    }
};

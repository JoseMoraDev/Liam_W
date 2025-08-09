<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Fonts
        Schema::create('fonts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source');
        });

        // Users (sin FK color_id todavía)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('username');
            $table->string('hashed_passwd');
            $table->foreignId('font_id')->nullable()->constrained('fonts');
            $table->unsignedBigInteger('color_id')->nullable(); // FK se añade después
            $table->string('last_location_latlon')->nullable();
            $table->string('last_location_city')->nullable();
            $table->timestamp('last_location_updated_at')->nullable();
        });

        // Colors (user_id nullable)
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('font_id')->nullable()->constrained('fonts');
            $table->string('font_size')->nullable();
            $table->string('text_color')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('background_color')->nullable();
            $table->string('border_color')->nullable();
            $table->string('shadow_color')->nullable();
            $table->string('hover_color')->nullable();
        });

        // Locations
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lat_lon');
            $table->boolean('is_default')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users');
        });

        // Tokens
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->text('api_desc');
            $table->string('token');
            $table->string('placeholder_key');
        });

        // API Calls Definitions
        Schema::create('api_calls_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('base_url')->nullable();
            $table->string('path')->nullable();
            $table->string('method')->nullable();
            $table->json('path_placeholders')->nullable();
            $table->json('query_placeholders')->nullable();
            $table->string('auth_type')->nullable();
            $table->string('default_format')->nullable();
            $table->text('notes')->nullable();
        });

        // Weather Data
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('endpoint_id')->nullable()->constrained('api_calls_definitions');
            $table->json('data')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
        });

        // API Calls
        Schema::create('api_calls', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->json('params')->nullable();
            $table->text('url')->nullable();
            $table->foreignId('token_id')->nullable()->constrained('tokens');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('definition_id')->nullable()->constrained('api_calls_definitions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_calls');
        Schema::dropIfExists('weather_data');
        Schema::dropIfExists('api_calls_definitions');
        Schema::dropIfExists('tokens');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('users');
        Schema::dropIfExists('fonts');
    }
};

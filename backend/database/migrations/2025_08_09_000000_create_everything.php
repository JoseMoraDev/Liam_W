<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Fonts (referenciada por users y colors)
        Schema::create('fonts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source');
        });

        // 2) Users (creada sin FK a colors; la FK se añadirá al final)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Campos "compatibles" con las migraciones por defecto y tu modelo personalizado
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();      // password estándar
            $table->string('hashed_passwd')->nullable(); // si tu app usa este campo
            $table->rememberToken();
            $table->timestamps();

            // FK a fonts
            $table->foreignId('font_id')->nullable()->constrained('fonts')->onDelete('SET NULL');

            // FK a colors se añade más tarde para evitar ciclo
            $table->unsignedBigInteger('color_id')->nullable();

            // Ubicación última
            $table->string('last_location_latlon')->nullable();
            $table->string('last_location_city')->nullable();
            $table->timestamp('last_location_updated_at')->nullable();
        });

        // 3) Password reset tokens (tu nombre actual: password_reset_tokens)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 4) Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('SET NULL')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 5) Cache & cache_locks
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // 6) Jobs, job_batches, failed_jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 7) Colors (referencia a users y fonts). users existe así que esto es seguro.
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('SET NULL');
            $table->text('description')->nullable();
            $table->foreignId('font_id')->nullable()->constrained('fonts')->onDelete('SET NULL');
            $table->string('font_size')->nullable();
            $table->string('text_color')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('background_color')->nullable();
            $table->string('border_color')->nullable();
            $table->string('shadow_color')->nullable();
            $table->string('hover_color')->nullable();
        });

        // 8) Locations
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lat_lon');
            $table->boolean('is_default')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('CASCADE');
        });

        // 9) Tokens
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->text('api_desc');
            $table->text('token');
            $table->string('placeholder_key');
        });

        // 10) API calls definitions
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

        // 11) Weather data
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('CASCADE');
            $table->foreignId('endpoint_id')->nullable()->constrained('api_calls_definitions')->onDelete('CASCADE');
            $table->json('data')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
        });

        // 12) API calls (instancias/llamadas)
        Schema::create('api_calls', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->json('params')->nullable();
            $table->text('url')->nullable();
            $table->foreignId('token_id')->nullable()->constrained('tokens')->onDelete('SET NULL');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('CASCADE');
            $table->foreignId('definition_id')->nullable()->constrained('api_calls_definitions')->onDelete('SET NULL');
        });

        // 13) Finalmente añadimos la FK users.color_id -> colors.id (para resolver el ciclo)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirar la FK circular primero si existe
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Evitar excepción si no existe la FK
                try {
                    $table->dropForeign(['color_id']);
                } catch (\Exception $e) {
                    // ignorar si no existe
                }
            });
        }

        // Borrar tablas en orden inverso a las dependencias
        Schema::dropIfExists('api_calls');
        Schema::dropIfExists('weather_data');
        Schema::dropIfExists('api_calls_definitions');
        Schema::dropIfExists('tokens');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('colors');

        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');

        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');

        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('users');
        Schema::dropIfExists('fonts');
    }
};

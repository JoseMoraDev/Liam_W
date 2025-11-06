<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_location_prefs', function (Blueprint $table) {
            if (!Schema::hasColumn('user_location_prefs', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('municipio_name');
            }
            if (!Schema::hasColumn('user_location_prefs', 'lon')) {
                $table->decimal('lon', 10, 7)->nullable()->after('lat');
            }
            if (!Schema::hasColumn('user_location_prefs', 'avisos_region')) {
                $table->string('avisos_region', 5)->nullable()->after('lon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_location_prefs', function (Blueprint $table) {
            if (Schema::hasColumn('user_location_prefs', 'avisos_region')) {
                $table->dropColumn('avisos_region');
            }
            if (Schema::hasColumn('user_location_prefs', 'lon')) {
                $table->dropColumn('lon');
            }
            if (Schema::hasColumn('user_location_prefs', 'lat')) {
                $table->dropColumn('lat');
            }
        });
    }
};

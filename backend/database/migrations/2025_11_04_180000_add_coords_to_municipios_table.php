<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            if (!Schema::hasColumn('municipios', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('municipios', 'lon')) {
                $table->decimal('lon', 10, 7)->nullable()->after('lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            if (Schema::hasColumn('municipios', 'lat')) {
                $table->dropColumn('lat');
            }
            if (Schema::hasColumn('municipios', 'lon')) {
                $table->dropColumn('lon');
            }
        });
    }
};

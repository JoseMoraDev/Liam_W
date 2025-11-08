<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'free_daily_quota')) {
                $table->unsignedInteger('free_daily_quota')->default(50)->after('is_blocked');
                $table->index('free_daily_quota', 'idx_users_free_daily_quota');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'free_daily_quota')) {
                $table->dropIndex('idx_users_free_daily_quota');
                $table->dropColumn('free_daily_quota');
            }
        });
    }
};

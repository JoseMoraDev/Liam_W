<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'free_daily_used')) {
                $table->unsignedInteger('free_daily_used')->default(0)->after('free_daily_quota');
                $table->index('free_daily_used', 'idx_users_free_daily_used');
            }
            if (!Schema::hasColumn('users', 'free_daily_date')) {
                $table->date('free_daily_date')->nullable()->after('free_daily_used');
                $table->index('free_daily_date', 'idx_users_free_daily_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'free_daily_date')) {
                $table->dropIndex('idx_users_free_daily_date');
                $table->dropColumn('free_daily_date');
            }
            if (Schema::hasColumn('users', 'free_daily_used')) {
                $table->dropIndex('idx_users_free_daily_used');
                $table->dropColumn('free_daily_used');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['free', 'premium', 'admin'])->default('free')->after('password');
                $table->index('role', 'idx_users_role');
            }
            if (!Schema::hasColumn('users', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('role');
                $table->index('is_blocked', 'idx_users_is_blocked');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_blocked')) {
                $table->dropIndex('idx_users_is_blocked');
                $table->dropColumn('is_blocked');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropIndex('idx_users_role');
                $table->dropColumn('role');
            }
        });
    }
};

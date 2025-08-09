<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShortcodeToTokensTable extends Migration
{
    public function up()
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->string('shortcode')->unique()->after('placeholder_key');
        });
    }

    public function down()
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->dropColumn('shortcode');
        });
    }
}

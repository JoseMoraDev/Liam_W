<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTimestampsFromPlayasTable extends Migration
{
    public function up()
    {
        Schema::table('playas', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }

    public function down()
    {
        Schema::table('playas', function (Blueprint $table) {
            $table->timestamps();
        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FontsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('fonts')->insert([
            ['name' => 'Arial', 'source' => 'system'],
            ['name' => 'Roboto', 'source' => 'google'],
        ]);
    }
}

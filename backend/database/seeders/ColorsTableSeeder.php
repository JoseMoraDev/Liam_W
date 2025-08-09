<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('colors')->insert([
            [
                'name' => 'Blue',
                'user_id' => 1,
                'description' => 'Blue color theme',
                'font_id' => 1,
                'font_size' => '12px',
                'text_color' => '#0000FF',
                'primary_color' => '#0000FF',
                'secondary_color' => '#00FFFF',
                'background_color' => '#E0EFFF',
                'border_color' => '#000099',
                'shadow_color' => '#CCCCFF',
                'hover_color' => '#0000CC',
            ],
        ]);
    }
}

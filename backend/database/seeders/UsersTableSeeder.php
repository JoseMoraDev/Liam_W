<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'email' => 'nett@nett.es',
                'username' => 'nett',
                'password' => Hash::make('nett'), // esta contraseña será hasheada
                'font_id' => 1,
                'color_id' => null,
                'last_location_latlon' => null,
                'last_location_city' => null,
                'last_location_updated_at' => now(),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MontanosasTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('montanosas')->truncate();

        $rows = [
            ['code' => 'peu1', 'name' => 'Picos de Europa'],
            ['code' => 'nav1', 'name' => 'Pirineo Navarro'],
            ['code' => 'arn1', 'name' => 'Pirineo Aragonés'],
            ['code' => 'cat1', 'name' => 'Pirineo Catalán'],
            ['code' => 'rio1', 'name' => 'Ibérica Riojana'],
            ['code' => 'arn2', 'name' => 'Ibérica Aragonesa'],
            ['code' => 'mad2', 'name' => 'Sierras de Guadarrama y Somosierra'],
            ['code' => 'gre1', 'name' => 'Sierra de Gredos'],
            ['code' => 'nev1', 'name' => 'Sierra Nevada'],
        ];

        DB::table('montanosas')->insert($rows);
    }
}

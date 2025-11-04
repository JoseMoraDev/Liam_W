<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComunidadesProvinciasAreasSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codauto' => '01', 'cpro' => '04', 'codAreaMont' => 'nev1', 'areaMontana' => 'Sierra Nevada'],
            ['codauto' => '01', 'cpro' => '11', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '01', 'cpro' => '14', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '01', 'cpro' => '18', 'codAreaMont' => 'nev1', 'areaMontana' => 'Sierra Nevada'],
            ['codauto' => '01', 'cpro' => '21', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '01', 'cpro' => '23', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '01', 'cpro' => '29', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '01', 'cpro' => '41', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '02', 'cpro' => '22', 'codAreaMont' => 'arn1', 'areaMontana' => 'Pirineo Aragonés'],
            ['codauto' => '02', 'cpro' => '44', 'codAreaMont' => 'arn2', 'areaMontana' => 'Ibérica Aragonesa'],
            ['codauto' => '02', 'cpro' => '50', 'codAreaMont' => 'arn2', 'areaMontana' => 'Ibérica Aragonesa'],
            ['codauto' => '03', 'cpro' => '33', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '04', 'cpro' => '07', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '05', 'cpro' => '35', 'codAreaMont' => 'nev1', 'areaMontana' => 'Sierra Nevada'],
            ['codauto' => '05', 'cpro' => '38', 'codAreaMont' => 'nev1', 'areaMontana' => 'Sierra Nevada'],
            ['codauto' => '06', 'cpro' => '39', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '07', 'cpro' => '05', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '07', 'cpro' => '09', 'codAreaMont' => 'arn2', 'areaMontana' => 'Ibérica Aragonesa'],
            ['codauto' => '07', 'cpro' => '24', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '07', 'cpro' => '34', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '07', 'cpro' => '37', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '07', 'cpro' => '40', 'codAreaMont' => 'mad2', 'areaMontana' => 'Sierras de Guadarrama y Somosierra'],
            ['codauto' => '07', 'cpro' => '42', 'codAreaMont' => 'arn2', 'areaMontana' => 'Ibérica Aragonesa'],
            ['codauto' => '07', 'cpro' => '47', 'codAreaMont' => 'mad2', 'areaMontana' => 'Sierras de Guadarrama y Somosierra'],
            ['codauto' => '07', 'cpro' => '49', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '08', 'cpro' => '02', 'codAreaMont' => 'nev1', 'areaMontana' => 'Sierra Nevada'],
            ['codauto' => '08', 'cpro' => '13', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '08', 'cpro' => '16', 'codAreaMont' => 'arn2', 'areaMontana' => 'Ibérica Aragonesa'],
            ['codauto' => '08', 'cpro' => '19', 'codAreaMont' => 'mad2', 'areaMontana' => 'Sierras de Guadarrama y Somosierra'],
            ['codauto' => '08', 'cpro' => '45', 'codAreaMont' => 'mad2', 'areaMontana' => 'Sierras de Guadarrama y Somosierra'],
            ['codauto' => '09', 'cpro' => '08', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '09', 'cpro' => '17', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '09', 'cpro' => '25', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '09', 'cpro' => '43', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '10', 'cpro' => '03', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '10', 'cpro' => '12', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '10', 'cpro' => '46', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '11', 'cpro' => '06', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '11', 'cpro' => '10', 'codAreaMont' => 'gre1', 'areaMontana' => 'Sierra de Gredos'],
            ['codauto' => '12', 'cpro' => '15', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '12', 'cpro' => '27', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '12', 'cpro' => '32', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '12', 'cpro' => '36', 'codAreaMont' => 'peu1', 'areaMontana' => 'Picos de Europa'],
            ['codauto' => '13', 'cpro' => '28', 'codAreaMont' => 'mad2', 'areaMontana' => 'Sierras de Guadarrama y Somosierra'],
            ['codauto' => '14', 'cpro' => '30', 'codAreaMont' => 'cat1', 'areaMontana' => 'Pirineo Catalán'],
            ['codauto' => '15', 'cpro' => '31', 'codAreaMont' => 'nav1', 'areaMontana' => 'Pirineo Navarro'],
            ['codauto' => '16', 'cpro' => '01', 'codAreaMont' => 'nav1', 'areaMontana' => 'Pirineo Navarro'],
            ['codauto' => '16', 'cpro' => '20', 'codAreaMont' => 'nav1', 'areaMontana' => 'Pirineo Navarro'],
            ['codauto' => '16', 'cpro' => '48', 'codAreaMont' => 'nav1', 'areaMontana' => 'Pirineo Navarro'],
            ['codauto' => '17', 'cpro' => '26', 'codAreaMont' => 'rio1', 'areaMontana' => 'Ibérica Riojana'],
            ['codauto' => '18', 'cpro' => '51', 'codAreaMont' => 'nev1', 'areaMontana' => 'Sierra Nevada'],
            ['codauto' => '19', 'cpro' => '52', 'codAreaMont' => 'nev1', 'areaMontana' => 'Sierra Nevada'],
        ];

        foreach ($rows as $row) {
            DB::table('comunidades_provincias')
                ->where('codauto', $row['codauto'])
                ->where('cpro', $row['cpro'])
                ->update([
                    'codAreaMont' => $row['codAreaMont'],
                    'areaMontana' => $row['areaMontana'],
                ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReglasNineboxSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('reglas_ninebox')->truncate();

        // Desempeño
        $D_bajo  = [5, 7];
        $D_medio = [8, 16];
        $D_alto  = [17, 25];

        // Potencial
        $P_bajo  = [5, 10];
        $P_medio = [11, 19];
        $P_alto  = [20, 25];

        $grid = [
            [1, 3, 4], // Potencial BAJO   (fila inferior)
            [2, 5, 7], // Potencial MEDIO  (fila media)
            [6, 8, 9], // Potencial ALTO   (fila superior)
        ];

        $D = [$D_bajo, $D_medio, $D_alto];
        $P = [$P_bajo, $P_medio, $P_alto];

        $etq = [
            ['Bajo desempeño / Bajo potencial',  'Medio desempeño / Bajo potencial',  'Alto desempeño / Bajo potencial'],
            ['Bajo desempeño / Medio potencial', 'Medio desempeño / Medio potencial', 'Alto desempeño / Medio potencial'],
            ['Bajo desempeño / Alto potencial',  'Medio desempeño / Alto potencial',  'Alto desempeño / Alto potencial'],
        ];

        $rows = [];
        for ($r = 0; $r < 3; $r++) {          // r: potencial 
            for ($c = 0; $c < 3; $c++) {      // c: desempeño 
                $row = [
                    'min_desempeno' => $D[$c][0],
                    'max_desempeno' => $D[$c][1],
                    'min_potencial' => $P[$r][0],
                    'max_potencial' => $P[$r][1],
                    'ninebox_id'    => $grid[$r][ $c ],
                    'etiqueta'      => $etq[$r][ $c ],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                if (Schema::hasColumn('reglas_ninebox', 'activo')) {
                    $row['activo'] = 1;
                }
                $rows[] = $row;
            }
        }

        DB::table('reglas_ninebox')->insert($rows);
    }
}
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReglasNineboxSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('reglas_ninebox')->truncate();

        // Desempeño (columnas: izq→der)
        // Bajo (5-12):  promedio < 2.5  → por debajo de lo aceptable
        // Medio (13-19): promedio 2.6-3.8 → cumple expectativas
        // Alto (20-25): promedio ≥ 4.0  → supera consistentemente
        $D = [
            'bajo'  => [5,  12],
            'medio' => [13, 19],
            'alto'  => [20, 25],
        ];

        // Potencial (filas: abajo→arriba)
        $P = [
            'bajo'  => [5,  12],
            'medio' => [13, 19],
            'alto'  => [20, 25],
        ];

        // [ninebox_id, etiqueta, min_des, max_des, min_pot, max_pot]
        $reglas = [
            // Potencial ALTO
            [6, 'Bajo desempeño / Alto potencial',   $D['bajo'][0],  $D['bajo'][1],  $P['alto'][0], $P['alto'][1]],
            [8, 'Medio desempeño / Alto potencial',  $D['medio'][0], $D['medio'][1], $P['alto'][0], $P['alto'][1]],
            [9, 'Alto desempeño / Alto potencial',   $D['alto'][0],  $D['alto'][1],  $P['alto'][0], $P['alto'][1]],
            // Potencial MEDIO
            [2, 'Bajo desempeño / Medio potencial',  $D['bajo'][0],  $D['bajo'][1],  $P['medio'][0], $P['medio'][1]],
            [5, 'Medio desempeño / Medio potencial', $D['medio'][0], $D['medio'][1], $P['medio'][0], $P['medio'][1]],
            [7, 'Alto desempeño / Medio potencial',  $D['alto'][0],  $D['alto'][1],  $P['medio'][0], $P['medio'][1]],
            // Potencial BAJO
            [1, 'Bajo desempeño / Bajo potencial',   $D['bajo'][0],  $D['bajo'][1],  $P['bajo'][0], $P['bajo'][1]],
            [3, 'Medio desempeño / Bajo potencial',  $D['medio'][0], $D['medio'][1], $P['bajo'][0], $P['bajo'][1]],
            [4, 'Alto desempeño / Bajo potencial',   $D['alto'][0],  $D['alto'][1],  $P['bajo'][0], $P['bajo'][1]],
        ];

        $rows = [];
        foreach ($reglas as [$nineboxId, $etiqueta, $minD, $maxD, $minP, $maxP]) {
            $rows[] = [
                'ninebox_id'    => $nineboxId,
                'etiqueta'      => $etiqueta,
                'min_desempeno' => $minD,
                'max_desempeno' => $maxD,
                'min_potencial' => $minP,
                'max_potencial' => $maxP,
                'activo'        => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        DB::table('reglas_ninebox')->insert($rows);
    }
}
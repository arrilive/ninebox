<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReglasNineboxSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            // Cuadrante Bajo (0–10)
            ['min_desempeno'=>0,  'max_desempeno'=>10, 'min_potencial'=>0,  'max_potencial'=>10, 'ninebox_id'=>1, 'etiqueta'=>'Bajo desempeño / Bajo potencial',   'created_at'=>$now,'updated_at'=>$now],
            ['min_desempeno'=>11, 'max_desempeno'=>20, 'min_potencial'=>0,  'max_potencial'=>10, 'ninebox_id'=>2, 'etiqueta'=>'Medio desempeño / Bajo potencial',  'created_at'=>$now,'updated_at'=>$now],
            ['min_desempeno'=>21, 'max_desempeno'=>25, 'min_potencial'=>0,  'max_potencial'=>10, 'ninebox_id'=>3, 'etiqueta'=>'Alto desempeño / Bajo potencial',   'created_at'=>$now,'updated_at'=>$now],

            // Cuadrante Medio (11–20)
            ['min_desempeno'=>0,  'max_desempeno'=>10, 'min_potencial'=>11, 'max_potencial'=>20, 'ninebox_id'=>4, 'etiqueta'=>'Bajo desempeño / Medio potencial',  'created_at'=>$now,'updated_at'=>$now],
            ['min_desempeno'=>11, 'max_desempeno'=>20, 'min_potencial'=>11, 'max_potencial'=>20, 'ninebox_id'=>5, 'etiqueta'=>'Medio desempeño / Medio potencial', 'created_at'=>$now,'updated_at'=>$now],
            ['min_desempeno'=>21, 'max_desempeno'=>25, 'min_potencial'=>11, 'max_potencial'=>20, 'ninebox_id'=>6, 'etiqueta'=>'Alto desempeño / Medio potencial',  'created_at'=>$now,'updated_at'=>$now],

            // Cuadrante Alto (21–25)
            ['min_desempeno'=>0,  'max_desempeno'=>10, 'min_potencial'=>21, 'max_potencial'=>25, 'ninebox_id'=>7, 'etiqueta'=>'Bajo desempeño / Alto potencial',   'created_at'=>$now,'updated_at'=>$now],
            ['min_desempeno'=>11, 'max_desempeno'=>20, 'min_potencial'=>21, 'max_potencial'=>25, 'ninebox_id'=>8, 'etiqueta'=>'Medio desempeño / Alto potencial',  'created_at'=>$now,'updated_at'=>$now],
            ['min_desempeno'=>21, 'max_desempeno'=>25, 'min_potencial'=>21, 'max_potencial'=>25, 'ninebox_id'=>9, 'etiqueta'=>'Estrella (Alto/Alto)',              'created_at'=>$now,'updated_at'=>$now],
        ];

        DB::table('reglas_ninebox')->truncate();
        DB::table('reglas_ninebox')->insert($rows);
    }
}

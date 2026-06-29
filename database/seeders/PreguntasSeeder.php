<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreguntasSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $preguntas = [
            // Desempeño (5) 
            ['texto' => '¿Con qué frecuencia cumple o excede sus objetivos clave (KPIs, metas) de forma consistente?', 'categoria' => 'desempeno', 'orden' => 1],
            ['texto' => '¿Qué tan eficiente es en el uso de recursos (tiempo, presupuesto, equipo)?',                  'categoria' => 'desempeno', 'orden' => 2],
            ['texto' => '¿Cuál es la calidad de su trabajo: precisión y atención al detalle?',                         'categoria' => 'desempeno', 'orden' => 3],
            ['texto' => '¿Qué tan bien colabora con otros departamentos, equipos o colegas?',                          'categoria' => 'desempeno', 'orden' => 4],
            ['texto' => '¿Cómo maneja la presión, plazos exigentes o cargas de trabajo intensas?',                     'categoria' => 'desempeno', 'orden' => 5],

            // Potencial (5) 
            ['texto' => '¿Qué tan rápido aprende y aplica nuevos conocimientos o habilidades?',                        'categoria' => 'potencial', 'orden' => 6],
            ['texto' => '¿Muestra interés o iniciativa para asumir retos más complejos o roles con mayor responsabilidad?', 'categoria' => 'potencial', 'orden' => 7],
            ['texto' => '¿Tiene habilidades de liderazgo emergentes (comunicación, influencia, toma de decisiones)?',  'categoria' => 'potencial', 'orden' => 8],
            ['texto' => '¿Cómo se adapta al cambio, incertidumbre, nuevas prioridades o ambigüedad?',                  'categoria' => 'potencial', 'orden' => 9],
            ['texto' => '¿Cuál es su motivación de desarrollo: aspira a crecer profesionalmente dentro de la organización?', 'categoria' => 'potencial', 'orden' => 10],
        ];

        if (app()->environment(['local', 'development', 'testing'])) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 🔸 Desactiva validación temporal
            DB::table('preguntas')->truncate();         // Limpia tabla
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // 🔸 Vuelve a activar

            DB::table('preguntas')->insert(
                collect($preguntas)
                    ->map(fn ($p) => $p + ['created_at' => $now, 'updated_at' => $now])
                    ->toArray()
            );
            return;
        }

        DB::table('preguntas')->upsert(
            collect($preguntas)
                ->map(fn ($p) => $p + ['updated_at' => $now])
                ->toArray(),
            ['texto', 'categoria'],
            ['orden', 'updated_at']
        );
    }
}
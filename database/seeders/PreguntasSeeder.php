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
            // ===== Desempeño (5) =====
            ['texto' => '¿Con qué frecuencia cumple o excede sus objetivos clave (KPIs, metas) de forma consistente?',          'categoria' => 'desempeno'],
            ['texto' => '¿Qué tan eficiente es en el uso de recursos (tiempo, presupuesto, equipo)?',                           'categoria' => 'desempeno'],
            ['texto' => '¿Cuál es la calidad de su trabajo: precisión y atención al detalle?',                                  'categoria' => 'desempeno'],
            ['texto' => '¿Qué tan bien colabora con otros departamentos, equipos o colegas?',                                   'categoria' => 'desempeno'],
            ['texto' => '¿Cómo maneja la presión, plazos exigentes o cargas de trabajo intensas?',                              'categoria' => 'desempeno'],

            // ===== Potencial (5) =====
            ['texto' => '¿Qué tan rápido aprende y aplica nuevos conocimientos o habilidades?',                                 'categoria' => 'potencial'],
            ['texto' => '¿Muestra interés o iniciativa para asumir retos más complejos o roles con mayor responsabilidad?',     'categoria' => 'potencial'],
            ['texto' => '¿Tiene habilidades de liderazgo emergentes (comunicación, influencia, toma de decisiones)?',           'categoria' => 'potencial'],
            ['texto' => '¿Cómo se adapta al cambio, incertidumbre, nuevas prioridades o ambigüedad?',                           'categoria' => 'potencial'],
            ['texto' => '¿Cuál es su motivación de desarrollo: aspira a crecer profesionalmente dentro de la organización?',    'categoria' => 'potencial'],
        ];

        // --- DEV: limpiar seguro (MySQL/MariaDB) ---
        if (app()->environment(['local', 'development'])) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');     // desactiva FKs
            DB::table('evaluaciones')->truncate();         // primero las dependientes
            DB::table('preguntas')->truncate();            // luego el catálogo
            DB::statement('SET FOREIGN_KEY_CHECKS=1');     // activa FKs

            DB::table('preguntas')->insert(
                collect($preguntas)->map(fn ($p) => $p + ['created_at' => $now, 'updated_at' => $now])->toArray()
            );
            return;
        }

        // --- PROD: no borres, actualiza/crea sin duplicar ---
        DB::table('preguntas')->upsert(
            collect($preguntas)->map(fn ($p) => $p + ['created_at' => $now, 'updated_at' => $now])->toArray(),
            ['texto', 'categoria'],                        // clave lógica
            ['updated_at']                                 // columnas a actualizar
        );
    }
}
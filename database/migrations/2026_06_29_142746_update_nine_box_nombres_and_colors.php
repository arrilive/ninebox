<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cuadrantes = [
            1 => ['nombre' => 'Inaceptable',           'color_hex' => '#991b1b'],
            2 => ['nombre' => 'Mal empleado',           'color_hex' => '#dc2626'],
            3 => ['nombre' => 'Aceptable',              'color_hex' => '#f97316'],
            4 => ['nombre' => 'Persona clave',          'color_hex' => '#d97706'],
            5 => ['nombre' => 'Personal sólido',        'color_hex' => '#ca8a04'],
            6 => ['nombre' => 'Diamante en bruto',      'color_hex' => '#eab308'],
            7 => ['nombre' => 'Elemento importante',    'color_hex' => '#65a30d'],
            8 => ['nombre' => 'Estrella en desarrollo', 'color_hex' => '#22c55e'],
            9 => ['nombre' => 'Estrella',               'color_hex' => '#16a34a'],
        ];

        foreach ($cuadrantes as $posicion => $data) {
            DB::table('nine_box')->where('posicion', $posicion)->update([
                'nombre'    => $data['nombre'],
                'color_hex' => $data['color_hex'],
            ]);
        }
    }

    public function down(): void
    {
        $originales = [
            1 => 'Bajo/Bajo',
            2 => 'Medio/Bajo',
            3 => 'Alto/Bajo',
            4 => 'Bajo/Medio',
            5 => 'Medio/Medio',
            6 => 'Alto/Medio',
            7 => 'Bajo/Alto',
            8 => 'Medio/Alto',
            9 => 'Alto/Alto',
        ];

        foreach ($originales as $posicion => $nombre) {
            DB::table('nine_box')->where('posicion', $posicion)->update([
                'nombre'    => $nombre,
                'color_hex' => null,
            ]);
        }
    }
};

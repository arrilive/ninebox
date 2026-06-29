<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tipos_usuarios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('tipos_usuarios')->insert([
            ['tipo_nombre' => 'Superadmin', 'descripcion' => 'Acceso total al sistema',                            'created_at' => now(), 'updated_at' => now()],
            ['tipo_nombre' => 'Dueño',      'descripcion' => 'Propietario de empresa. Evalúa a sus jefes.',       'created_at' => now(), 'updated_at' => now()],
            ['tipo_nombre' => 'Jefe',       'descripcion' => 'Líder de departamento. Evalúa a sus empleados.',    'created_at' => now(), 'updated_at' => now()],
            ['tipo_nombre' => 'Empleado',   'descripcion' => 'Colaborador evaluado. Sin acceso al sistema.',      'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tipos_usuarios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};

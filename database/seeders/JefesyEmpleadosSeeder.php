<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JefesYEmpleadosSeeder extends Seeder
{
    public function run(): void
    {
        // Crea 7 departamentos y 7 jefes
        for ($i = 1; $i <= 7; $i++) {
            // Crea jefe
            $jefeId = DB::table('usuarios')->insertGetId([
                'user_name' => "jefe{$i}",
                'password' => Hash::make('password123'),
                'correo' => "jefe{$i}@ejemplo.com",
                'apellido_paterno' => "Jefe{$i}",
                'apellido_materno' => "Apellido",
                'telefono' => "55500000{$i}",
                'tipo_usuario_id' => 2, // 2 = administrador/jefe
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crea departamento y lo asigna al jefe
            $departamentoId = DB::table('departamentos')->insertGetId([
                'nombre_departamento' => "Departamento {$i}",
                'descripcion' => "Departamento de prueba {$i}",
                'jefe_id' => $jefeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Actualiza jefe con el departamento asignado
            DB::table('usuarios')->where('id', $jefeId)->update([
                'departamento_id' => $departamentoId,
            ]);

            // Crea 5 empleados para cada jefe
            for ($j = 1; $j <= 5; $j++) {
                DB::table('usuarios')->insert([
                    'user_name' => "empleado{$i}_{$j}",
                    'password' => Hash::make('password123'),
                    'correo' => "empleado{$i}_{$j}@ejemplo.com",
                    'apellido_paterno' => "Empleado{$j}",
                    'apellido_materno' => "Apellido",
                    'telefono' => "55510000{$i}{$j}",
                    'tipo_usuario_id' => 3, // 3 = empleado
                    'departamento_id' => $departamentoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Crea un superusuario
        DB::table('usuarios')->insert([
            'user_name' => "superadmin",
            'password' => Hash::make('superpassword'),
            'correo' => "superadmin@ejemplo.com",
            'apellido_paterno' => "Super",
            'apellido_materno' => "Admin",
            'telefono' => "5559999999",
            'tipo_usuario_id' => 1, // 1 = superusuario
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
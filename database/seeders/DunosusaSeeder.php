<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DunosusaSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPwd = Hash::make(env('SEEDER_DEFAULT_PASSWORD', 'changeme'));

        // Verificar que empresa Dunosusa existe (id=2)
        $empresa = DB::table('empresas')->where('slug', 'dunosusa')->first();
        if (!$empresa) {
            DB::table('empresas')->insert([
                'id'         => 2,
                'nombre'     => 'Dunosusa',
                'slug'       => 'dunosusa',
                'activa'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $empresaId = 2;

        // ── Departamento: Recursos Humanos ──────────────────────────────
        $deptRH = DB::table('departamentos')
            ->where('nombre_departamento', 'Recursos Humanos')
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$deptRH) {
            $deptRHId = DB::table('departamentos')->insertGetId([
                'nombre_departamento' => 'Recursos Humanos',
                'descripcion'         => 'Recursos Humanos',
                'empresa_id'          => $empresaId,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        } else {
            $deptRHId = $deptRH->id;
        }

        // ── Jefa: Carolina Medina ───────────────────────────────────────
        $jefa = DB::table('usuarios')->where('correo', 'Carolina.Medina@dunosusa.com.mx')->first();

        if (!$jefa) {
            $jefaId = DB::table('usuarios')->insertGetId([
                'user_name'          => 'carolina_medina',
                'password'           => $defaultPwd,
                'correo'             => 'Carolina.Medina@dunosusa.com.mx',
                'nombre'             => 'Carolina',
                'apellido_paterno'   => 'Medina',
                'apellido_materno'   => null,
                'telefono'           => null,
                'tipo_usuario_id'    => 2,
                'departamento_id'    => $deptRHId,
                'empresa_id'         => $empresaId,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } else {
            $jefaId = $jefa->id;
            DB::table('usuarios')->where('id', $jefaId)->update([
                'tipo_usuario_id' => 2,
                'departamento_id' => $deptRHId,
                'empresa_id'      => $empresaId,
            ]);
        }

        // Asignar jefa al departamento
        DB::table('departamentos')->where('id', $deptRHId)->update([
            'jefe_id' => $jefaId,
        ]);

        // ── Empleados de prueba ─────────────────────────────────────────
        $empleadosPrueba = [
            ['user_name' => 'empleado_dunosusa_1', 'nombre' => 'Empleado',  'apellido_paterno' => 'Prueba',  'apellido_materno' => 'Uno',    'correo' => 'empleado1@dunosusa.com.mx'],
            ['user_name' => 'empleado_dunosusa_2', 'nombre' => 'Empleado',  'apellido_paterno' => 'Prueba',  'apellido_materno' => 'Dos',    'correo' => 'empleado2@dunosusa.com.mx'],
            ['user_name' => 'empleado_dunosusa_3', 'nombre' => 'Empleado',  'apellido_paterno' => 'Prueba',  'apellido_materno' => 'Tres',   'correo' => 'empleado3@dunosusa.com.mx'],
            ['user_name' => 'empleado_dunosusa_4', 'nombre' => 'Empleado',  'apellido_paterno' => 'Prueba',  'apellido_materno' => 'Cuatro', 'correo' => 'empleado4@dunosusa.com.mx'],
            ['user_name' => 'empleado_dunosusa_5', 'nombre' => 'Empleado',  'apellido_paterno' => 'Prueba',  'apellido_materno' => 'Cinco',  'correo' => 'empleado5@dunosusa.com.mx'],
        ];

        foreach ($empleadosPrueba as $emp) {
            $existe = DB::table('usuarios')->where('user_name', $emp['user_name'])->first();
            if (!$existe) {
                DB::table('usuarios')->insert([
                    'user_name'        => $emp['user_name'],
                    'password'         => $defaultPwd,
                    'correo'           => $emp['correo'],
                    'nombre'           => $emp['nombre'],
                    'apellido_paterno' => $emp['apellido_paterno'],
                    'apellido_materno' => $emp['apellido_materno'],
                    'telefono'         => null,
                    'tipo_usuario_id'  => 3,
                    'departamento_id'  => $deptRHId,
                    'empresa_id'       => $empresaId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            } else {
                DB::table('usuarios')->where('id', $existe->id)->update([
                    'empresa_id'      => $empresaId,
                    'departamento_id' => $deptRHId,
                ]);
            }
        }
    }
}

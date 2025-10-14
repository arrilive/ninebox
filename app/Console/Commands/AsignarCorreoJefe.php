<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AsignarCorreoJefe extends Command
{
    protected $signature = 'jefe:asignar-correo {username} {correo}';
    protected $description = 'Asigna correo y contraseña a un jefe';

    public function handle()
    {
        $username = $this->argument('username');
        $correo = $this->argument('correo');

        $jefe = User::where('user_name', $username)
                    ->where('tipo_usuario_id', 2)
                    ->first();

        if (!$jefe) {
            $this->error("No se encontró un jefe con el username: {$username}");
            return 1;
        }

        $jefe->correo = $correo;
        $jefe->password = Hash::make('password123');
        $jefe->save();

        $this->info("Jefe configurado correctamente:");
        $this->line("Nombre: {$jefe->apellido_paterno} {$jefe->apellido_materno}");
        $this->line("Correo: {$jefe->correo}");
        $this->line("Departamento: " . ($jefe->departamento ? $jefe->departamento->nombre_departamento : 'N/A'));
        $this->line("Contraseña: password123");

        return 0;
    }
}
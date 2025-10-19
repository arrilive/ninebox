<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JefesYEmpleadosSeeder extends Seeder
{
    public function run(): void
    {
        // Define tus roles con ID fijo
        $rows = [
            ['id' => 1, 'tipo_nombre' => 'Superadmin', 'descripcion' => 'Acceso total al sistema'],
            ['id' => 2, 'tipo_nombre' => 'Jefe',       'descripcion' => 'Gestión de equipos y evaluaciones'],
            ['id' => 3, 'tipo_nombre' => 'Empleado',   'descripcion' => 'Acceso estándar a funcionalidades'],
            ['id' => 4, 'tipo_nombre' => 'RRHH', 'descripcion' => 'Gestión de personal'],
        ];

        // Inserta/actualiza por ID (no duplica si ya existen)
        DB::table('tipos_usuarios')->upsert(
            $rows,
            ['id'],                             // clave única
            ['tipo_nombre', 'descripcion']      // columnas a actualizar si ya existe
        );

        // (MySQL/MariaDB) Ajusta AUTO_INCREMENT al siguiente ID libre
        // Si usas PostgreSQL, omite esto (ver nota abajo).
        $maxId = DB::table('tipos_usuarios')->max('id') ?? 0;
        try {
            DB::statement('ALTER TABLE tipos_usuarios AUTO_INCREMENT = ' . ($maxId + 1));
        } catch (\Throwable $e) {
            // Silencioso: por si no es MySQL/MariaDB o no aplica
        }
    
       $areas = collect([
    [
        'nombre' => 'Administración',
        'jefe' => 'Isaac Sánchez Neri',
        'empleados' => [
            'Thaily Guadalupe Batun Ix',
            'María Neisy Che Colli',
            'Jonathan Alejandro Chi Iuit',
            'Susana María De La Luz Chin Mex',
            'Alma Guadalupe Lavalle Monforte',
            'Mayna López Ceron',
            'Emmanuel Pérez Juárez',
            'Luis Humberto Puc Chuc',
            'Óscar Daniel Salazar Rojas',
            'Julio Jhoanssen Serrano Albor',
            'David Alejandro Torres Cob',
            'Wilbert Alejandro Uitzil Pat',
            'Derian Sebastián Us Chi',
            'María Asunción Us Tzuc',
        ],
    ],
    [
        'nombre' => 'Cedis',
        'jefe' => 'Fabiola Martínez Fernández',
        'empleados' => [
            'Tomás Jesús Abdo Concha',
            'Adrián Manuel Andrade Ortega',
            'Edwin Jhosep Basto López',
            'Valentín Apolonio Cardoz Herrera',
            'Kenny Emmanuel Cen Chi',
            'Jesús Daniel Chi Cob',
            'Alexis Israel Colli Valle',
            'Óscar Raymundo Guillén Iuit',
            'Juan Norman Hernández García',
            'Lizandro Manuel Iuit Canul',
            'Andrés López Villegas',
            'Fernanda Carolina Magaña Medina',
            'Lizbeth Yesenia Naal Mendoza',
            'Jesús Israel Nah Rivero',
            'Ana Regina Robertos Rodríguez',
            'Rogelio Andrés Valle Martín',
            'Jahdai Rubí Vicinaiz Burgos',
        ],
    ],
    [
        'nombre' => 'Dirección Operaciones Mérida',
        'jefe' => 'Jesús Enrique Pérez Cañedo',
        'empleados' => [
            'Martín Antonio Cardos Herrera',
            'Mario García Hurtado',
            'Francisco Javier Quijano Silveira',
            'Jorge Armando Trujeque García',
        ],
    ],
    [
        'nombre' => 'Expansión',
        'jefe' => 'Sergio Antonio Cruz Franco',
        'empleados' => [
            'Manuel David Canul Quijano',
            'Andrea Hernández Bote',
            'Gloria Alejandra Vázquez García',
            'Jessica Del Razo Cedillo',
            'Roger Iván Gómez Chávez',
            'Rodrigo Díaz Maldonado',
            'Alondra Berenice Chable Carrillo',
            'Aarón Concha Riquelme',
            'Christopher Alexander Guillermo Dimas',
            'Jorge Alejandro Herrera Solís',
            'Carlos Erwin Ríos Kuthe',
            'Víctor Hugo Sánchez Pérez',
        ],
    ],
    [
        'nombre' => 'MKT Mérida',
        'jefe' => 'Ana Luisa Vallado Sosa',
        'empleados' => [
            'Jessica Estefanía Cruz Salas',
            'Claudia Gómez Chan',
            'Fernando Quijano Vela',
            'Estefany Yulián Soriano Laines',
            'José Enrique Vallado Sosa',
        ],
    ],
    [
        'nombre' => 'Recursos Humanos',
        'jefe' => 'Martha Guadalupe Luna Saint Martin',
        'empleados' => [
            'Marines Guadalupe Hernández Romero',
            'Sarai Alondra Quijano Cárdenas',
            'Biana De Los Ángeles Rosado Vázquez',
            'Katherine Isabel Tun Cauich',
            'Guadalupe Varela Martínez',
        ],
    ],
    [
        'nombre' => 'Inteligencia Comercial',
        'jefe' => 'Alan Joel Cruz Palma',
        'empleados' => [
            'Gaspar Antonio Gallareta Santos',
            'Adrián Gabriel Velázquez Várguez',
        ],
    ],
])->map(function ($area) {
    // Función auxiliar para descomponer nombres
    $parse = function ($nombreCompleto) {
        $partes = explode(' ', trim($nombreCompleto));
        $nombre = implode(' ', array_slice($partes, 0, -2));
        $apellido_paterno = $partes[count($partes) - 2] ?? '';
        $apellido_materno = $partes[count($partes) - 1] ?? '';
        $slug = $this->slugify($nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno);
        return [
            'user_name' => $slug,
            'nombre' => $nombre,
            'apellido_paterno' => $apellido_paterno,
            'apellido_materno' => $apellido_materno,
            'correo' => "{$slug}@example.com",
            'telefono' => '999' . rand(1000000, 9999999),
        ];
    };

    return [
        'nombre' => $area['nombre'],
        'jefe' => $parse($area['jefe']),
        'empleados' => collect($area['empleados'])->map(fn($e) => $parse($e))->toArray(),
    ];
});


        foreach ($areas as $area) {
            // Crear o recuperar jefe
            $jefe = $area['jefe'];
            $jefeDB = DB::table('usuarios')->where('user_name', $jefe['user_name'])->first();
            if ($jefeDB) {
                $jefeId = $jefeDB->id;
            } else {
                $jefeId = DB::table('usuarios')->insertGetId([
                    'user_name' => $jefe['user_name'],
                    'password' => Hash::make('password123'),
                    'correo' => $jefe['correo'],
                    'nombre' => $jefe['nombre'],
                    'apellido_paterno' => $jefe['apellido_paterno'],
                    'apellido_materno' => $jefe['apellido_materno'],
                    'telefono' => $jefe['telefono'],
                    'tipo_usuario_id' => 2, // jefe
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Crear o recuperar departamento
            $departamento = DB::table('departamentos')->where('nombre_departamento', $area['nombre'])->first();
            if ($departamento) {
                $departamentoId = $departamento->id;
            } else {
                $departamentoId = DB::table('departamentos')->insertGetId([
                    'nombre_departamento' => $area['nombre'],
                    'descripcion' => $area['nombre'],
                    'jefe_id' => $jefeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Actualiza jefe con departamento
            DB::table('usuarios')->where('id', $jefeId)->update([
                'departamento_id' => $departamentoId,
            ]);

            // Crear empleados si no existen
            foreach ($area['empleados'] as $empleado) {
                $existe = DB::table('usuarios')->where('user_name', $empleado['user_name'])->exists();
                if (!$existe) {
                    DB::table('usuarios')->insert([
                        'user_name' => $empleado['user_name'],
                        'password' => Hash::make('password123'),
                        'correo' => $empleado['correo'],
                        'nombre' => $empleado['nombre'],
                        'apellido_paterno' => $empleado['apellido_paterno'],
                        'apellido_materno' => $empleado['apellido_materno'],
                        'telefono' =>   $empleado['telefono'],
                        'tipo_usuario_id' => 3, // empleado
                        'departamento_id' => $departamentoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Crear superusuario si no existe
        $super = DB::table('usuarios')->where('user_name', 'superadmin')->first();
        if (!$super) {
            DB::table('usuarios')->insert([
                'user_name' => "superadmin",
                'password' => Hash::make('superpassword'),
                'correo' => 'ninebox@example.com',
                'nombre' => "Super",
                'apellido_paterno' => "Super",
                'apellido_materno' => "Admin",
                'telefono' => null,
                'tipo_usuario_id' => 1, // superusuario
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // Convierte nombres a slug para user_name
    private function slugify($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[áàäâ]/u', 'a', $text);
        $text = preg_replace('/[éèëê]/u', 'e', $text);
        $text = preg_replace('/[íìïî]/u', 'i', $text);
        $text = preg_replace('/[óòöô]/u', 'o', $text);
        $text = preg_replace('/[úùüû]/u', 'u', $text);
        $text = preg_replace('/[^a-z0-9]+/u', '_', $text);
        $text = trim($text, '_');
        return $text;
    }
}

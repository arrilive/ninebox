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
            ['id' => 4, 'tipo_nombre' => 'Dueño', 'descripcion' => 'Propietario con visión global de los jefes'],
        ];

        // Inserta/actualiza por ID (no duplica si ya existen)
        DB::table('tipos_usuarios')->upsert(
            $rows,
            ['id'],                             // clave única
            ['tipo_nombre', 'descripcion']      // columnas a actualizar si ya existe
        );

        $maxId = DB::table('tipos_usuarios')->max('id') ?? 0;
        try {
            DB::statement('ALTER TABLE tipos_usuarios AUTO_INCREMENT = ' . ($maxId + 1));
        } catch (\Throwable $e) {
        }
    
       $areas = collect([
    [
        'nombre' => 'Administración',
        'jefe' => 'Isaac Sánchez Neri',
        'empleados' => [
            'Thaily Guadalupe Batun Ix',
            'Jonathan Alejandro Chi Iuit',
            'Susana María de la Luz Chin Mex',
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
            'Karen Maribel Canul Fuentes',
            'Yanuri Beatriz Alvarado Dominguez',
            'Luis Antonio Rojas Alcantara',
            'Dulce Maria Rosales Kuan',
            'Olivia Monserrat Barradas Ochoa',
            'Erika Cecilia Vierya Lopez',
            'Ester Ahinoam Blanquet Guzman',
            'Marco Andres Navarro Itza',
            'Nancy del Carmen Canche Garcia',
            'Russel Alejandro Aviles Valdez',
            'Roxana Gutierrez Piñero',
        ],
    ],
    [
        'nombre' => 'CEDIS',
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
        ],
    ],
    [
        'nombre' => 'Expansión',
        'jefe' => 'Sergio Antonio Cruz Franco',
        'empleados' => [
            'Manuel David Canul Quijano',
            'Andrea Hernández Bote',
            'Gloria Alejandra Vázquez García',
            'Jessica del Razo Cedillo',
            'Roger Iván Gómez Chávez',
            'Christian Ramírez Olmos',
            'José Carlos Baladez Espitia',
            'Luis Ángel Santiago Matías',
            'Gonzalo Adrián Medel San Vicente',
            'Mauro González Rodríguez',
            'Martín Uriel García Espitia',
            'Hugo Israel Sánchez Zetina',
            'José David Hernández Garmendia',
            'Ángel Yasmani Pech Herrera',
            'Cesar Canché Yama',
            'Rodrigo Díaz Maldonado',
            'Sergio Ignacio Ancona Ciau',
            'Roger Abdiel Barrera Vazquez',
            'Henry Matías Chan Can',
            'Juan Alejandro Chan Chin',
            'Javier Ricardo Chi Dzib',
            'Héctor Gerardo Chi Dzib',
            'Jorge Manuel González Manzanilla',
            'Enrique Lazcano Zamora',
            'José Raúl Martin Pech',
            'Marcos Gabriel Mukul Ku',
        ],
    ],
    [
        'nombre' => 'Sistemas',
        'jefe' => 'Gerardo de Jesús Franco Cruz',
        'empleados' => [
            'Luis Manuel Grajales Rodríguez',
            'Christopher Alexander Guillermo Dimas',
            'Jorge Alejandro Herrera Solís',
            'Carlos Erwin Ríos Kuthe',
            'Ángel Germán Sánchez Hoil',
            'Víctor Hugo Sánchez Pérez',
            'Samuel Isaac Valle Chi',
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
            'Itzel Alejandra García Marquez',
            'Mairani Guadalupe Tejero Vera',
        ],
    ],
    [
        'nombre' => 'Recursos Humanos',
        'jefe' => 'Martha Guadalupe Luna Saint Martin',
        'empleados' => [
            'Marines Guadalupe Hernández Romero',
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
                // Si el jefe existe pero es empleado (tipo 3), actualizarlo a jefe (tipo 2)
                if ($jefeDB->tipo_usuario_id == 3) {
                    DB::table('usuarios')->where('id', $jefeId)->update([
                        'tipo_usuario_id' => 2,
                    ]);
                }
            } else {
                $jefeId = DB::table('usuarios')->insertGetId([
                    'user_name' => $jefe['user_name'],
                    'password' => Hash::make('password123'),
                    'correo' => $jefe['correo'],
                    'nombre' => $jefe['nombre'],
                    'apellido_paterno' => $jefe['apellido_paterno'],
                    'apellido_materno' => $jefe['apellido_materno'],
                    'telefono' => $jefe['telefono'],
                    'tipo_usuario_id' => 2, 
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

            // Crear empleados si no existen, o actualizar su departamento si ya existen
            foreach ($area['empleados'] as $empleado) {
                $empleadoDB = DB::table('usuarios')->where('user_name', $empleado['user_name'])->first();
                if (!$empleadoDB) {
                    DB::table('usuarios')->insert([
                        'user_name' => $empleado['user_name'],
                        'password' => Hash::make('password123'),
                        'correo' => $empleado['correo'],
                        'nombre' => $empleado['nombre'],
                        'apellido_paterno' => $empleado['apellido_paterno'],
                        'apellido_materno' => $empleado['apellido_materno'],
                        'telefono' =>   $empleado['telefono'],
                        'tipo_usuario_id' => 3, 
                        'departamento_id' => $departamentoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Si el empleado ya existe, actualizar su departamento
                    DB::table('usuarios')->where('id', $empleadoDB->id)->update([
                        'departamento_id' => $departamentoId,
                    ]);
                }
            }
        }

        // Iterar la lista de usuarios y eliminar usuarios no deseados
        $expectedUserNames = [];
        foreach ($areas as $area) {
            $expectedUserNames[] = $area['jefe']['user_name'];
            $expectedUserNames = array_merge($expectedUserNames, array_column($area['empleados'], 'user_name'));
        }
        DB::table('usuarios')->whereNotIn('user_name', $expectedUserNames)->whereIn('tipo_usuario_id', [2,3])->delete();

        // Crear admin si no existe
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
                'tipo_usuario_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear dueño si no existe
        $dueno = DB::table('usuarios')->where('user_name', 'BPTGroup')->first();

        if (!$dueno) {
            DB::table('usuarios')->insert([
                'user_name'       => 'BPTGroup',
                'password'        => Hash::make('BTPGroup'),
                'correo'          => 'BPTGroup@ninebox.com',
                'nombre'          => 'Dueño',
                'apellido_paterno'=> 'General',
                'apellido_materno'=> null,
                'telefono'        => null,
                'tipo_usuario_id' => 4, 
                'created_at'      => now(),
                'updated_at'      => now(),
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

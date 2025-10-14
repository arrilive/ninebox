<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JefesYEmpleadosSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
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
        ];

        foreach ($areas as $area) {
            // Crear o recuperar jefe
            $jefe = DB::table('usuarios')->where('user_name', $this->slugify($area['jefe']))->first();
            if ($jefe) {
                $jefeId = $jefe->id;
            } else {
                $jefeId = DB::table('usuarios')->insertGetId([
                    'user_name' => $this->slugify($area['jefe']),
                    'password' => Hash::make('password123'),
                    'correo' => null,
                    'apellido_paterno' => $area['jefe'],
                    'apellido_materno' => '',
                    'telefono' => null,
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
                $existe = DB::table('usuarios')->where('user_name', $this->slugify($empleado))->exists();
                if (!$existe) {
                    DB::table('usuarios')->insert([
                        'user_name' => $this->slugify($empleado),
                        'password' => Hash::make('password123'),
                        'correo' => null,
                        'apellido_paterno' => $empleado,
                        'apellido_materno' => '',
                        'telefono' => null,
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

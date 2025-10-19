<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // tipos_usuarios (roles)
        Schema::create('tipos_usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo_nombre', 50);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // departamentos 
        Schema::create('departamentos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_departamento', 120);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // sucursales
        Schema::create('sucursales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_sucursal', 150);
            $table->string('direccion', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->timestamps();
        });

        // nine_box (9-box)
        Schema::create('nine_box', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 80);
            $table->tinyInteger('posicion')->unsigned();
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->unique('posicion', 'ux_ninebox_posicion');
        });

        // usuarios
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name', 60)->nullable()->unique();
            $table->string('password', 255)->nullable();
            $table->string('correo', 150)->nullable()->unique();
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100)->nullable();
            $table->string('apellido_materno', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->unsignedInteger('departamento_id')->nullable();
            $table->unsignedInteger('tipo_usuario_id');
            $table->unsignedInteger('sucursal_id')->nullable();
            $table->timestamps();

            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('tipo_usuario_id')->references('id')->on('tipos_usuarios')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null')->onUpdate('cascade');
        });

        // rendimientos
        Schema::create('rendimientos', function (Blueprint $table) {
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('ninebox_id');
            $table->text('comentario')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['usuario_id', 'created_at']);

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ninebox_id')->references('id')->on('nine_box')->onDelete('restrict')->onUpdate('cascade');
        });

        // Jefe_id en departamentos (único por departamento) a usuarios
        Schema::table('departamentos', function (Blueprint $table) {
            $table->unsignedInteger('jefe_id')->nullable()->after('descripcion');
            $table->unique('jefe_id', 'uq_departamentos_jefe');
            $table->foreign('jefe_id')->references('id')->on('usuarios')->onDelete('set null')->onUpdate('cascade');
        });

        // Seeds básicos: roles y nine_box
        DB::table('tipos_usuarios')->insert([
            ['tipo_nombre' => 'superusuario', 'descripcion' => 'Acceso total al sistema', 'created_at' => now(), 'updated_at' => now()],
            ['tipo_nombre' => 'administrador', 'descripcion' => 'Jefe de departamento - ve y asigna 9-box a sus empleados', 'created_at' => now(), 'updated_at' => now()],
            ['tipo_nombre' => 'empleado', 'descripcion' => 'Empleado registrado; sin permiso de login', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('nine_box')->insert([
            ['nombre' => 'Bajo/Bajo', 'posicion' => 1, 'descripcion' => 'Cuadrante 1', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Medio/Bajo', 'posicion' => 2, 'descripcion' => 'Cuadrante 2', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Alto/Bajo', 'posicion' => 3, 'descripcion' => 'Cuadrante 3', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bajo/Medio', 'posicion' => 4, 'descripcion' => 'Cuadrante 4', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Medio/Medio', 'posicion' => 5, 'descripcion' => 'Cuadrante 5', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Alto/Medio', 'posicion' => 6, 'descripcion' => 'Cuadrante 6', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bajo/Alto', 'posicion' => 7, 'descripcion' => 'Cuadrante 7', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Medio/Alto', 'posicion' => 8, 'descripcion' => 'Cuadrante 8', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Alto/Alto', 'posicion' => 9, 'descripcion' => 'Cuadrante 9', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departamentos', function (Blueprint $table) {
            // drop FK and column if exists
            if (Schema::hasColumn('departamentos', 'jefe_id')) {
                $table->dropForeign(['jefe_id']);
                $table->dropUnique('uq_departamentos_jefe');
                $table->dropColumn('jefe_id');
            }
        });

        Schema::dropIfExists('rendimientos');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('nine_box');
        Schema::dropIfExists('sucursales');
        Schema::dropIfExists('departamentos');
        Schema::dropIfExists('tipos_usuarios');
    }
};

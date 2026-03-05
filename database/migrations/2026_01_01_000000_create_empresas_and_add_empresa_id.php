<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up()
    {
        // 1. Crear tabla empresas
        Schema::create('empresas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 150);
            $table->string('slug', 100)->unique();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        // 2. Insertar BPT y Dunosusa
        DB::table('empresas')->insert([
            ['id' => 1, 'nombre' => 'BPT',      'slug' => 'bpt',      'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Dunosusa',  'slug' => 'dunosusa', 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Agregar empresa_id a las 3 tablas (nullable primero para no romper datos existentes)
        Schema::table('departamentos', function (Blueprint $table) {
            $table->unsignedInteger('empresa_id')->nullable()->after('descripcion');
        });
        Schema::table('sucursales', function (Blueprint $table) {
            $table->unsignedInteger('empresa_id')->nullable()->after('ciudad');
        });
        Schema::table('usuarios', function (Blueprint $table) {
            $table->unsignedInteger('empresa_id')->nullable()->after('sucursal_id');
        });

        // 4. Asignar empresa_id=1 (BPT) a todos los registros existentes
        DB::table('departamentos')->update(['empresa_id' => 1]);
        DB::table('sucursales')->update(['empresa_id' => 1]);
        DB::table('usuarios')->update(['empresa_id' => 1]);

        // 5. Agregar foreign keys
        Schema::table('departamentos', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict');
        });
        Schema::table('sucursales', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict');
        });
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
        Schema::table('departamentos', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
        Schema::dropIfExists('empresas');
    }
};
